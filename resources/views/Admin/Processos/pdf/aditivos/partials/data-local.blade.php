{{-- Linha "Cidade - UF, dd de mês de aaaa." — mesma posição em todos os
     documentos de aditivo (antes cada template alinhava de um jeito). --}}
<div class="data-local">
    {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
</div>
