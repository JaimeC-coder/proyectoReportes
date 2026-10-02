<div>
    <x-slot name="header"><h1 class="font-semibold">Usuarios</h1></x-slot>

    <x-page-header title="Usuarios" subtitle="No existe registro público: sólo el administrador crea las cuentas.">
        <x-slot name="actions">
            <x-w-button primary icon="user-plus" label="Nuevo usuario" wire:click="create" />
        </x-slot>
    </x-page-header>

    <div class="card">
        <div class="border-b border-gray-100 p-4 dark:border-slate-700">
            <div class="max-w-sm"><x-w-input icon="magnifying-glass" placeholder="Buscar por nombre o correo…" wire:model.live.debounce.400ms="search" /></div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-slate-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3">Firma</th>
                        <th class="px-4 py-3">Informes</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach ($users as $user)
                        <tr wire:key="u-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}@if ($user->position) · {{ $user->position }}@endif</p>
                            </td>
                            <td class="px-4 py-3">
                                <x-w-badge flat :color="$user->hasRole('admin') ? 'violet' : 'primary'" :label="$user->roleLabel()" />
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->signature_path)
                                    <img src="{{ $user->signatureUrl() }}" class="h-8 max-w-[90px] rounded bg-white object-contain p-0.5" alt="Firma">
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $user->reports_count }}</td>
                            <td class="px-4 py-3">
                                <x-w-toggle :checked="$user->is_active" wire:click="toggleActive({{ $user->id }})" :disabled="$user->id === auth()->id()" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="btn-icon" title="Editar" wire:click="edit({{ $user->id }})"><x-w-icon name="pencil-square" class="h-4 w-4" /></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-slate-700">{{ $users->links() }}</div>
        @endif
    </div>

    <x-w-modal-card :title="$editingId ? 'Editar usuario' : 'Nuevo usuario'" wire:model="showModal" width="2xl">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-w-input label="Nombre completo *" wire:model="form.name" />
            <x-w-input label="Correo electrónico *" type="email" wire:model="form.email" />
            <x-w-input label="Cargo" wire:model="form.position" placeholder="Ej. Técnico electricista" />
            <x-w-native-select label="Rol *" wire:model="form.role">
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </x-w-native-select>
            <x-w-password :label="$editingId ? 'Nueva contraseña (opcional)' : 'Contraseña *'" wire:model="form.password" autocomplete="new-password" />
            <div class="flex items-end pb-2"><x-w-toggle label="Usuario activo" wire:model="form.is_active" /></div>

            <div class="sm:col-span-2">
                <label class="field-label">Firma (imagen PNG con fondo transparente recomendada)</label>
                <div class="flex items-center gap-4">
                    <div class="flex h-20 w-48 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white dark:border-slate-600">
                        @if ($signature)
                            <img src="{{ $signature->temporaryUrl() }}" class="max-h-16 max-w-[180px] object-contain" alt="">
                        @elseif ($signatureUrl)
                            <img src="{{ $signatureUrl }}" class="max-h-16 max-w-[180px] object-contain" alt="">
                        @else
                            <span class="text-xs text-gray-400">Sin firma</span>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="cursor-pointer text-sm font-medium text-primary-600 hover:underline">
                            <span wire:loading.remove wire:target="signature">Subir firma</span>
                            <span wire:loading wire:target="signature">Cargando…</span>
                            <input type="file" class="hidden" accept="image/*" wire:model="signature">
                        </label>
                        @if ($signature || $signatureUrl)
                            <button type="button" class="text-left text-sm text-red-600 hover:underline" wire:click="removeSignature">Quitar firma</button>
                        @endif
                    </div>
                </div>
                @error('signature') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <x-slot name="footer" class="flex justify-end gap-x-3">
            <x-w-button flat label="Cancelar" x-on:click="close" />
            <x-w-button primary label="Guardar" wire:click="save" spinner="save" />
        </x-slot>
    </x-w-modal-card>
</div>
