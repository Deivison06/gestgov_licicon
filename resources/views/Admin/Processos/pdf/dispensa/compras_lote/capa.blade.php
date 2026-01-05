<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Capa do Processo - {{ $processo->numero_processo }}</title>
    <style type="text/css">
        @font-face {
            font-family: 'Aptos';
            src: url('{{ public_path('storage/fonts/Aptos-ExtraBold.ttf') }}') format('truetype');
            font-weight: 900;
            font-style: normal;
        }

        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Aptos', sans-serif;
            font-size: 18pt;
            font-weight: 900;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            position: relative;
        }

        .container {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
        }

        .capa-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .conteudo {
            position: absolute;
            top: 55%;
            left: 50%;
            width: 78%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .dados-processo {
            padding: 20px;
            border-radius: 8px;
        }

        .numero-processo {
            color: #000000;
            margin-bottom: 5px;
        }

        .modalidade {
            color: #000;
            margin-bottom: 5px;
        }

        .objeto-titulo {
            margin: 20px 0;
        }

        .objeto {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Imagem de fundo da capa -->
        <img src="{{  public_path($prefeitura->capa)}}"
            class="capa-background" alt="Capa da Prefeitura">

        <!-- Conteúdo centralizado -->
        <div class="conteudo">
            <div class="dados-processo">
                <div class="numero-processo">
                    PROCESSO ADMINISTRATIVO<br>
                    <strong>{{ $processo->numero_processo }}</strong>
                </div>

                <div class="modalidade">
                    {{ strtoupper($processo->modalidade->getDisplayName()) }}<br>
                    <strong>{{ $processo->numero_procedimento }}</strong>
                </div>

                <div class="objeto-titulo">OBJETO:</div>
                <div class="objeto">
                    {!! strip_tags($processo->objeto) !!}
                </div>
            </div>
        </div>
    </div>
</body>

</html>
