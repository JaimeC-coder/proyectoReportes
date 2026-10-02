<div>
    <x-slot name="header"><h1 class="font-semibold">Ajustes del PDF</h1></x-slot>

    <x-page-header title="Ajustes del PDF" subtitle="Encabezado, datos del cliente, firma y pie de página. Puedes cambiarlos cuando quieras." />

    <div class="grid gap-6 xl:grid-cols-2">
        <form wire:submit="save" class="space-y-6">
            <div class="card p-5">
                <h2 class="mb-4 font-semibold">Empresa (encabezado)</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><x-w-input label="Razón social *" wire:model.live.debounce.500ms="form.company_name" /></div>
                    <x-w-input label="RUC" wire:model.live.debounce.500ms="form.company_ruc" />
                    <x-w-input label="Teléfono" wire:model.live.debounce.500ms="form.company_phone" />
                    <x-w-input label="Dirección" wire:model.live.debounce.500ms="form.company_address" />
                    <x-w-input label="Correo" wire:model.live.debounce.500ms="form.company_email" />
                    <div class="sm:col-span-2">
                        <label class="field-label">Logo</label>
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-40 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white dark:border-slate-600">
                                @if ($logoSrc)
                                    <img src="{{ $logoSrc }}" class="max-h-14 max-w-[150px] object-contain" alt="Logo">
                                @else
                                    <span class="text-xs text-gray-400">Sin logo</span>
                                @endif
                            </div>
                            <label class="cursor-pointer text-sm font-medium text-primary-600 hover:underline">
                                Subir logo <input type="file" class="hidden" accept="image/*" wire:model="logo">
                            </label>
                            @if ($logoSrc)
                                <button type="button" class="text-sm text-red-600 hover:underline" wire:click="removeLogo">Quitar</button>
                            @endif
                        </div>
                        @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="field-label">Color principal</label>
                        <div class="flex items-center gap-2">
                            <input type="color" class="h-10 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-900" wire:model.live="form.primary_color">
                            <input type="text" class="field-input" wire:model.live.debounce.500ms="form.primary_color">
                        </div>
                        @error('form.primary_color') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="mb-4 font-semibold">Contenido del PDF</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-w-toggle label="Mostrar logo y datos de la empresa" wire:model.live="form.show_company" />
                    <x-w-toggle label="Mostrar datos del cliente / ubicación" wire:model.live="form.show_client" />
                    <x-w-toggle label="Mostrar firma del técnico" wire:model.live="form.show_signature" />
                    <x-w-toggle label="Mostrar firma del cliente" wire:model.live="form.show_client_signature" />
                    <x-w-toggle label="Mostrar código correlativo" wire:model.live="form.show_code" />
                    <x-w-toggle label="Numerar páginas" wire:model.live="form.show_page_numbers" />
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <x-w-input label="Etiqueta para cliente *" wire:model.live.debounce.500ms="form.client_label" />
                    <x-w-input label="Etiqueta para ubicación *" wire:model.live.debounce.500ms="form.location_label" />
                    <x-w-input label="Texto bajo la firma del técnico" wire:model.live.debounce.500ms="form.technician_signature_label" placeholder="Técnico responsable" />
                    <x-w-input label="Texto bajo la firma del cliente" wire:model.live.debounce.500ms="form.client_signature_label" placeholder="Conformidad del cliente" />
                    <div class="sm:col-span-2"><x-w-input label="Texto del pie de página" wire:model.live.debounce.500ms="form.footer_text" /></div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-w-button type="submit" primary icon="check" label="Guardar ajustes" spinner="save" />
            </div>
        </form>

        <div class="hidden xl:block">
            <div class="sticky top-20">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold"><x-w-icon name="eye" class="h-5 w-5 text-primary-500" /> Vista previa</h3>
                <div class="rounded-xl bg-slate-200/70 p-4 dark:bg-slate-950/60">
                    @include('documents.styles', ['settings' => $form])
                    <div class="preview-sheet !min-h-0">
                        @include('documents.report', ['doc' => $doc, 'settings' => $form, 'pdf' => false, 'logoSrc' => $logoSrc])
                        <div class="mt-10 border-t border-gray-200 pt-1 text-[10px] text-gray-500">{{ $form['footer_text'] }} @if ($form['show_page_numbers']) <span class="float-right">Página 1 de 1</span> @endif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
