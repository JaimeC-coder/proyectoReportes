<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use App\Services\ReportDocumentBuilder;
use App\Services\ReportPdfService;
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
        $this->report = $report->load(['user:id,name', 'template:id,name', 'duplicatedFrom:id,code']);
    }

    public function regenerate(ReportPdfService $pdf): void
    {
        Gate::authorize('delete', $this->report); // sólo administradores
        $pdf->generate($this->report);
        $this->dispatch('toast', icon: 'success', title: 'PDF regenerado con los ajustes actuales');
        $this->dispatch('pdf-reloaded');
    }

    public function delete(): void
    {
        Gate::authorize('delete', $this->report);
        $this->report->delete();

        session()->flash('toast', ['icon' => 'success', 'title' => "Informe {$this->report->code} eliminado"]);
        $this->redirectRoute('reports.index');
    }

    public function render(ReportDocumentBuilder $builder, SettingService $settings)
    {
        return view('livewire.reports.show', [
            'doc' => $this->view === 'html' ? $builder->fromReport($this->report) : null,
            'settings' => $settings->all(),
            'logoSrc' => $settings->logoUrl(),
        ])->title($this->report->code);
    }
}
