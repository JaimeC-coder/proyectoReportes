<?php

namespace App\Services;

use App\Data\ReportData;
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

    /** Crea el informe (inmutable) y genera su PDF. */
    public function create(ReportData $data, User $author): Report
    {
        $report = DB::transaction(function () use ($data, $author) {
            $report = Report::create([
                'code' => $this->reports->nextCode(),
                'title' => $data->title,
                'client_name' => $data->clientName,
                'client_location' => $data->clientLocation,
                'service_date' => $data->serviceDate,
                'template_id' => $data->templateId,
                'duplicated_from_id' => $data->duplicatedFromId,
                'user_id' => $author->id,
                'signature' => $this->signatureSnapshot($author),
                'client_signature' => $data->clientSignature,
            ]);

            foreach ($data->sections as $i => $section) {
                $report->sections()->create($section->toArray($i + 1));
            }

            return $report;
        });

        $this->pdf->generate($report);

        return $report->fresh(['sections']);
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
