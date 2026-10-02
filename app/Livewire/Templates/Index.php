<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Services\TemplateService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Plantillas')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $template = Template::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);
        $this->dispatch('toast', icon: 'success', title: $template->is_active ? 'Plantilla activada' : 'Plantilla desactivada');
    }

    public function duplicate(int $id, TemplateService $service): void
    {
        $copy = $service->duplicate(Template::with('sections')->findOrFail($id), auth()->user());
        $this->dispatch('toast', icon: 'success', title: "Se creó “{$copy->name}”");
    }

    public function delete(int $id): void
    {
        Template::findOrFail($id)->delete();
        $this->dispatch('toast', icon: 'success', title: 'Plantilla eliminada');
    }

    public function render(TemplateRepository $templates)
    {
        return view('livewire.templates.index', [
            'templates' => $templates->paginate(trim($this->search)),
        ]);
    }
}
