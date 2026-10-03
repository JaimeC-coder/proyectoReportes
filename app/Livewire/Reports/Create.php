<?php

namespace App\Livewire\Reports;

use App\Data\ReportData;
use App\Livewire\Concerns\ManagesSections;
use App\Models\Report;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Services\ReportDocumentBuilder;
use App\Services\ImageService;
use App\Services\ReportService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Nuevo informe')]
class Create extends Component
{
    use ManagesSections;

    #[Url(as: 'plantilla', history: true)]
    public ?int $templateId = null;

    #[Url(as: 'duplicar')]
    public ?int $duplicateId = null;

    /** Borrador que se está continuando (?borrador=ID). */
    #[Url(as: 'borrador')]
    public ?int $draftId = null;

    /** Paso 1: elegir plantilla. Paso 2: editor. */
    public bool $editing = false;

    public array $form = [
        'title' => '',
        'client_name' => '',
        'client_location' => '',
        'service_date' => '',
        'template_id' => null,
        'duplicated_from_id' => null,
        'client_signature' => ['name' => '', 'position' => '', 'path' => null],
    ];

    /** Imagen de la firma del cliente (opcional). */
    public $clientSignatureUpload;

    public function mount(ReportService $service): void
    {
        if ($this->draftId) {
            $this->loadDraft($service->draftFromSavedDraft($this->currentDraft()));
        } elseif ($this->duplicateId) {
            $source = Report::findOrFail($this->duplicateId);
            Gate::authorize('duplicate', $source);
            $this->loadDraft($service->draftFromReport($source));
        } elseif ($this->templateId) {
            $template = Template::active()->find($this->templateId);
            $template ? $this->loadDraft($service->draftFromTemplate($template)) : $this->templateId = null;
        }
    }

    public function useTemplate(?int $id, ReportService $service): void
    {
        if ($id === null) {
            // Informe en blanco
            $this->templateId = null;
            $this->loadDraft(['form' => array_merge($this->form, [
                'service_date' => now()->toDateString(),
                'template_id' => null,
                'client_signature' => ReportService::emptyClientSignature(),
            ]), 'sections' => []]);

            return;
        }

        $template = Template::active()->findOrFail($id);
        $this->templateId = $template->id;
        $this->loadDraft($service->draftFromTemplate($template));
    }

    public function backToTemplates(): void
    {
        $this->reset(['editing', 'templateId', 'duplicateId', 'draftId', 'sections', 'form']);
    }

    protected function loadDraft(array $draft): void
    {
        $this->form = array_merge($this->form, $draft['form']);
        $this->sections = $this->keyedSections($draft['sections']);
        $this->editing = true;
    }

    public function updatedClientSignatureUpload(ImageService $images): void
    {
        $this->validate(
            ['clientSignatureUpload' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096']],
            [],
            ['clientSignatureUpload' => 'firma del cliente'],
        );

        $this->form['client_signature']['path'] = $images->store($this->clientSignatureUpload, 'signatures/clients');
        $this->reset('clientSignatureUpload');
    }

    public function removeClientSignature(): void
    {
        $this->form['client_signature']['path'] = null;
        $this->reset('clientSignatureUpload');
    }

    /** Borrador en edición (re-autorizado en cada uso) o null si el informe es nuevo. */
    protected function currentDraft(): ?Report
    {
        if (! $this->draftId) {
            return null;
        }

        $draft = Report::findOrFail($this->draftId);
        Gate::authorize('update', $draft);

        return $draft;
    }

    protected function reportData(): ReportData
    {
        return ReportData::fromArray([
            ...$this->form,
            'sections' => array_values($this->sections),
        ]);
    }

    /** "Seguir editando después": guarda como borrador, sin PDF, y vuelve al listado. */
    public function saveDraft(ReportService $service): void
    {
        $this->validateWithAlert(fn () => $this->validateReport(final: false));

        $report = $service->saveDraft($this->reportData(), auth()->user(), $this->currentDraft());
        $this->draftId = $report->id;

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Borrador guardado',
            'text' => 'Puedes seguir editándolo cuando quieras desde "Mis informes". El PDF se generará al finalizarlo.',
        ]);

        $this->redirectRoute('reports.index');
    }

    /** Finaliza el informe: genera el PDF y ya no podrá editarse. */
    public function save(ReportService $service): void
    {
        $this->validateWithAlert(fn () => $this->validateReport(final: true));

        $report = $service->finalize($this->reportData(), auth()->user(), $this->currentDraft());

        session()->flash('swal', [
            'icon' => 'success',
            'title' => 'Informe '.$report->code.' finalizado',
            'text' => 'El PDF se generó correctamente.',
        ]);

        $this->redirectRoute('reports.show', $report);
    }

    /**
     * Un borrador sólo necesita título y fecha; al finalizar se exige
     * el informe completo (al menos una sección y todas las imágenes).
     */
    protected function validateReport(bool $final): void
    {
        $rules = array_merge([
            'form.title' => ['required', 'string', 'max:255'],
            'form.client_name' => ['nullable', 'string', 'max:255'],
            'form.client_location' => ['nullable', 'string', 'max:255'],
            'form.service_date' => ['required', 'date'],
            'form.client_signature.name' => ['nullable', 'string', 'max:150'],
            'form.client_signature.position' => ['nullable', 'string', 'max:150'],
        ], $this->sectionRules());

        if (! $final) {
            $rules['sections'] = ['array'];
        }

        $this->validate($rules, $this->sectionMessages(), [
            'form.title' => 'título',
            'form.client_name' => 'cliente',
            'form.client_location' => 'ubicación',
            'form.service_date' => 'fecha del servicio',
            'form.client_signature.name' => 'nombre de quien firma por el cliente',
            'form.client_signature.position' => 'cargo de quien firma por el cliente',
        ] + $this->sectionAttributes());

        if ($errors = $this->sectionBusinessErrors(requireImages: $final)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function render(TemplateRepository $templates, ReportDocumentBuilder $builder, SettingService $settings, ReportService $reports)
    {
        return view('livewire.reports.create', [
            'templates' => $this->editing ? collect() : $templates->active(),
            'doc' => $this->editing ? $builder->fromDraft($this->form, $this->sections, $reports->signatureSnapshot(auth()->user())) : null,
            'settings' => $settings->all(),
            'logoSrc' => $settings->logoUrl(),
            'isDraft' => (bool) $this->draftId,
            'source' => $this->form['duplicated_from_id'] ? Report::find($this->form['duplicated_from_id'], ['id', 'code']) : null,
        ]);
    }
}
