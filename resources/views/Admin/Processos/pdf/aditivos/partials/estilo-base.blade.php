{{-- Estilos comuns aos 9 documentos de aditivo — antes repetidos em cada
     arquivo .blade.php individualmente. @include('...partials.estilo-base')
     dentro de <style> em cada template. --}}
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

@page { margin: 0; size: A4; }

body {
    margin: 0; padding: 4cm 2cm;
    font-size: 12pt; font-family: 'Aptos', sans-serif;
    background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
    background-repeat: no-repeat; background-position: top left; background-size: cover;
    text-align: justify; text-justify: inter-word; line-height: 1.5;
}

.page-break { page-break-after: always; }

.title {
    text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 30px;
    font-family: 'AptosExtraBold', sans-serif; text-transform: uppercase;
}

.section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
.header-info { margin-bottom: 25px; }
.header-info p { margin: 3px 0; }
.clause-title { font-weight: bold; margin-top: 25px; margin-bottom: 10px; }
.clause-content { text-indent: 40px; margin-bottom: 10px; }
.list-content { margin-left: 40px; margin-bottom: 10px; }
.list-content li { margin-bottom: 5px; }
.signature-block, .signature-section { margin-top: 60px; text-align: center; }
.signature-box { display: inline-block; width: 45%; vertical-align: top; margin-bottom: 40px; }
.check-box { font-family: monospace; font-size: 12pt; }
