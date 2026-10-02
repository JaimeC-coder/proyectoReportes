<x-guest-layout>
    <div class="flex min-h-screen">
        {{-- Panel de marca --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-slate-900 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-700/40 via-slate-900 to-slate-950"></div>
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary-600/20 blur-3xl"></div>

            <div class="relative flex items-center gap-3 text-white">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-600">
                    <x-w-icon name="document-chart-bar" class="h-6 w-6" />
                </span>
                <span class="text-lg font-bold">Sistema de Informes</span>
            </div>

            <div class="relative space-y-6">
                <h1 class="text-4xl font-bold leading-tight text-white">Informes técnicos<br>profesionales en minutos.</h1>
                <ul class="space-y-3 text-slate-300">
                    <li class="flex items-center gap-3"><x-w-icon name="check-circle" class="h-5 w-5 text-primary-400" /> Plantillas reutilizables por tipo de servicio</li>
                    <li class="flex items-center gap-3"><x-w-icon name="check-circle" class="h-5 w-5 text-primary-400" /> Editor de texto enriquecido con vista previa en vivo</li>
                    <li class="flex items-center gap-3"><x-w-icon name="check-circle" class="h-5 w-5 text-primary-400" /> Evidencias fotográficas y PDF automático</li>
                </ul>
            </div>

            <p class="relative text-sm text-slate-500">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        {{-- Formulario --}}
        <div class="relative flex w-full items-center justify-center bg-gray-50 px-6 py-12 lg:w-1/2 dark:bg-slate-900">
            <x-theme-toggle class="absolute right-4 top-4" />

            <div class="w-full max-w-sm">
                <div class="mb-8 lg:hidden">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-600 text-white">
                        <x-w-icon name="document-chart-bar" class="h-6 w-6" />
                    </span>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Iniciar sesión</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ingresa con las credenciales que te asignó el administrador.</p>

                <x-validation-errors class="mt-6" />

                @session('status')
                    <div class="mt-6 text-sm font-medium text-green-600 dark:text-green-400">{{ $value }}</div>
                @endsession

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" x-data="{ loading: false }" @submit="loading = true">
                    @csrf

                    <x-w-input label="Correo electrónico" name="email" type="email" icon="envelope" :value="old('email')" required autofocus autocomplete="username" placeholder="usuario@empresa.com" />

                    <x-w-password label="Contraseña" name="password" required autocomplete="current-password" />

                    <label for="remember_me" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <x-checkbox id="remember_me" name="remember" />
                        Recordar sesión
                    </label>

                    <x-w-button type="submit" primary class="w-full" lg x-bind:disabled="loading">
                        <span x-show="!loading">Ingresar</span>
                        <span x-show="loading" x-cloak>Ingresando…</span>
                    </x-w-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
