import './bootstrap';
import Swal from 'sweetalert2';
import richEditor from './editor';

/* ---------- Modo oscuro ---------- */
window.swalTheme = () => (document.documentElement.classList.contains('dark') ? 'dark' : 'light');

/* ---------- SweetAlert2 ---------- */
window.Swal = Swal;

window.toast = (icon, title) =>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        theme: window.swalTheme(),
    });

/**
 * Confirmación genérica. Devuelve una promesa con true/false.
 *   confirmAction({ title, text, confirmText, icon }).then(ok => ok && ...)
 */
window.confirmAction = ({ title = '¿Estás seguro?', text = '', confirmText = 'Sí, continuar', icon = 'warning', danger = false } = {}) =>
    Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: danger ? '#dc2626' : '#2563eb',
        reverseButtons: true,
        focusCancel: danger,
        theme: window.swalTheme(),
    }).then((r) => r.isConfirmed);

// Eventos disparados desde Livewire: $this->dispatch('swal', icon: 'success', title: '...')
window.addEventListener('swal', (e) => Swal.fire({ theme: window.swalTheme(), ...e.detail }));
window.addEventListener('toast', (e) => window.toast(e.detail.icon ?? 'success', e.detail.title ?? ''));

/* ---------- Alpine (incluido en Livewire 3) ---------- */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('richEditor', richEditor);

    window.Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            try {
                localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            } catch (e) {}
        },
    });
});
