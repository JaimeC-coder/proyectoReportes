{{-- Mensajes flash de sesión mostrados con SweetAlert2 --}}
@if (session('swal'))
    <script>
        document.addEventListener('DOMContentLoaded', () => Swal.fire(Object.assign({ theme: window.swalTheme() }, @js(session('swal')))));
    </script>
@endif
@if (session('toast'))
    <script>
        document.addEventListener('DOMContentLoaded', () => window.toast(@js(session('toast.icon', 'success')), @js(session('toast.title'))));
    </script>
@endif
