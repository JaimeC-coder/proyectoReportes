<div wire:ignore wire:key="rich-{{ $uid }}"
     x-data="richEditor({ state: $wire.entangle('sections.{{ $uid }}.content.html'), placeholder: 'Escribe el contenido de la sección…' })"
     class="overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-slate-600 dark:bg-slate-900">
    <div class="tiptap-toolbar flex flex-wrap items-center gap-0.5 border-b border-gray-200 bg-gray-50 px-2 py-1 dark:border-slate-600 dark:bg-slate-800">
        <button type="button" title="Negrita" @click="run('toggleBold')" :class="{ 'is-active': isActive('bold') }"><b>B</b></button>
        <button type="button" title="Cursiva" @click="run('toggleItalic')" :class="{ 'is-active': isActive('italic') }"><i class="font-serif">I</i></button>
        <button type="button" title="Subrayado" @click="run('toggleUnderline')" :class="{ 'is-active': isActive('underline') }"><u>U</u></button>
        <button type="button" title="Tachado" @click="run('toggleStrike')" :class="{ 'is-active': isActive('strike') }"><s>S</s></button>
        <button type="button" title="Resaltar" @click="run('toggleHighlight')" :class="{ 'is-active': isActive('highlight') }"><span class="rounded bg-yellow-200 px-1 text-gray-800">A</span></button>
        <label title="Color de texto" class="relative inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-slate-600">
            <span class="text-sm font-bold text-red-600 underline decoration-2">A</span>
            <input type="color" class="absolute inset-0 cursor-pointer opacity-0" @input.debounce.150ms="setColor($event.target.value)">
        </label>
        <button type="button" title="Quitar formato" @click="run('unsetAllMarks')"><x-w-icon name="backspace" class="h-4 w-4" /></button>
        <span class="sep"></span>
        <button type="button" title="Título" @click="run('toggleHeading', { level: 2 })" :class="{ 'is-active': isActive('heading', { level: 2 }) }">H2</button>
        <button type="button" title="Subtítulo" @click="run('toggleHeading', { level: 3 })" :class="{ 'is-active': isActive('heading', { level: 3 }) }">H3</button>
        <button type="button" title="Viñetas" @click="run('toggleBulletList')" :class="{ 'is-active': isActive('bulletList') }"><x-w-icon name="list-bullet" class="h-4 w-4" /></button>
        <button type="button" title="Lista numerada" @click="run('toggleOrderedList')" :class="{ 'is-active': isActive('orderedList') }"><x-w-icon name="numbered-list" class="h-4 w-4" /></button>
        <button type="button" title="Cita" @click="run('toggleBlockquote')" :class="{ 'is-active': isActive('blockquote') }"><x-w-icon name="chat-bubble-bottom-center-text" class="h-4 w-4" /></button>
        <span class="sep"></span>
        <button type="button" title="Alinear a la izquierda" @click="run('setTextAlign', 'left')" :class="{ 'is-active': isAlign('left') }"><x-w-icon name="bars-3-bottom-left" class="h-4 w-4" /></button>
        <button type="button" title="Centrar" @click="run('setTextAlign', 'center')" :class="{ 'is-active': isAlign('center') }"><x-w-icon name="bars-3" class="h-4 w-4" /></button>
        <button type="button" title="Alinear a la derecha" @click="run('setTextAlign', 'right')" :class="{ 'is-active': isAlign('right') }"><x-w-icon name="bars-3-bottom-right" class="h-4 w-4" /></button>
        <button type="button" title="Justificar" @click="run('setTextAlign', 'justify')" :class="{ 'is-active': isAlign('justify') }"><x-w-icon name="bars-4" class="h-4 w-4" /></button>
        <span class="sep"></span>
        <button type="button" title="Enlace" @click="setLink()" :class="{ 'is-active': isActive('link') }"><x-w-icon name="link" class="h-4 w-4" /></button>
        <button type="button" title="Insertar imagen" @click="pickImage()" :disabled="uploading">
            <x-w-icon name="photo" class="h-4 w-4" x-show="!uploading" />
            <svg x-show="uploading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
        </button>
        <input type="file" x-ref="image" accept="image/*" class="hidden" @change="uploadImage($event)">
        <button type="button" title="Insertar tabla" @click="run('insertTable', { rows: 3, cols: 3, withHeaderRow: true })"><x-w-icon name="table-cells" class="h-4 w-4" /></button>
        <template x-if="inTable()">
            <span class="flex items-center gap-0.5 rounded bg-primary-50 px-1 dark:bg-slate-700">
                <button type="button" title="Agregar fila" class="!text-xs" @click="run('addRowAfter')">+Fila</button>
                <button type="button" title="Agregar columna" class="!text-xs" @click="run('addColumnAfter')">+Col</button>
                <button type="button" title="Eliminar fila" class="!text-xs" @click="run('deleteRow')">−Fila</button>
                <button type="button" title="Eliminar columna" class="!text-xs" @click="run('deleteColumn')">−Col</button>
                <button type="button" title="Eliminar tabla" class="!text-xs !text-red-600" @click="run('deleteTable')">✕</button>
            </span>
        </template>
        <span class="sep"></span>
        <button type="button" title="Deshacer" @click="run('undo')"><x-w-icon name="arrow-uturn-left" class="h-4 w-4" /></button>
        <button type="button" title="Rehacer" @click="run('redo')"><x-w-icon name="arrow-uturn-right" class="h-4 w-4" /></button>
    </div>
    <div x-ref="editor"></div>
</div>
