<?php

namespace App\Livewire\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Template;
use App\Models\User;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Informes')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'estado', except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $template = '';

    #[Url(except: '')]
    public string $author = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'template', 'author', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'template', 'author', 'from', 'to']);
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $report = Report::findOrFail($id);
        Gate::authorize('delete', $report);
        $report->delete();

        $this->dispatch('toast', icon: 'success', title: $report->isDraft() ? 'Borrador descartado' : "Informe {$report->code} eliminado");
    }

    public function render(ReportRepository $reports)
    {
        $user = auth()->user();

        return view('livewire.reports.index', [
            'reports' => $reports->paginate($user, [
                'search' => trim($this->search),
                'status' => ReportStatus::tryFrom($this->status)?->value,
                'template_id' => $this->template ?: null,
                'user_id' => $user->can('reports.view_all') ? ($this->author ?: null) : null,
                'from' => $this->from ?: null,
                'to' => $this->to ?: null,
            ]),
            'templates' => Template::withTrashed()->orderBy('name')->get(['id', 'name']),
            'authors' => $user->can('reports.view_all') ? User::orderBy('name')->get(['id', 'name']) : collect(),
        ]);
    }
}
