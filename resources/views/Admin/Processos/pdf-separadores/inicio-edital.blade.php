<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Início do Edital</title>
<style type="text/css">
    @font-face {
        font-family: 'Aptos';
        src: url('{{ public_path('storage/fonts/Aptos.ttf') }}') format('truetype');
        font-style: normal;
    }

    @font-face {
        font-family: 'AptosExtraBold';
        src: url('{{ public_path('storage/fonts/Aptos-ExtraBold.ttf') }}') format('truetype');
        font-style: normal;
    }

    @page {
        margin: 0;
        size: A4;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        font-family: 'Aptos', sans-serif;
    }

    body {
        /* Adiciona o timbre como background conforme exemplo */
        background-image: url('{{ public_path($processo->prefeitura->timbre) }}');
        background-repeat: no-repeat;
        background-position: top left;
        background-size: cover;
    }

    /* Estrutura de tabela para garantir alinhamento exato no centro da página */
    .pagina {
        display: table;
        width: 100%;
        height: 100%;
        text-align: center;
    }

    .conteudo {
        display: table-cell;
        vertical-align: middle;
        padding: 0 40px;
    }

    .titulo {
        font-size: 32pt;
        font-family: 'AptosExtraBold', 'Helvetica', Arial, sans-serif;
        font-weight: bold;
        letter-spacing: 2pt;
        color: #1f2937;
        margin: 0;
        text-transform: uppercase;
    }

    .linha {
        width: 120px;
        height: 3px;
        background: #1f2937;
        margin: 25px auto;
    }

    .sub {
        font-size: 13pt;
        color: #4b5563;
        margin: 0;
    }

    .pequeno {
        position: absolute;
        bottom: 30px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 9pt;
        color: #6b7280;
    }
</style>
</head>
<body>
    <div class="pagina">
        <div class="conteudo">
            <p class="titulo">INÍCIO DA MINUTA DO EDITAL</p>
            <div class="linha"></div>
            <p class="sub">As páginas a seguir contêm o Edital integral.</p>
        </div>

        @isset($processo)
        <div class="pequeno">
            Processo {{ $processo->numero_processo }} — {{ $processo->prefeitura->cidade ?? '' }}
        </div>
        @endisset
    </div>
</body>
</html>