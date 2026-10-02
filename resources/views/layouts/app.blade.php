<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 dark:bg-slate-900 dark:text-gray-100">
    <x-banner />

    <div x-data="{ sidebar: false }" class="min-h-screen">
        {{-- Fondo para móvil --}}
        <div x-show="sidebar" x-transition.opacity x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/60 lg:hidden"></div>

        {{-- Barra lateral --}}
        <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 transition-transform lg:translate-x-0 dark:border-r dark:border-slate-800">
            <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-600 text-white">
                    <x-w-icon name="document-chart-bar" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-sm font-bold text-white">Sistema de Informes</p>
                    <p class="text-xs text-slate-400">Informes técnicos</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-4">
                <x-sidebar-link :href="route('dashboard')" icon="home" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>

                <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Informes</p>
                <x-sidebar-link :href="route('reports.create')" icon="plus-circle" :active="request()->routeIs('reports.create')">Nuevo informe</x-sidebar-link>
                <x-sidebar-link :href="route('reports.index')" icon="document-text" :active="request()->routeIs('reports.index', 'reports.show')">Mis informes</x-sidebar-link>

                @canany(['templates.manage', 'users.manage', 'settings.manage'])
                    <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Administración</p>
                @endcanany
                @can('templates.manage')
                    <x-sidebar-link :href="route('templates.index')" icon="rectangle-group" :active="request()->routeIs('templates.*')">Plantillas</x-sidebar-link>
                @endcan
                @can('users.manage')
                    <x-sidebar-link :href="route('users.index')" icon="users" :active="request()->routeIs('users.*')">Usuarios</x-sidebar-link>
                @endcan
                @can('settings.manage')
                    <x-sidebar-link :href="route('settings.edit')" icon="cog-6-tooth" :active="request()->routeIs('settings.*')">Ajustes del PDF</x-sidebar-link>
                @endcan
            </nav>

            <div class="border-t border-slate-800 p-4 text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </div>
        </aside>

        {{-- Contenido --}}
        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white/90 px-4 backdrop-blur sm:px-6 dark:border-slate-800 dark:bg-slate-900/90">
                <button type="button" class="btn-icon lg:hidden" @click="sidebar = true">
                    <x-w-icon name="bars-3" class="h-6 w-6" />
                </button>

                <div class="min-w-0 flex-1">
                    @if (isset($header))
                        {{ $header }}
                    @endif
                </div>

                <x-theme-toggle />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-slate-800">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden text-left leading-tight sm:block">
                                <span class="block font-medium">{{ Auth::user()->name }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->roleLabel() }}</span>
                            </span>
                            <x-w-icon name="chevron-down" class="h-4 w-4 text-gray-400" />
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('profile.show') }}">Mi perfil</x-dropdown-link>
                        <div class="border-t border-gray-200 dark:border-gray-600"></div>
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">Cerrar sesión</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </header>

            <main class="p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')
    @livewireScripts
    @include('partials.flash')
</body>
</html>
