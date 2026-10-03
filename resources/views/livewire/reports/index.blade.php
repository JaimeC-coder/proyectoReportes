<div>
    <x-slot name="header"><h1 class="font-semibold">Informes</h1></x-slot>

    <x-page-header title="Informes" subtitle="Los borradores se pueden seguir editando. Los informes finalizados ya no se editan: puedes verlos, descargar su PDF o duplicarlos.">
        <x-slot name="actions">
            @can('reports.create')
                <x-w-button primary icon="plus" label="Nuevo informe" :href="route('reports.create')" />
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card">
        <div class="flex gap-1 border-b border-gray-100 px-4 pt-3 text-sm dark:border-slate-700">
            @foreach (['' => 'Todos', 'final' => 'Finalizados', 'draft' => 'Borradores'] as $value => $text)
                <button type="button" wire:click="$set('status', '{{ $value }}')"
                        @class([
                            '-mb-px border-b-2 px-3 pb-2 font-medium transition',
                            'border-primary-600 text-primary-700 dark:text-primary-300' => $status === $value,
                            'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200' => $status !== $value,
                        ])>{{ $text }}</button>
            @endforeach
        </div>
        <div class="grid gap-3 border-b border-gray-100 p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-slate-700">
            <div class="lg:col-span-2">
                <x-w-input icon="magnifying-glass" placeholder="Buscar por código, título, cliente…" wire:model.live.debounce.400ms="search" />
            </div>
            <select class="field-input" wire:model.live="template">
                <option value="">Todas las plantillas</option>
                @foreach ($templates as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
            @if ($authors->isNotEmpty())
                <select class="field-input" wire:model.live="author">
                    <option value="">Todos los autores</option>
                    @foreach ($authors as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
            @endif
            <input type="date" class="field-input" wire:model.live="from" title="Desde">
            <div class="flex gap-2">
                <input type="date" class="field-input" wire:model.live="to" title="Hasta">
                <button type="button" class="btn-icon shrink-0" title="Limpiar filtros" wire:click="clearFilters"><x-w-icon name="x-mark" class="h-5 w-5" /></button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-slate-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Informe</th>
                        <th class="px-4 py-3">Fecha servicio</th>
                        <th class="px-4 py-3">Autor</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700" wire:loading.class="opacity-60">
                    @forelse ($reports as $report)
                        <tr wire:key="r-{{ $report->id }}" class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($report->isDraft())
                                    <x-w-badge flat warning icon="pencil" label="Borrador" />
                                @else
                                    <span class="font-mono text-xs font-semibold text-primary-700 dark:text-primary-300">{{ $report->code }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ $report->isDraft() && auth()->user()->can('update', $report) ? route('reports.create', ['borrador' => $report->id]) : route('reports.show', $report) }}" class="font-medium text-gray-900 hover:text-primary-600 dark:text-white">{{ $report->title }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $report->client_name ?: 'Sin cliente' }}@if ($report->client_location) · {{ $report->client_location }}@endif @if ($report->template) · <span class="italic">{{ $report->template->name }}</span>@endif</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $report->service_date->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $report->user->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    @if ($report->isDraft())
                                        @can('update', $report)
                                            <x-w-button xs primary icon="pencil-square" label="Seguir editando" :href="route('reports.create', ['borrador' => $report->id])" />
                                        @else
                                            <a href="{{ route('reports.show', $report) }}" class="btn-icon" title="Ver borrador"><x-w-icon name="eye" class="h-4 w-4" /></a>
                                        @endcan
                                    @else
                                        <a href="{{ route('reports.show', $report) }}" class="btn-icon" title="Ver"><x-w-icon name="eye" class="h-4 w-4" /></a>
                                        <a href="{{ route('reports.download', $report) }}" class="btn-icon" title="Descargar PDF"><x-w-icon name="arrow-down-tray" class="h-4 w-4" /></a>
                                        @can('duplicate', $report)
                                            <a href="{{ route('reports.create', ['duplicar' => $report->id]) }}" class="btn-icon" title="Duplicar"><x-w-icon name="document-duplicate" class="h-4 w-4" /></a>
                                        @endcan
                                    @endif
                                    @can('delete', $report)
                                        <button type="button" class="btn-icon hover:!text-red-600" title="{{ $report->isDraft() ? 'Descartar borrador' : 'Eliminar' }}"
                                                @click="confirmAction({ title: '{{ $report->isDraft() ? '¿Descartar este borrador?' : '¿Eliminar '.$report->code.'?' }}', text: 'El informe dejará de estar disponible.', confirmText: 'Sí, eliminar', danger: true }).then(ok => ok && $wire.delete({{ $report->id }}))">
                                            <x-w-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No se encontraron informes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reports->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-slate-700">{{ $reports->links() }}</div>
        @endif
    </div>
</div>
