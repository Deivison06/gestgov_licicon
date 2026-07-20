{{--
    Renderiza mensagens de flash (session success/error) como toast consistente.
    Usa SweetAlert2 (carregado globalmente no layout). Fallback silencioso caso indisponível.
--}}
@if (session('success') || session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var flash = @json(['success' => session('success'), 'error' => session('error')]);
            function toast(icon, title) {
                if (typeof Swal === 'undefined') { return; }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: title,
                    showConfirmButton: false,
                    timer: icon === 'error' ? 5000 : 3000,
                    timerProgressBar: true,
                });
            }
            if (flash.success) { toast('success', flash.success); }
            if (flash.error) { toast('error', flash.error); }
        });
    </script>
@endif
