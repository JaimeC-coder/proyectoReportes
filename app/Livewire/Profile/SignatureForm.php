<?php

namespace App\Livewire\Profile;

use App\Services\ImageService;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Permite a cada usuario subir la imagen de su firma y su cargo. */
class SignatureForm extends Component
{
    use WithFileUploads;

    public $signature;
    public string $position = '';

    public function mount(): void
    {
        $this->position = auth()->user()->position ?? '';
    }

    public function save(ImageService $images): void
    {
        $this->validate([
            'position' => ['nullable', 'string', 'max:120'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ], [], ['position' => 'cargo', 'signature' => 'firma']);

        $user = auth()->user();
        $user->position = $this->position ?: null;
        if ($this->signature) {
            $user->signature_path = $images->store($this->signature, 'signatures');
            $this->signature = null;
        }
        $user->save();

        $this->dispatch('toast', icon: 'success', title: 'Firma actualizada');
    }

    public function removeSignature(): void
    {
        auth()->user()->update(['signature_path' => null]);
        $this->dispatch('toast', icon: 'success', title: 'Firma eliminada');
    }

    public function render()
    {
        return view('livewire.profile.signature-form', ['user' => auth()->user()]);
    }
}
