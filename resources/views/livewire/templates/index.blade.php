<div>
    <x-slot name="header"><h1 class="font-semibold">Plantillas</h1></x-slot>

    <x-page-header title="Plantillas de informe" subtitle="Define las secciones base de cada tipo de informe. Los cambios no afectan a informes ya creados.">
        <x-slot name="actions">
            <x-w-button primary icon="plus" label="Nueva plantilla" :href="route('templates.create')" />
        </x-slot>
    </x-page-header>

    <div class="mb-4 max-w-md">
        <x-w-input icon="magnifying-glass" placeholder="Buscar plantilla…" wire:model.live.debounce.400ms="search" />
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($templates as $template)
            <div wire:key="t-{{ $template->id }}" class="card flex flex-col p-5">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                        <x-w-icon name="rectangle-group" class="h-6 w-6" />
                    </span>
                    <x-w-toggle :checked="$template->is_active" wire:click="toggleActive({{ $template->id }})" :label="$template->is_active ? 'Activa' : 'Inactiva'" />
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $template->name }}</h3>
                <p class="mt-1 line-clamp-2 flex-1 text-sm text-gray-500 dark:text-gray-400">{{ $template->description ?: 'Sin descripción' }}</p>
                <div class="mt-4 flex gap-4 text-xs text-gray-500">
                    <span><x-w-icon name="squares-2x2" class="inline h-4 w-4" /> {{ $template->sections_count }} secciones</span>
                    <span><x-w-icon name="document-text" class="inline h-4 w-4" /> {{ $template->reports_count }} informes</span>
                </div>
                <div class="mt-4 flex gap-2 border-t border-gray-100 pt-4 dark:border-slate-700">
                    <x-w-button sm primary icon="pencil-square" label="Editar" :href="route('templates.edit', $template)" class="flex-1" />
                    <x-w-button sm outline secondary icon="document-duplicate" wire:click="duplicate({{ $template->id }})" title="Duplicar" />
                    <x-w-button sm outline negative icon="trash" title="Eliminar"
                                @click="confirmAction({ title: '¿Eliminar la plantilla?', text: {{ \Illuminate\Support\Js::from($template->name.'. Los informes creados con ella se conservan.') }}, confirmText: 'Sí, eliminar', danger: true }).then(ok => ok && $wire.delete({{ $template->id }}))" />
                </div>
            </div>
        @empty
            <div class="card col-span-full p-12 text-center text-gray-500">No hay plantillas.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $templates->links() }}</div>
</div>
