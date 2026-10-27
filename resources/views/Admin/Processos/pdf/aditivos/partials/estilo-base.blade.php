{{-- Estilos comuns aos documentos de aditivo. @include('...partials.estilo-base')
     dentro de <style> em cada template.
     Segue o padrão visual dos documentos do processo (pregao_eletronico/compras_lote):
     Arial 10pt, margens do timbre (4cm × 2cm), e as regras ABNT de corpo de texto —
     justificado, recuo de 1,25cm na 1ª linha, entrelinha 1,5, títulos centralizados em
     negrito, data à direita e assinaturas centralizadas uma abaixo da outra. --}}
@page { margin: 0; size: A4; }

body {
    margin: 0; padding: 4cm 2cm;
    font-size: 10pt; font-family: Arial, Helvetica, sans-serif; color: #000;
    background-image: url('{{ public_path($prefeitura->timbre ?? '') }}');
    background-repeat: no-repeat; background-position: top left; background-size: cover;
    text-align: justify; text-justify: inter-word; line-height: 1.5;
}

.page-break { page-break-after: always; }

/* Títulos */
.title {
    text-align: center; font-weight: bold; font-size: 12pt; text-transform: uppercase;
    line-height: 1.3; margin: 0 0 18pt 0;
}
.section-title, .clause-title {
    font-weight: bold; text-transform: uppercase; text-align: left;
    margin: 12pt 0 6pt 0; page-break-after: avoid;
}

/* Corpo do texto */
.clause-content { text-indent: 1.25cm; margin: 0 0 6pt 0; }
.header-info { margin-bottom: 12pt; }
.header-info p { margin: 0; text-indent: 0; }
.list-content { margin: 0 0 6pt 0; padding-left: 1.25cm; }
.list-content li { margin-bottom: 3pt; text-indent: 0; }
.check-box { font-family: 'DejaVu Sans Mono', monospace; }

/* Tabelas (dotação, itens, extrato) */
table.tabela { width: 100%; border-collapse: collapse; margin: 6pt 0 12pt 0; font-size: 9pt; line-height: 1.3; text-align: left; }
table.tabela th, table.tabela td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
table.tabela th, table.tabela td.cabecalho { background-color: #c8d3da; font-weight: bold; text-align: center; }
table.tabela td.rotulo { font-weight: bold; width: 32%; }

/* Local e data */
.data-local { margin-top: 18pt; text-align: right; text-indent: 0; }

/* Data + assinaturas que não podem se separar */
.bloco-final { page-break-inside: avoid; }

/* Assinaturas: sempre empilhadas (lado a lado as linhas "____" se emendavam) */
.signature-section { margin-top: 12pt; }
.signature-block, .signature-box {
    display: block; width: 100%; margin-top: 36pt; text-align: center; text-indent: 0;
    line-height: 1.2; page-break-inside: avoid;
}
