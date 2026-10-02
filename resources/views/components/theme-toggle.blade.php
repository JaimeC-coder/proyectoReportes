<button type="button" x-data @click="$store.theme.toggle()" {{ $attributes->merge(['class' => 'btn-icon']) }}
        :title="$store.theme.dark ? 'Modo claro' : 'Modo oscuro'">
    <x-w-icon name="moon" class="h-5 w-5" x-show="!$store.theme.dark" />
    <x-w-icon name="sun" class="h-5 w-5" x-show="$store.theme.dark" x-cloak />
</button>
