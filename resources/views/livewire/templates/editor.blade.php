<div>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('templates.index') }}" class="text-gray-500 hover:text-primary-600 dark:text-gray-400">Plantillas</a>
            <x-w-icon name="chevron-right" class="h-4 w-4 text-gray-400" />
            <h1 class="truncate font-semibold">{{ $template ? $template->name : 'Nueva plantilla' }}</h1>
        </div>
    </x-slot>

    <div x-data="{ preview: false }" class="grid gap-6 xl:grid-cols-2">
        <div class="min-w-0 space-y-5 pb-24">
            <div class="card p-5">
                <h2 class="mb-4 flex items-center gap-2 font-semibold"><x-w-icon name="rectangle-group" class="h-5 w-5 text-violet-500" /> Datos de la plantilla</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-w-input label="Nombre *" wire:model.live.debounce.500ms="form.name" placeholder="Ej. Mantenimiento de tableros eléctricos" />
                    <x-w-input label="Título por defecto del informe" wire:model.live.debounce.500ms="form.default_title" placeholder="Informe técnico: …" />
                    <div class="sm:col-span-2">
                        <x-w-textarea label="Descripción" wire:model="form.description" rows="2" />
                    </div>
                    <x-w-toggle label="Plantilla activa (disponible para crear informes)" wire:model="form.is_active" />
                </div>
            </div>

            <x-w-alert title="El contenido que escribas aquí será el texto inicial de cada informe." info flat>
                Los técnicos podrán modificarlo, eliminar secciones o agregar nuevas al crear el informe.
            </x-w-alert>

            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold">Secciones</h2>
                    <span class="text-xs text-gray-400">{{ count($sections) }} secciones</span>
                </div>
                @error('sections') <x-w-alert :title="$message" negative flat class="mb-3" /> @enderror
                @include('livewire.sections.builder')
            </div>
        </div>

        <div class="hidden xl:block">
            <div class="sticky top-20 h-[calc(100vh-6.5rem)]">
                @include('livewire.sections.preview')
            </div>
        </div>

        <div x-show="preview" x-cloak class="fixed inset-0 z-50 flex flex-col bg-white p-4 xl:hidden dark:bg-slate-900">
            <div class="mb-2 flex justify-end"><x-w-button flat icon="x-mark" label="Cerrar" @click="preview = false" /></div>
            <div class="min-h-0 flex-1">@include('livewire.sections.preview')</div>
        </div>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur lg:left-64 dark:border-slate-700 dark:bg-slate-900/95">
            <div class="flex justify-end gap-2">
                <x-w-button flat secondary label="Cancelar" :href="route('templates.index')" />
                <x-w-button outline secondary icon="eye" label="Vista previa" class="xl:hidden" @click="preview = true" />
                <x-w-button primary icon="check" label="Guardar plantilla" wire:click="save" spinner="save" />
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('validation-failed', ({ messages }) => {
            Swal.fire({ icon: 'error', title: 'Revisa la plantilla', html: '<ul style="text-align:left">' + messages.map(m => `<li>• ${m}</li>`).join('') + '</ul>', theme: window.swalTheme() });
        });
        $wire.on('section-added', ({ uid }) => {
            setTimeout(() => document.getElementById('section-' + uid)?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
        });
    </script>
    @endscript
</div>
