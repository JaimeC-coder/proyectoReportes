<?php

namespace App\Livewire\Users;

use App\Enums\Role;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Usuarios')]
class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;
    public $signature;

    public array $form = [
        'name' => '',
        'email' => '',
        'position' => '',
        'role' => 'tecnico',
        'password' => '',
        'is_active' => true,
        'signature_path' => null,
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetForm();
        $user = User::with('roles')->findOrFail($id);
        $this->editingId = $user->id;
        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'position' => $user->position ?? '',
            'role' => $user->roles->first()?->name ?? Role::Technician->value,
            'password' => '',
            'is_active' => $user->is_active,
            'signature_path' => $user->signature_path,
        ];
        $this->showModal = true;
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'signature', 'form']);
    }

    public function removeSignature(): void
    {
        $this->signature = null;
        $this->form['signature_path'] = null;
    }

    public function save(ImageService $images): void
    {
        $data = $this->validate([
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'form.position' => ['nullable', 'string', 'max:120'],
            'form.role' => ['required', Rule::enum(Role::class)],
            'form.password' => [$this->editingId ? 'nullable' : 'required', Password::min(8)],
            'form.is_active' => ['boolean'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ], [], [
            'form.name' => 'nombre', 'form.email' => 'correo', 'form.position' => 'cargo',
            'form.role' => 'rol', 'form.password' => 'contraseña', 'signature' => 'firma',
        ])['form'];

        if ($this->editingId === auth()->id() && (! $data['is_active'] || $data['role'] !== Role::Admin->value)) {
            $this->addError('form.role', 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.');

            return;
        }

        $user = $this->editingId ? User::findOrFail($this->editingId) : new User;
        $oldSignature = $user->signature_path;

        $signaturePath = $this->signature ? $images->store($this->signature, 'signatures') : $this->form['signature_path'];

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'position' => $data['position'] ?: null,
            'is_active' => $data['is_active'],
            'signature_path' => $signaturePath,
        ]);
        if (filled($data['password'])) {
            $user->password = $data['password'];
        }
        $user->email_verified_at ??= now();
        $user->save();
        $user->syncRoles([$data['role']]);

        // La firma anterior se conserva porque los informes ya emitidos la referencian.
        unset($oldSignature);

        $this->showModal = false;
        $this->dispatch('toast', icon: 'success', title: $this->editingId ? 'Usuario actualizado' : 'Usuario creado');
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', icon: 'warning', title: 'No puedes desactivar tu propia cuenta');

            return;
        }
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch('toast', icon: 'success', title: $user->is_active ? 'Usuario activado' : 'Usuario desactivado');
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => User::query()
                ->with('roles')
                ->withCount('reports')
                ->when(trim($this->search), fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
                ->orderBy('name')
                ->paginate(10),
            'roles' => Role::cases(),
            'signatureUrl' => $this->form['signature_path'] ? Storage::disk('public')->url($this->form['signature_path']) : null,
        ]);
    }
}
