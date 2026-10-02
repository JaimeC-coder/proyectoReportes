<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportPdfService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportPdfController extends Controller
{
    public function __construct(private readonly ReportPdfService $pdf) {}

    public function show(Report $report): BinaryFileResponse
    {
        Gate::authorize('view', $report);

        return response()->file($this->pdf->absolutePath($report), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$report->pdfFileName().'"',
        ]);
    }

    public function download(Report $report): BinaryFileResponse
    {
        Gate::authorize('view', $report);

        return response()->download($this->pdf->absolutePath($report), $report->pdfFileName());
    }
}
