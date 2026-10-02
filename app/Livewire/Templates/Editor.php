<?php

namespace App\Livewire\Templates;

use App\Data\TemplateData;
use App\Livewire\Concerns\ManagesSections;
use App\Models\Template;
use App\Services\ReportDocumentBuilder;
use App\Services\SettingService;
use App\Services\TemplateService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Editor extends Component
{
    use ManagesSections;

    public ?Template $template = null;

    public array $form = [
        'name' => '',
        'description' => '',
        'default_title' => '',
        'is_active' => true,
    ];

    public function mount(?Template $template, TemplateService $service): void
    {
        $this->template = $template?->exists ? $template : null;
        $draft = $service->draft($this->template);
        $this->form = $draft['form'];
        $this->sections = $this->keyedSections($draft['sections']);
    }

    public function save(TemplateService $service): void
    {
        $this->validateWithAlert(function () {
            $this->validate(array_merge([
                'form.name' => ['required', 'string', 'max:150'],
                'form.description' => ['nullable', 'string', 'max:500'],
                'form.default_title' => ['nullable', 'string', 'max:255'],
                'form.is_active' => ['boolean'],
            ], $this->sectionRules()), $this->sectionMessages(), [
                'form.name' => 'nombre',
                'form.description' => 'descripción',
                'form.default_title' => 'título por defecto',
            ] + $this->sectionAttributes());

            if ($errors = $this->sectionBusinessErrors(requireImages: false)) {
                throw ValidationException::withMessages($errors);
            }
        });

        $isNew = ! $this->template;
        $this->template = $service->save(TemplateData::fromArray([
            ...$this->form,
            'sections' => array_values($this->sections),
        ]), $this->template, auth()->user());

        session()->flash('toast', ['icon' => 'success', 'title' => $isNew ? 'Plantilla creada' : 'Plantilla actualizada']);
        $this->redirectRoute('templates.index');
    }

    public function render(ReportDocumentBuilder $builder, SettingService $settings)
    {
        $doc = $builder->fromDraft(
            ['title' => $this->form['default_title'] ?: $this->form['name'], 'service_date' => now()->toDateString(), 'client_signature' => ['name' => 'Nombre del cliente', 'position' => 'Cargo']],
            $this->sections,
            ['name' => 'Nombre del técnico', 'position' => 'Cargo', 'path' => null],
        );

        return view('livewire.templates.editor', [
            'doc' => $doc,
            'settings' => $settings->all(),
            'logoSrc' => $settings->logoUrl(),
        ])->title($this->template ? 'Editar plantilla' : 'Nueva plantilla');
    }
}
