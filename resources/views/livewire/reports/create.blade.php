<div>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-primary-600 dark:text-gray-400">Informes</a>
            <x-w-icon name="chevron-right" class="h-4 w-4 text-gray-400" />
            <h1 class="truncate font-semibold text-gray-900 dark:text-white">Nuevo informe</h1>
        </div>
    </x-slot>

    @if (! $editing)
        {{-- Paso 1: elegir plantilla --}}
        <div class="mx-auto max-w-5xl">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">¿Con qué plantilla empezamos?</h2>
                <p class="mt-1 text-gray-500 dark:text-gray-400">La plantilla carga las secciones base; luego podrás modificarlas o agregar nuevas.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)
                    <button type="button" wire:click="useTemplate({{ $template->id }})" wire:key="tpl-{{ $template->id }}"
                            class="card group flex flex-col p-5 text-left transition hover:-translate-y-0.5 hover:border-primary-400 hover:shadow-md">
                        <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-200">
                            <x-w-icon name="rectangle-group" class="h-6 w-6" />
                        </span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $template->name }}</span>
                        <span class="mt-1 line-clamp-2 flex-1 text-sm text-gray-500 dark:text-gray-400">{{ $template->description }}</span>
                        <span class="mt-4 flex items-center justify-between text-xs text-gray-400">
                            <span>{{ $template->sections_count }} secciones</span>
                            <span class="font-semibold text-primary-600 group-hover:underline dark:text-primary-400">Usar plantilla →</span>
                        </span>
                    </button>
                @endforeach

                <button type="button" wire:click="useTemplate(null)"
                        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 p-5 text-gray-500 transition hover:border-primary-400 hover:text-primary-600 dark:border-slate-600 dark:text-gray-400">
                    <x-w-icon name="document-plus" class="mb-2 h-10 w-10" />
                    <span class="font-semibold">Informe en blanco</span>
                    <span class="text-xs">Sin secciones predefinidas</span>
                </button>
            </div>
        </div>
    @else
        {{-- Paso 2: editor + vista previa --}}
        <div x-data="{ preview: false }" class="grid gap-6 xl:grid-cols-2">
            <div class="min-w-0 space-y-5 pb-24">
                @if ($source)
                    <x-w-alert title="Duplicando el informe {{ $source->code }}" info flat>
                        El informe original no se modificará; se creará uno nuevo con un código distinto.
                    </x-w-alert>
                @endif

                <div class="card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                            <x-w-icon name="information-circle" class="h-5 w-5 text-primary-500" /> Datos del informe
                        </h2>
                        @unless ($source)
                            <button type="button" class="text-xs text-gray-500 hover:text-primary-600"
                                    @click="confirmAction({ title: '¿Cambiar de plantilla?', text: 'Se perderán los cambios no guardados.' }).then(ok => ok && $wire.backToTemplates())">
                                Cambiar plantilla
                            </button>
                        @endunless
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-w-input label="Título del informe *" wire:model.live.debounce.500ms="form.title" placeholder="Informe técnico: …" />
                        </div>
                        <x-w-input :label="$settings['client_label']" wire:model.live.debounce.500ms="form.client_name" placeholder="Nombre o razón social" />
                        <x-w-input label="Fecha del servicio *" type="date" wire:model.live="form.service_date" />
                        <div class="sm:col-span-2">
                            <x-w-input :label="$settings['location_label']" wire:model.live.debounce.500ms="form.client_location" icon="map-pin" placeholder="Ej. San Juan de Marcona, Ica" />
                        </div>
                    </div>
                </div>

                @if ($settings['show_client_signature'])
                    <div class="card p-5">
                        <h2 class="mb-1 flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                            <x-w-icon name="pencil" class="h-5 w-5 text-primary-500" /> Firma del cliente
                        </h2>
                        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Aparece al final del informe, junto a la firma del técnico. Si no subes la imagen, queda la línea para firmar a mano.</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-w-input label="Nombre de quien firma" wire:model.live.debounce.500ms="form.client_signature.name" :placeholder="$form['client_name'] ?: 'Ej. Juan Ramos'" />
                            <x-w-input label="Cargo" wire:model.live.debounce.500ms="form.client_signature.position" placeholder="Ej. Supervisor de planta" />
                            <div class="sm:col-span-2">
                                <label class="field-label">Imagen de la firma <span class="text-xs font-normal text-gray-400">(opcional · PNG con fondo transparente recomendado)</span></label>
                                <div class="flex items-center gap-4">
                                    <div class="flex h-20 w-52 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white dark:border-slate-600">
                                        @if ($form['client_signature']['path'] ?? null)
                                            <img src="{{ \App\Support\DocumentAsset::src($form['client_signature']['path'], false) }}" class="max-h-16 max-w-[190px] object-contain" alt="Firma del cliente">
                                        @else
                                            <span class="text-xs text-gray-400" wire:loading.remove wire:target="clientSignatureUpload">Sin firma</span>
                                        @endif
                                        <span class="text-xs text-gray-400" wire:loading wire:target="clientSignatureUpload">Subiendo…</span>
                                    </div>
                                    <div class="flex flex-col gap-2 text-sm">
                                        <label class="cursor-pointer font-medium text-primary-600 hover:underline dark:text-primary-400">
                                            {{ ($form['client_signature']['path'] ?? null) ? 'Cambiar firma' : 'Subir firma' }}
                                            <input type="file" class="hidden" accept="image/*" wire:model="clientSignatureUpload">
                                        </label>
                                        @if ($form['client_signature']['path'] ?? null)
                                            <button type="button" class="text-left text-red-600 hover:underline" wire:click="removeClientSignature">Quitar firma</button>
                                        @endif
                                    </div>
                                </div>
                                @error('clientSignatureUpload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Secciones del informe</h2>
                        <span class="text-xs text-gray-400">{{ count($sections) }} secciones</span>
                    </div>
                    @error('sections') <x-w-alert :title="$message" negative flat class="mb-3" /> @enderror
                    @include('livewire.sections.builder')
                </div>
            </div>

            {{-- Vista previa (escritorio) --}}
            <div class="hidden xl:block">
                <div class="sticky top-20 h-[calc(100vh-6.5rem)]">
                    @include('livewire.sections.preview')
                </div>
            </div>

            {{-- Vista previa (móvil / tablet) --}}
            <div x-show="preview" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex flex-col bg-white p-4 xl:hidden dark:bg-slate-900">
                <div class="mb-2 flex justify-end">
                    <x-w-button flat icon="x-mark" label="Cerrar" @click="preview = false" />
                </div>
                <div class="min-h-0 flex-1">@include('livewire.sections.preview')</div>
            </div>

            {{-- Barra de acciones --}}
            <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur lg:left-64 dark:border-slate-700 dark:bg-slate-900/95">
                <div class="flex items-center justify-between gap-3">
                    <p class="hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                        <x-w-icon name="lock-closed" class="inline h-4 w-4" /> Al guardar se genera el PDF y el informe ya no podrá editarse.
                    </p>
                    <div class="flex flex-1 justify-end gap-2 sm:flex-none">
                        <x-w-button outline secondary icon="eye" label="Vista previa" class="xl:hidden" @click="preview = true" />
                        <x-w-button primary icon="document-check" label="Guardar y generar PDF" wire:loading.attr="disabled" wire:target="save"
                                    @click="confirmAction({ title: '¿Guardar el informe?', text: 'Una vez guardado no podrá editarse; sólo visualizarse, descargarse o duplicarse.', confirmText: 'Sí, guardar', icon: 'question' }).then(ok => ok && $wire.save())" />
                    </div>
                </div>
            </div>

            <div wire:loading.flex wire:target="save" class="fixed inset-0 z-[60] items-center justify-center bg-slate-900/60 backdrop-blur-sm">
                <div class="card flex items-center gap-3 px-6 py-4">
                    <svg class="h-6 w-6 animate-spin text-primary-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    <span class="font-medium">Guardando y generando PDF…</span>
                </div>
            </div>
        </div>
    @endif

@script
<script>
    // Si la validación falla, muestra un resumen con SweetAlert2.
    $wire.on('validation-failed', ({ messages }) => {
        Swal.fire({
            icon: 'error',
            title: 'Revisa el informe',
            html: '<ul style="text-align:left">' + messages.map(m => `<li>• ${m}</li>`).join('') + '</ul>',
            theme: window.swalTheme(),
        });
    });
    $wire.on('section-added', ({ uid }) => {
        setTimeout(() => document.getElementById('section-' + uid)?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
    });
</script>
@endscript
</div>
