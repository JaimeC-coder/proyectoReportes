import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import { TextStyle, Color } from '@tiptap/extension-text-style';
import Highlight from '@tiptap/extension-highlight';
import Image from '@tiptap/extension-image';
import { TableKit } from '@tiptap/extension-table';
import { Placeholder } from '@tiptap/extensions';

/**
 * Componente Alpine para el editor de texto enriquecido (Tiptap).
 *
 * Uso: x-data="richEditor({ state: $wire.entangle('ruta.html'), placeholder: '...' })"
 * La instancia de Tiptap se guarda fuera del estado reactivo de Alpine
 * (evita problemas con los Proxies).
 */
export default function richEditor({ state, placeholder = 'Escribe aquí…' }) {
    let editor = null;
    let commitTimer = null;

    return {
        state,
        tick: 0,
        uploading: false,

        init() {
            editor = new Editor({
                element: this.$refs.editor,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [2, 3] },
                        link: { openOnClick: false, autolink: true },
                    }),
                    TextStyle,
                    Color,
                    Highlight.configure({ multicolor: false }),
                    TextAlign.configure({ types: ['heading', 'paragraph'] }),
                    Image.configure({ inline: false, allowBase64: false }),
                    TableKit.configure({ table: { resizable: false } }),
                    Placeholder.configure({ placeholder }),
                ],
                content: this.state || '',
                onUpdate: ({ editor }) => {
                    this.state = editor.isEmpty ? '' : editor.getHTML();
                    // Refresca la vista previa sin saturar el servidor.
                    clearTimeout(commitTimer);
                    commitTimer = setTimeout(() => this.$wire.$commit(), 600);
                },
                onTransaction: () => {
                    this.tick++;
                },
            });

            this.$watch('state', (value) => {
                if (!editor || value === editor.getHTML() || (editor.isEmpty && !value)) return;
                editor.commands.setContent(value || '', { emitUpdate: false });
            });
        },

        destroy() {
            clearTimeout(commitTimer);
            editor?.destroy();
            editor = null;
        },

        isActive(name, attrs = {}) {
            this.tick; // dependencia reactiva
            return editor ? editor.isActive(name, attrs) : false;
        },

        isAlign(value) {
            this.tick;
            return editor ? editor.isActive({ textAlign: value }) : false;
        },

        inTable() {
            this.tick;
            return editor ? editor.isActive('table') : false;
        },

        run(command, ...args) {
            if (!editor) return;
            editor.chain().focus()[command](...args).run();
        },

        setColor(color) {
            editor?.chain().focus().setColor(color).run();
        },

        async setLink() {
            const previous = editor.getAttributes('link').href || '';
            const { value, isDismissed } = await window.Swal.fire({
                title: 'Enlace',
                input: 'url',
                inputValue: previous,
                inputPlaceholder: 'https://',
                showCancelButton: true,
                confirmButtonText: 'Aplicar',
                cancelButtonText: 'Cancelar',
                theme: window.swalTheme(),
            });
            if (isDismissed) return;
            if (!value) {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            editor.chain().focus().extendMarkRange('link').setLink({ href: value }).run();
        },

        pickImage() {
            this.$refs.image.click();
        },

        uploadImage(event) {
            const file = event.target.files[0];
            event.target.value = '';
            if (!file) return;

            this.uploading = true;
            this.$wire.upload(
                'editorImage',
                file,
                async () => {
                    const url = await this.$wire.storeEditorImage();
                    this.uploading = false;
                    if (url) editor.chain().focus().setImage({ src: url }).run();
                },
                () => {
                    this.uploading = false;
                    window.toast('error', 'No se pudo subir la imagen');
                },
            );
        },
    };
}
