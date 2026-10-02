<div class="space-y-2">
    @foreach ($section['content']['items'] ?? [] as $i => $item)
        <div wire:key="kv-{{ $uid }}-{{ $i }}" class="flex items-start gap-2">
            <div class="w-2/5">
                <input type="text" class="field-input font-medium" placeholder="Etiqueta (ej. Objetivo)" wire:model.live.debounce.500ms="sections.{{ $uid }}.content.items.{{ $i }}.label">
                @error("sections.$uid.content.items.$i.label") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <textarea rows="2" class="field-input flex-1" placeholder="Valor" wire:model.live.debounce.500ms="sections.{{ $uid }}.content.items.{{ $i }}.value"></textarea>
            <button type="button" class="btn-icon mt-1 hover:!text-red-600" title="Quitar campo" wire:click="removeKvItem('{{ $uid }}', {{ $i }})"><x-w-icon name="x-mark" class="h-4 w-4" /></button>
        </div>
    @endforeach
    <x-w-button xs outline primary icon="plus" label="Agregar campo" wire:click="addKvItem('{{ $uid }}')" />
</div>
