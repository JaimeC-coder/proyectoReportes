@props(['href', 'icon', 'active' => false])
<a href="{{ $href }}"
   @class([
       'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
       'bg-primary-600 text-white shadow-sm' => $active,
       'text-slate-300 hover:bg-slate-800 hover:text-white' => ! $active,
   ])>
    <x-w-icon :name="$icon" class="h-5 w-5 shrink-0" />
    <span>{{ $slot }}</span>
</a>
