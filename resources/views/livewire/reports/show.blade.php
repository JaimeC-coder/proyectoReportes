<div>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-primary-600 dark:text-gray-400">Informes</a>
            <x-w-icon name="chevron-right" class="h-4 w-4 text-gray-400" />
            <h1 class="font-mono font-semibold">{{ $report->label() }}</h1>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="space-y-4 lg:col-span-1">
            <div class="card p-5">
                <div class="mb-3 flex items-center gap-2">
                    @if ($report->isDraft())
                        <x-w-badge flat warning icon="pencil" label="Borrador" />
                        <x-w-badge flat slate label="Sin PDF" />
                    @else
                        <x-w-badge flat primary :label="$report->code" />
                        <x-w-badge flat slate icon="lock-closed" label="Sólo lectura" />
                    @endif
                </div>
                <h2 class="font-semibold text-gray-900 dark:text-white">{{ $report->title }}</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-500">Cliente</dt><dd>{{ $report->client_name ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Ubicación</dt><dd>{{ $report->client_location ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Fecha del servicio</dt><dd>{{ $report->service_date->format('d/m/Y') }}</dd></div>
                    @if ($report->client_signature)
                        <div><dt class="text-xs text-gray-500">Firma del cliente</dt><dd>{{ $report->client_signature['name'] ?? $report->client_name ?? '—' }}@if (filled($report->client_signature['position'] ?? null)) · {{ $report->client_signature['position'] }}@endif</dd></div>
                    @endif
                    <div><dt class="text-xs text-gray-500">Plantilla</dt><dd>{{ $report->template?->name ?? 'En blanco' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Creado por</dt><dd>{{ $report->user->name }} · {{ $report->created_at->format('d/m/Y H:i') }}</dd></div>
                    @if ($report->isDraft())
                        <div><dt class="text-xs text-gray-500">Última edición</dt><dd>{{ $report->updated_at->format('d/m/Y H:i') }}</dd></div>
                    @elseif ($report->finalized_at)
                        <div><dt class="text-xs text-gray-500">Finalizado</dt><dd>{{ $report->finalized_at->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if ($report->duplicatedFrom)
                        <div><dt class="text-xs text-gray-500">Duplicado de</dt><dd><a class="text-primary-600 hover:underline" href="{{ route('reports.show', $report->duplicatedFrom) }}">{{ $report->duplicatedFrom->code }}</a></dd></div>
                    @endif
                </dl>
            </div>

            <div class="card grid gap-2 p-4">
                @if ($report->isDraft())
                    <p class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                        Este informe aún es un borrador. El PDF estará disponible cuando se finalice.
                    </p>
                    @can('update', $report)
                        <x-w-button primary icon="pencil-square" label="Seguir editando" :href="route('reports.create', ['borrador' => $report->id])" />
                    @endcan
                @else
                    <x-w-button primary icon="arrow-down-tray" label="Descargar PDF" :href="route('reports.download', $report)" />
                    <x-w-button outline primary icon="arrow-top-right-on-square" label="Abrir en pestaña" :href="route('reports.pdf', $report)" target="_blank" />
                    @can('duplicate', $report)
                        <x-w-button outline secondary icon="document-duplicate" label="Duplicar como nuevo" :href="route('reports.create', ['duplicar' => $report->id])" />
                    @endcan
                    @can('regenerate', $report)
                        <x-w-button flat secondary icon="arrow-path" label="Regenerar PDF" wire:click="regenerate" spinner="regenerate" />
                    @endcan
                @endif
                @can('delete', $report)
                    <x-w-button flat negative icon="trash" :label="$report->isDraft() ? 'Descartar borrador' : 'Eliminar'"
                                @click="confirmAction({ title: '{{ $report->isDraft() ? '¿Descartar este borrador?' : '¿Eliminar '.$report->code.'?' }}', confirmText: 'Sí, eliminar', danger: true }).then(ok => ok && $wire.delete())" />
                @endcan
            </div>
        </div>

        <div class="lg:col-span-3">
            @if ($report->isFinal())
                <div class="mb-3 inline-flex rounded-lg border border-gray-200 bg-white p-1 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <button type="button" wire:click="$set('view', 'pdf')" @class(['rounded-md px-3 py-1.5 font-medium', 'bg-primary-600 text-white' => $view === 'pdf', 'text-gray-600 dark:text-gray-300' => $view !== 'pdf'])>PDF</button>
                    <button type="button" wire:click="$set('view', 'html')" @class(['rounded-md px-3 py-1.5 font-medium', 'bg-primary-600 text-white' => $view === 'html', 'text-gray-600 dark:text-gray-300' => $view !== 'html'])>Documento</button>
                </div>
            @endif

            @if ($view === 'pdf')
                <div class="card overflow-hidden" x-data x-on:pdf-reloaded.window="$refs.frame.src = $refs.frame.src.split('?')[0] + '?t=' + Date.now()">
                    <iframe x-ref="frame" src="{{ route('reports.pdf', $report) }}" class="h-[calc(100vh-12rem)] min-h-[600px] w-full bg-slate-100" title="PDF {{ $report->label() }}"></iframe>
                </div>
            @else
                <div class="rounded-xl bg-slate-200/70 p-4 dark:bg-slate-950/60">
                    @include('documents.styles', ['settings' => $settings])
                    <div class="preview-sheet">
                        @include('documents.report', ['doc' => $doc, 'settings' => $settings, 'pdf' => false, 'logoSrc' => $logoSrc])
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
