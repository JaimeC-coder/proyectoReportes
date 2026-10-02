<?php

namespace App\Livewire\Settings;

use App\Services\ImageService;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Ajustes del PDF')]
class Edit extends Component
{
    use WithFileUploads;

    public array $form = [];
    public $logo;

    public function mount(SettingService $settings): void
    {
        $this->form = $settings->all();
    }

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->form['company_logo'] = null;
    }

    public function save(SettingService $settings, ImageService $images): void
    {
        $this->validate([
            'form.company_name' => ['required', 'string', 'max:150'],
            'form.company_ruc' => ['nullable', 'string', 'max:20'],
            'form.company_address' => ['nullable', 'string', 'max:255'],
            'form.company_phone' => ['nullable', 'string', 'max:50'],
            'form.company_email' => ['nullable', 'email', 'max:150'],
            'form.primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'form.footer_text' => ['nullable', 'string', 'max:200'],
            'form.client_label' => ['required', 'string', 'max:40'],
            'form.location_label' => ['required', 'string', 'max:40'],
            'form.show_company' => ['boolean'],
            'form.show_client' => ['boolean'],
            'form.show_signature' => ['boolean'],
            'form.show_client_signature' => ['boolean'],
            'form.technician_signature_label' => ['nullable', 'string', 'max:60'],
            'form.client_signature_label' => ['nullable', 'string', 'max:60'],
            'form.show_code' => ['boolean'],
            'form.show_page_numbers' => ['boolean'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ], [], [
            'form.company_name' => 'razón social', 'form.primary_color' => 'color principal',
            'form.client_label' => 'etiqueta de cliente', 'form.location_label' => 'etiqueta de ubicación',
            'form.company_email' => 'correo', 'logo' => 'logo',
        ]);

        if ($this->logo) {
            $this->form['company_logo'] = $images->store($this->logo, 'branding');
            $this->logo = null;
        }

        $settings->update($this->form);
        $this->form = $settings->all();

        $this->dispatch('swal', icon: 'success', title: 'Ajustes guardados', text: 'Se aplicarán a los PDF que se generen desde ahora.');
    }

    public function render(SettingService $settings)
    {
        $sample = [
            'code' => 'INF-'.now()->year.'-0001',
            'title' => 'Informe técnico de ejemplo',
            'client_name' => 'Cliente de ejemplo S.A.C.',
            'client_location' => 'Trujillo, La Libertad',
            'service_date' => now(),
            'signature' => ['name' => auth()->user()->name, 'position' => auth()->user()->position, 'path' => auth()->user()->signature_path],
            'client_signature' => ['name' => 'Nombre del cliente', 'position' => 'Cargo', 'path' => null],
            'sections' => [[
                'title' => 'Información general',
                'description' => null,
                'type' => 'key_value',
                'content' => ['items' => [['label' => 'Servicio', 'value' => 'Mantenimiento preventivo'], ['label' => 'Objetivo', 'value' => 'Vista de ejemplo del encabezado y pie del PDF.']]],
            ]],
        ];

        return view('livewire.settings.edit', [
            'doc' => $sample,
            'logoSrc' => $this->logo ? $this->logo->temporaryUrl() : ($this->form['company_logo'] ? '/storage/'.$this->form['company_logo'] : null),
        ]);
    }
}
