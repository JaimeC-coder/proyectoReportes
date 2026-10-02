<div>
    <x-slot name="header"><h1 class="font-semibold">Dashboard</h1></x-slot>

    <x-page-header :title="'Hola, '.auth()->user()->name" subtitle="Resumen de la actividad de informes técnicos.">
        <x-slot name="actions">
            @can('reports.create')
                <x-w-button primary icon="plus" label="Nuevo informe" :href="route('reports.create')" />
            @endcan
        </x-slot>
    </x-page-header>

    @php
        $cards = [
            ['label' => auth()->user()->can('reports.view_all') ? 'Informes totales' : 'Mis informes', 'value' => $stats['total'], 'icon' => 'document-text', 'color' => 'bg-primary-600'],
            ['label' => 'Este mes', 'value' => $stats['month'], 'icon' => 'calendar-days', 'color' => 'bg-emerald-600'],
            ['label' => 'Hoy', 'value' => $stats['today'], 'icon' => 'clock', 'color' => 'bg-amber-500'],
            ['label' => 'Plantillas activas', 'value' => $templatesCount, 'icon' => 'rectangle-group', 'color' => 'bg-violet-600'],
        ];
    @endphp
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="card flex items-center gap-4 p-5">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl {{ $card['color'] }} text-white shadow-sm">
                    <x-w-icon :name="$card['icon']" class="h-6 w-6" />
                </span>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($card['value']) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-slate-700">
                <h2 class="font-semibold">Últimos informes</h2>
                <a href="{{ route('reports.index') }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">Ver todos</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-slate-700">
                @forelse ($recent as $report)
                    <li>
                        <a href="{{ route('reports.show', $report) }}" class="flex items-center gap-4 px-5 py-3 transition hover:bg-gray-50 dark:hover:bg-slate-700/40">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300">
                                <x-w-icon name="document" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $report->title }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $report->code }} · {{ $report->client_name ?: 'Sin cliente' }} · {{ $report->user->name }}</p>
                            </div>
                            <span class="hidden text-xs text-gray-400 sm:block">{{ $report->created_at->diffForHumans() }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-sm text-gray-500">Aún no hay informes.</li>
                @endforelse
            </ul>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="mb-3 font-semibold">Accesos rápidos</h2>
                <div class="grid gap-2">
                    @can('reports.create')
                        <a href="{{ route('reports.create') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm hover:border-primary-400 hover:bg-primary-50 dark:border-slate-600 dark:hover:bg-slate-700"><x-w-icon name="plus-circle" class="h-5 w-5 text-primary-600" /> Crear informe</a>
                    @endcan
                    @can('templates.manage')
                        <a href="{{ route('templates.create') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm hover:border-primary-400 hover:bg-primary-50 dark:border-slate-600 dark:hover:bg-slate-700"><x-w-icon name="rectangle-group" class="h-5 w-5 text-violet-600" /> Nueva plantilla</a>
                    @endcan
                    @can('users.manage')
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm hover:border-primary-400 hover:bg-primary-50 dark:border-slate-600 dark:hover:bg-slate-700"><x-w-icon name="users" class="h-5 w-5 text-emerald-600" /> Usuarios ({{ $usersCount }})</a>
                    @endcan
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm hover:border-primary-400 hover:bg-primary-50 dark:border-slate-600 dark:hover:bg-slate-700"><x-w-icon name="pencil-square" class="h-5 w-5 text-amber-500" /> Mi firma y perfil</a>
                </div>
            </div>
            @if (! auth()->user()->signature_path)
                <x-w-alert title="Aún no tienes firma" warning>
                    Sube tu firma desde <a href="{{ route('profile.show') }}" class="font-semibold underline">Mi perfil</a> para que aparezca al final de tus informes.
                </x-w-alert>
            @endif
        </div>
    </div>
</div>
