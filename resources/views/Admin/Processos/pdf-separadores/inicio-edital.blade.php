<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Início do Edital</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'Helvetica', Arial, sans-serif;
        }

        .pagina {
            position: relative;
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
            font-size: 42pt;
            font-weight: bold;
            letter-spacing: 4pt;
            color: #1f2937;
            margin: 0;
        }

        .linha {
            width: 120px;
            height: 3px;
            background: #1f2937;
            margin: 30px auto;
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
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="pagina">
        <div class="conteudo">
            <p class="titulo">INÍCIO DO EDITAL</p>
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