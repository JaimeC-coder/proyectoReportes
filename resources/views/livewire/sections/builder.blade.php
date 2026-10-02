{{--
    Constructor de secciones (compartido por plantillas e informes).
    Variables: $sections (propiedad Livewire), $requireImages (bool)
--}}
<div class="space-y-4">
    @foreach ($sections as $uid => $section)
        @php
            $type = \App\Enums\SectionType::from($section['type']);
            $n = $loop->iteration;
        @endphp
        <div wire:key="section-{{ $uid }}" id="section-{{ $uid }}" x-data="{ open: true }" class="card overflow-hidden">
            {{-- Encabezado de la sección --}}
            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/80 px-4 py-2.5 dark:border-slate-700 dark:bg-slate-800/60">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">{{ $n }}</span>
                <button type="button" @click="open = !open" class="flex min-w-0 flex-1 items-center gap-2 text-left">
                    <span class="truncate text-sm font-semibold uppercase text-gray-800 dark:text-gray-100">{{ $section['title'] ?: 'Sección sin título' }}</span>
                    <span class="hidden shrink-0 items-center gap-1 rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 sm:inline-flex dark:bg-primary-900/40 dark:text-primary-200">
                        <x-w-icon :name="$type->icon()" class="h-3.5 w-3.5" /> {{ $type->label() }}
                    </span>
                </button>
                <div class="flex shrink-0 items-center">
                    <button type="button" class="btn-icon" title="Subir" wire:click="moveSection('{{ $uid }}', -1)" @disabled($loop->first)><x-w-icon name="arrow-up" class="h-4 w-4" /></button>
                    <button type="button" class="btn-icon" title="Bajar" wire:click="moveSection('{{ $uid }}', 1)" @disabled($loop->last)><x-w-icon name="arrow-down" class="h-4 w-4" /></button>
                    <button type="button" class="btn-icon" title="Duplicar sección" wire:click="duplicateSection('{{ $uid }}')"><x-w-icon name="document-duplicate" class="h-4 w-4" /></button>
                    <button type="button" class="btn-icon" title="Insertar sección debajo" wire:click="openAddModal('{{ $uid }}')"><x-w-icon name="plus" class="h-4 w-4" /></button>
                    <button type="button" class="btn-icon hover:!text-red-600" title="Eliminar sección"
                            @click="confirmAction({ title: '¿Eliminar la sección?', text: @js($section['title']), confirmText: 'Sí, eliminar', danger: true }).then(ok => ok && $wire.removeSection('{{ $uid }}'))">
                        <x-w-icon name="trash" class="h-4 w-4" />
                    </button>
                    <button type="button" class="btn-icon" @click="open = !open" :title="open ? 'Contraer' : 'Expandir'">
                        <x-w-icon name="chevron-up" class="h-4 w-4 transition" x-bind:class="open ? '' : 'rotate-180'" />
                    </button>
                </div>
            </div>

            {{-- Cuerpo --}}
            <div x-show="open" x-collapse.duration.150ms class="space-y-4 p-4">
                <div class="grid gap-4 sm:grid-cols-5">
                    <div class="sm:col-span-2">
                        <label class="field-label">Subtítulo <span class="text-red-500">*</span></label>
                        <input type="text" class="field-input" wire:model.live.debounce.500ms="sections.{{ $uid }}.title" placeholder="Ej. Estado inicial">
                        @error("sections.$uid.title") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-3">
                        <label class="field-label">Descripción / introducción <span class="text-xs font-normal text-gray-400">(opcional)</span></label>
                        <textarea rows="2" class="field-input" wire:model.live.debounce.600ms="sections.{{ $uid }}.description" placeholder="Texto breve que aparece en cursiva bajo el subtítulo"></textarea>
                    </div>
                </div>

                @include('livewire.sections.editors.'.$type->value, ['uid' => $uid, 'section' => $section])
            </div>
        </div>
    @endforeach

    {{-- Tarjeta para agregar secciones --}}
    <button type="button" wire:click="openAddModal"
            class="group flex w-full flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-gray-300 bg-white/50 px-6 py-10 text-gray-500 transition hover:border-primary-500 hover:bg-primary-50/50 hover:text-primary-600 dark:border-slate-600 dark:bg-slate-800/40 dark:text-gray-400 dark:hover:border-primary-400 dark:hover:bg-slate-800 dark:hover:text-primary-300">
        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-600 text-white shadow-lg shadow-primary-600/30 transition group-hover:scale-110">
            <x-w-icon name="plus" class="h-9 w-9" />
        </span>
        <span class="text-base font-semibold">Agregar sección</span>
        <span class="text-sm">Texto enriquecido, campos clave-valor, tabla o galería de imágenes</span>
    </button>

    {{-- Modal: elegir tipo de bloque --}}
    <x-w-modal-card title="Agregar sección" wire:model="showAddModal" width="3xl">
        <div class="space-y-5">
            <div>
                <p class="field-label">1. Elige el tipo de bloque</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach (\App\Enums\SectionType::cases() as $option)
                        <button type="button" wire:click="selectType('{{ $option->value }}')" wire:key="type-{{ $option->value }}"
                                @class([
                                    'flex items-start gap-3 rounded-xl border-2 p-4 text-left transition',
                                    'border-primary-500 bg-primary-50 ring-2 ring-primary-500/20 dark:bg-primary-900/30' => $newType === $option->value,
                                    'border-gray-200 hover:border-primary-300 hover:bg-gray-50 dark:border-slate-600 dark:hover:bg-slate-700/50' => $newType !== $option->value,
                                ])>
                            <span @class([
                                'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg',
                                'bg-primary-600 text-white' => $newType === $option->value,
                                'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300' => $newType !== $option->value,
                            ])>
                                <x-w-icon :name="$option->icon()" class="h-5 w-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $option->label() }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $option->description() }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
                @error('newType') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4">
                <x-w-input label="2. Subtítulo de la sección" wire:model="newTitle" placeholder="Ej. Observaciones adicionales" wire:keydown.enter="addSection" />
                <x-w-textarea label="Descripción (opcional)" wire:model="newDescription" rows="2" placeholder="Texto introductorio que aparecerá bajo el subtítulo" />
            </div>
        </div>

        <x-slot name="footer" class="flex justify-end gap-x-3">
            <x-w-button flat label="Cancelar" x-on:click="close" />
            <x-w-button primary label="Agregar sección" icon="plus" wire:click="addSection" spinner="addSection" />
        </x-slot>
    </x-w-modal-card>
</div>
