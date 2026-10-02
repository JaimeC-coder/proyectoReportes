{{-- Panel de vista previa en vivo. Variables: $doc, $settings, $logoSrc --}}
<div class="flex h-full flex-col">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
            <x-w-icon name="eye" class="h-5 w-5 text-primary-500" /> Vista previa
        </h3>
        <span class="flex items-center gap-1.5 text-xs text-gray-400">
            <span wire:loading.remove class="h-2 w-2 rounded-full bg-emerald-500"></span>
            <span wire:loading class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
            <span wire:loading.remove>Actualizado</span>
            <span wire:loading>Actualizando…</span>
        </span>
    </div>
    <div class="flex-1 overflow-y-auto rounded-xl bg-slate-200/70 p-4 dark:bg-slate-950/60">
        @include('documents.styles', ['settings' => $settings])
        <div class="preview-sheet">
            @include('documents.report', ['doc' => $doc, 'settings' => $settings, 'pdf' => false, 'logoSrc' => $logoSrc])
        </div>
    </div>
</div>
