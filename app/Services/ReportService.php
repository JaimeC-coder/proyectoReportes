<?php

namespace App\Services;

use App\Data\ReportData;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Template;
use App\Models\User;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        private readonly ReportRepository $reports,
        private readonly ReportPdfService $pdf,
    ) {}

    /** Crea directamente un informe finalizado (con PDF). */
    public function create(ReportData $data, User $author): Report
    {
        return $this->finalize($data, $author);
    }

    /**
     * Guarda el informe como BORRADOR para seguir editándolo después.
     * No genera PDF ni asigna correlativo.
     */
    public function saveDraft(ReportData $data, User $author, ?Report $draft = null): Report
    {
        $this->assertEditable($draft);

        return DB::transaction(function () use ($data, $author, $draft) {
            $report = $this->persist($data, $author, $draft, [
                'status' => ReportStatus::Draft,
            ]);

            return $report->fresh(['sections']);
        });
    }

    /**
     * Finaliza el informe (nuevo o desde un borrador): asigna el correlativo,
     * congela la firma del técnico, lo vuelve inmutable y genera el PDF.
     */
    public function finalize(ReportData $data, User $author, ?Report $draft = null): Report
    {
        $this->assertEditable($draft);

        $report = DB::transaction(function () use ($data, $author, $draft) {
            return $this->persist($data, $author, $draft, [
                'status' => ReportStatus::Final,
                'code' => $this->reports->nextCode(),
                'signature' => $this->signatureSnapshot($draft?->user ?? $author),
                'finalized_at' => now(),
            ]);
        });

        $this->pdf->generate($report);

        return $report->fresh(['sections']);
    }

    /** Crea o actualiza el informe y reemplaza sus secciones. */
    private function persist(ReportData $data, User $author, ?Report $draft, array $extra): Report
    {
        $report = $draft ?? new Report(['user_id' => $author->id]);

        $report->fill([
            'title' => $data->title,
            'client_name' => $data->clientName,
            'client_location' => $data->clientLocation,
            'service_date' => $data->serviceDate,
            'template_id' => $data->templateId,
            'duplicated_from_id' => $data->duplicatedFromId,
            'client_signature' => $data->clientSignature,
            ...$extra,
        ])->save();

        $report->sections()->delete();
        foreach ($data->sections as $i => $section) {
            $report->sections()->create($section->toArray($i + 1));
        }

        return $report;
    }

    private function assertEditable(?Report $draft): void
    {
        if ($draft && ! $draft->isDraft()) {
            throw new \LogicException('Un informe finalizado no puede modificarse.');
        }
    }

    /** Firma "congelada" al momento de crear el informe. */
    public function signatureSnapshot(User $user): array
    {
        return [
            'name' => $user->name,
            'position' => $user->position,
            'path' => $user->signature_path,
        ];
    }

    /** Borrador del editor a partir de una plantilla. */
    public function draftFromTemplate(Template $template): array
    {
        $template->loadMissing('sections');

        return [
            'form' => [
                'title' => $template->default_title ?? '',
                'client_name' => '',
                'client_location' => '',
                'service_date' => now()->toDateString(),
                'template_id' => $template->id,
                'duplicated_from_id' => null,
                'client_signature' => self::emptyClientSignature(),
            ],
            'sections' => $template->sections->map(fn ($s) => self::draftSection($s->title, $s->description, $s->type->value, $s->content ?? []))->all(),
        ];
    }

    /** Borrador del editor a partir de un informe existente (duplicar). */
    public function draftFromReport(Report $report): array
    {
        $report->loadMissing('sections');

        return [
            'form' => [
                'title' => $report->title,
                'client_name' => $report->client_name ?? '',
                'client_location' => $report->client_location ?? '',
                'service_date' => now()->toDateString(),
                'template_id' => $report->template_id,
                'duplicated_from_id' => $report->id,
                'client_signature' => array_merge(self::emptyClientSignature(), $report->client_signature ?? []),
            ],
            'sections' => $report->sections->map(fn ($s) => self::draftSection($s->title, $s->description, $s->type->value, $s->content ?? []))->all(),
        ];
    }

    /** Estado del editor para continuar un borrador guardado. */
    public function draftFromSavedDraft(Report $report): array
    {
        $draft = $this->draftFromReport($report);
        $draft['form']['service_date'] = $report->service_date->toDateString();
        $draft['form']['duplicated_from_id'] = $report->duplicated_from_id;

        return $draft;
    }

    public static function emptyClientSignature(): array
    {
        return ['name' => '', 'position' => '', 'path' => null];
    }

    public static function draftSection(string $title, ?string $description, string $type, array $content): array
    {
        return [
            'uid' => (string) Str::ulid(),
            'title' => $title,
            'description' => $description ?? '',
            'type' => $type,
            'content' => $content,
        ];
    }
}
