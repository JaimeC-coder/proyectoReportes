<?php

namespace App\Services;

use App\Data\ReportData;
use App\Data\SectionData;
use App\Enums\SectionType;
use App\Models\Report;
use Carbon\Carbon;

/**
 * Construye la estructura "documento" que comparten la vista previa en vivo,
 * la vista de detalle y el PDF. Así los tres se ven exactamente igual.
 */
class ReportDocumentBuilder
{
    public function fromReport(Report $report): array
    {
        $report->loadMissing('sections');

        return [
            'code' => $report->code,
            'title' => $report->title,
            'client_name' => $report->client_name,
            'client_location' => $report->client_location,
            'service_date' => $report->service_date,
            'signature' => $report->signature,
            'client_signature' => $report->client_signature,
            'sections' => $report->sections->map(fn ($s) => [
                'title' => $s->title,
                'description' => $s->description,
                'type' => $s->type->value,
                'content' => $s->content ?? [],
            ])->all(),
        ];
    }

    /** Desde el estado del editor (Livewire), limpiando el contenido igual que al guardar. */
    public function fromDraft(array $form, array $sections, ?array $signature, ?string $code = null): array
    {
        $date = null;
        try {
            $date = filled($form['service_date'] ?? null) ? Carbon::parse($form['service_date']) : null;
        } catch (\Throwable) {
            $date = null;
        }

        return [
            'code' => $code,
            'title' => $form['title'] ?? '',
            'client_name' => $form['client_name'] ?? null,
            'client_location' => $form['client_location'] ?? null,
            'service_date' => $date,
            'signature' => $signature,
            'client_signature' => ReportData::clientSignature($form['client_signature'] ?? null),
            'sections' => collect($sections)->values()->map(function ($s) {
                $type = SectionType::tryFrom($s['type'] ?? '') ?? SectionType::RichText;

                return [
                    'title' => $s['title'] ?? '',
                    'description' => $s['description'] ?? null,
                    'type' => $type->value,
                    'content' => SectionData::normalizeContent($type, (array) ($s['content'] ?? [])),
                ];
            })->all(),
        ];
    }
}
