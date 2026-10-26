{{-- ====================================================================== --}}
{{-- TERMO DE ADJUDICAÇÃO (gerado junto com o Ato de Autorização de Dispensa) --}}
{{-- ====================================================================== --}}
<div id="cover-page">
    <img src="{{ public_path('icons/capa-documento.png') }}" alt="Martelo da Justiça" class="cover-image">
    <div class="cover-title">
        TERMO DE ADJUDICAÇÃO
    </div>
</div>

<div class="page-break"></div>

<div>
    <h4 style="text-align: center;">
        TERMO DE ADJUDICAÇÃO <br>
        PROCESSO ADMINISTRATIVO N° {{ $processo->numero_processo }} <br>
        DISPENSA DE LICITAÇÃO N° {{ $processo->numero_procedimento }} <br>
    </h4>

    <p style="text-align: justify;">
        CONSIDERANDO os elementos contidos no presente processo de dispensa de licitação, que foi
        devidamente justificado, tanto pela razão da escolha {{ $textoEscolha }}, quanto pela justificativa dos
        preços, vez que a empresa apresentou o {{ $textoPreco }};
    </p>
    <p style="text-align: justify;">
        CONSIDERANDO que o processo foi instruído com os documentos e requisitos que comprovam que o
        contratado possui habilitação e qualificação mínima para celebrar o contrato, conforme preconizado no
        artigo 72 da Lei Federal 14.133/2021;
    </p>
    <p style="text-align: justify;">
        CONSIDERANDO que o PARECER JURÍDICO atesta que foram cumpridas as exigências legais e os
        requisitos mínimos para a contratação;
    </p>
    <p style="text-align: justify;">
        CONSIDERANDO que a dispensa de licitação foi autorizada pela autoridade competente, nos termos do
        artigo 72, VIII da Lei Federal 14.133/2021;
    </p>
    <p style="text-align: justify;">
        No uso das atribuições que me foram conferidas, em especial ao disposto no artigo 71, IV da Lei Federal
        14.133/2021, aplicado no que couber às contratações diretas, ADJUDICO o objeto da DISPENSA DE LICITAÇÃO
        N° {{ $processo->numero_procedimento }} à empresa abaixo indicada, nos termos descritos abaixo:
    </p>

    @include('Admin.Processos.pdf-finalizacao.dispensa.partials.quadro_dispensa')

    @include('Admin.Processos.pdf-finalizacao.dispensa.partials.assinatura_autoridade')
</div>

<div class="page-break"></div>
