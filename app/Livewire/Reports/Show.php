<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use App\Services\ReportDocumentBuilder;
use App\Services\ReportPdfService;
use App\Services\ReportService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Report $report;

    /** pdf | html */
    public string $view = 'pdf';

    public function mount(Report $report): void
    {
        Gate::authorize('view', $report);
        $this->report = $report->load(['user', 'template:id,name', 'duplicatedFrom:id,code']);

        // Un borrador no tiene PDF: sólo se muestra el documento.
        if ($report->isDraft()) {
            $this->view = 'html';
        }
    }

    public function regenerate(ReportPdfService $pdf): void
    {
        Gate::authorize('regenerate', $this->report);
        $pdf->generate($this->report);
        $this->dispatch('toast', icon: 'success', title: 'PDF regenerado con los ajustes actuales');
        $this->dispatch('pdf-reloaded');
    }

    public function delete(): void
    {
        Gate::authorize('delete', $this->report);
        $this->report->delete();

        session()->flash('toast', ['icon' => 'success', 'title' => $this->report->isDraft() ? 'Borrador descartado' : "Informe {$this->report->code} eliminado"]);
        $this->redirectRoute('reports.index');
    }

    public function updatedView(): void
    {
        if ($this->report->isDraft()) {
            $this->view = 'html';
        }
    }

    public function render(ReportDocumentBuilder $builder, SettingService $settings, ReportService $reports)
    {
        $doc = null;
        if ($this->view === 'html') {
            $doc = $builder->fromReport($this->report);
            // El borrador aún no tiene la firma "congelada": se muestra la actual del autor.
            $doc['signature'] ??= $reports->signatureSnapshot($this->report->user);
        }

        return view('livewire.reports.show', [
            'doc' => $doc,
            'settings' => $settings->all(),
            'logoSrc' => $settings->logoUrl(),
        ])->title($this->report->label());
    }
}
