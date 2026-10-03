<?php

namespace App\Services;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
    public const DISK = 'local';

    public function __construct(
        private readonly ReportDocumentBuilder $builder,
        private readonly SettingService $settings,
    ) {}

    /** Genera (o regenera) el PDF del informe y devuelve la ruta relativa en el disco privado. */
    public function generate(Report $report): string
    {
        if (! $report->isFinal()) {
            throw new \LogicException('Sólo los informes finalizados tienen PDF.');
        }

        $settings = $this->settings->all();

        $pdf = Pdf::loadView('pdf.report', [
            'doc' => $this->builder->fromReport($report),
            'settings' => $settings,
            'logoPath' => $this->logoPath($settings),
        ])->setPaper('a4');

        $pdf->render();

        if ($settings['show_page_numbers']) {
            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('Helvetica');
            $canvas->page_text($canvas->get_width() - 110, $canvas->get_height() - 30, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, 8, [0.4, 0.4, 0.4]);
        }

        $path = 'reports/'.$report->service_date->format('Y').'/'.$report->pdfFileName();
        Storage::disk(self::DISK)->put($path, $pdf->output());

        if ($report->pdf_path !== $path) {
            $report->forceFill(['pdf_path' => $path])->saveQuietly();
        }

        return $path;
    }

    /** Ruta absoluta del PDF; si no existe (p. ej. se borró), se vuelve a generar. */
    public function absolutePath(Report $report): string
    {
        if (! $report->pdf_path || ! Storage::disk(self::DISK)->exists($report->pdf_path)) {
            $this->generate($report);
        }

        return Storage::disk(self::DISK)->path($report->pdf_path);
    }

    private function logoPath(array $settings): ?string
    {
        $logo = $settings['company_logo'] ?? null;

        return $logo && Storage::disk('public')->exists($logo) ? Storage::disk('public')->path($logo) : null;
    }
}
