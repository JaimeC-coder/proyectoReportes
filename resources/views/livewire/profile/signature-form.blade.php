<x-form-section submit="save">
    <x-slot name="title">Firma y cargo</x-slot>
    <x-slot name="description">Tu firma aparecerá al final de los informes que crees.</x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="position" value="Cargo" />
            <x-input id="position" type="text" class="mt-1 block w-full" wire:model="position" />
            <x-input-error for="position" class="mt-2" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label value="Firma" />
            <div class="mt-2 flex items-center gap-4">
                <div class="flex h-20 w-52 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white dark:border-gray-600">
                    @if ($signature)
                        <img src="{{ $signature->temporaryUrl() }}" class="max-h-16 max-w-[190px] object-contain" alt="">
                    @elseif ($user->signature_path)
                        <img src="{{ $user->signatureUrl() }}" class="max-h-16 max-w-[190px] object-contain" alt="">
                    @else
                        <span class="text-xs text-gray-400">Sin firma</span>
                    @endif
                </div>
                <div class="flex flex-col gap-2 text-sm">
                    <label class="cursor-pointer font-medium text-primary-600 hover:underline">
                        Seleccionar imagen <input type="file" class="hidden" accept="image/*" wire:model="signature">
                    </label>
                    @if ($user->signature_path)
                        <button type="button" class="text-left text-red-600 hover:underline" wire:click="removeSignature">Quitar firma</button>
                    @endif
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Recomendado: PNG con fondo transparente.</p>
            <x-input-error for="signature" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button wire:loading.attr="disabled">Guardar</x-button>
    </x-slot>
</x-form-section>
