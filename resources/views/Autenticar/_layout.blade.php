<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Autenticidade') — GestGov Licitações</title>

    {{-- Tailwind via CDN para a página pública (não depende de build do Vite) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen">

    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#009496] rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-halved text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Autenticidade de Documentos</h1>
                    <p class="text-xs text-slate-500">GestGov Licitações — Validação Pública</p>
                </div>
            </div>
            <a href="{{ route('autenticar.formulario') }}"
               class="text-sm text-[#009496] hover:underline">
                Nova consulta
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="max-w-5xl mx-auto px-6 py-6 text-center text-xs text-slate-400 border-t border-slate-200 mt-12 bg-white">
        Sistema GestGov — assinatura eletrônica simples conforme
        <a href="https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2020/lei/l14063.htm"
           target="_blank" class="text-[#009496] hover:underline">Lei nº 14.063/2020</a>.
    </footer>

</body>
</html>
