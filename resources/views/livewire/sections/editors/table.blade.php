@php $columns = $section['content']['columns'] ?? []; @endphp
<div class="space-y-3">
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-600">
        <table class="w-full min-w-[520px] text-sm">
            <thead class="bg-primary-600/10 dark:bg-primary-900/30">
                <tr>
                    @foreach ($columns as $ci => $col)
                        <th wire:key="th-{{ $uid }}-{{ $ci }}" class="p-1.5">
                            <div class="flex items-center gap-1">
                                <input type="text" class="field-input !py-1 font-semibold" wire:model.live.debounce.500ms="sections.{{ $uid }}.content.columns.{{ $ci }}">
                                @if (count($columns) > 1)
                                    <button type="button" class="btn-icon !h-6 !w-6 hover:!text-red-600" title="Quitar columna" wire:click="removeTableColumn('{{ $uid }}', {{ $ci }})"><x-w-icon name="x-mark" class="h-3.5 w-3.5" /></button>
                                @endif
                            </div>
                        </th>
                    @endforeach
                    <th class="w-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach ($section['content']['rows'] ?? [] as $ri => $row)
                    <tr wire:key="tr-{{ $uid }}-{{ $ri }}">
                        @foreach ($columns as $ci => $col)
                            <td class="p-1.5">
                                <input type="text" @class(['field-input !py-1', 'font-semibold' => $ci === 0]) wire:model.live.debounce.500ms="sections.{{ $uid }}.content.rows.{{ $ri }}.{{ $ci }}">
                            </td>
                        @endforeach
                        <td class="p-1 text-center">
                            <button type="button" class="btn-icon !h-7 !w-7 hover:!text-red-600" title="Quitar fila" wire:click="removeTableRow('{{ $uid }}', {{ $ri }})"><x-w-icon name="trash" class="h-4 w-4" /></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-w-button xs outline primary icon="plus" label="Agregar fila" wire:click="addTableRow('{{ $uid }}')" />
        <x-w-button xs outline secondary icon="view-columns" label="Agregar columna" wire:click="addTableColumn('{{ $uid }}')" />
    </div>
</div>
