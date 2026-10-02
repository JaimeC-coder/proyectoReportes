@php $items = $section['content']['items'] ?? []; @endphp
<div class="space-y-4">
    <div class="grid gap-3 sm:grid-cols-3">
        <div>
            <label class="field-label">Distribución</label>
            <select class="field-input" wire:model.live="sections.{{ $uid }}.content.columns">
                <option value="1">1 imagen por fila</option>
                <option value="2">2 imágenes por fila</option>
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="field-label">Prefijo de cada imagen</label>
            <input type="text" class="field-input" wire:model.live.debounce.500ms="sections.{{ $uid }}.content.label" placeholder="Evidencia">
            <p class="mt-1 text-xs text-gray-400">Se mostrará como “{{ ($section['content']['label'] ?? '') ?: 'Evidencia' }} 1 - Título: descripción”.</p>
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($items as $i => $item)
            <div wire:key="gi-{{ $uid }}-{{ $i }}-{{ md5($item['path'] ?? 'empty') }}" class="rounded-lg border border-gray-200 p-3 dark:border-slate-600">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase text-gray-500">{{ ($section['content']['label'] ?? '') ?: 'Evidencia' }} {{ $i + 1 }}</span>
                    <div class="flex">
                        <button type="button" class="btn-icon !h-7 !w-7" title="Mover antes" wire:click="moveGalleryItem('{{ $uid }}', {{ $i }}, -1)" @disabled($loop->first)><x-w-icon name="arrow-left" class="h-4 w-4" /></button>
                        <button type="button" class="btn-icon !h-7 !w-7" title="Mover después" wire:click="moveGalleryItem('{{ $uid }}', {{ $i }}, 1)" @disabled($loop->last)><x-w-icon name="arrow-right" class="h-4 w-4" /></button>
                        <button type="button" class="btn-icon !h-7 !w-7 hover:!text-red-600" title="Quitar"
                                @click="confirmAction({ title: '¿Quitar esta imagen?', confirmText: 'Quitar', danger: true }).then(ok => ok && $wire.removeGalleryItem('{{ $uid }}', {{ $i }}))">
                            <x-w-icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                @if (! empty($item['path']))
                    <div class="group relative mb-2 overflow-hidden rounded-md bg-gray-100 dark:bg-slate-900">
                        <img src="{{ \App\Support\DocumentAsset::src($item['path'], false) }}" class="mx-auto max-h-44 object-contain" alt="">
                        <label class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/50 text-sm font-medium text-white opacity-0 transition group-hover:opacity-100">
                            <x-w-icon name="arrow-path" class="mr-1 h-4 w-4" /> Cambiar imagen
                            <input type="file" class="hidden" accept="image/*" wire:model="galleryItemUploads.{{ $uid }}.{{ $i }}">
                        </label>
                    </div>
                @else
                    <label class="mb-2 flex h-32 cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-gray-300 text-sm text-gray-500 hover:border-primary-400 hover:text-primary-600 dark:border-slate-600">
                        <x-w-icon name="arrow-up-tray" class="mb-1 h-6 w-6" />
                        <span wire:loading.remove wire:target="galleryItemUploads.{{ $uid }}.{{ $i }}">Subir imagen</span>
                        <span wire:loading wire:target="galleryItemUploads.{{ $uid }}.{{ $i }}">Subiendo…</span>
                        <input type="file" class="hidden" accept="image/*" wire:model="galleryItemUploads.{{ $uid }}.{{ $i }}">
                    </label>
                    @error("sections.$uid.content.items.$i.path") <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror
                @endif
                @error("galleryItemUploads.$uid.$i") <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

                <input type="text" class="field-input mb-2" placeholder="Título (ej. Medición inicial)" wire:model.live.debounce.500ms="sections.{{ $uid }}.content.items.{{ $i }}.title">
                <textarea rows="2" class="field-input" placeholder="Descripción de la imagen" wire:model.live.debounce.600ms="sections.{{ $uid }}.content.items.{{ $i }}.caption"></textarea>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <label class="inline-flex cursor-pointer items-center gap-2 rounded-md bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-700">
            <x-w-icon name="photo" class="h-4 w-4" />
            <span wire:loading.remove wire:target="galleryUploads.{{ $uid }}">Subir imágenes</span>
            <span wire:loading wire:target="galleryUploads.{{ $uid }}">Subiendo…</span>
            <input type="file" class="hidden" accept="image/*" multiple wire:model="galleryUploads.{{ $uid }}">
        </label>
        <x-w-button xs outline secondary icon="plus" label="Tarjeta vacía" wire:click="addGalleryItem('{{ $uid }}')" />
        <span class="text-xs text-gray-400">JPG, PNG o WEBP · máx. 10 MB c/u · se optimizan automáticamente</span>
    </div>
    @error("galleryUploads.$uid.*") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
</div>
