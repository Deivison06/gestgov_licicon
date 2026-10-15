<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parecer Jurídico</title>
    <style>
        @include('Admin.Processos.pdf.aditivos.partials.estilo-base')
        .check-box { font-family: monospace; font-size: 12pt; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('pt_BR');

        $chkAcrescimo = $vm->temAcrescimo() ? '( X )' : '(   )';
        $chkSupressao = $vm->temSupressao() ? '( X )' : '(   )';
        $chkPrazo = ($incidente->tipo === 'prazo') ? '( X )' : '(   )';
        $chkAmbos = ($incidente->tipo === 'prazo_valor') ? '( X )' : '(   )';

        $itemDotacao = $textoJuridico->itemDotacaoDocumentosDosAutos($vm);
        $paragrafoResponsabilidade = $textoJuridico->paragrafoResponsabilidadeAtrasoParecerJuridico($vm);
    @endphp

    <div class="title">PARECER JURÍDICO</div>

    <div class="section-title">I – RELATÓRIO</div>
    <div class="clause-content">
        Trata-se de solicitação de manifestação jurídica acerca da possibilidade legal de celebração de {{ $vm->tituloTipoAlteracao() }}
        ao Contrato nº {{ $vm->numeroContrato }}, firmado entre o Município de {{ $prefeitura->cidade ?? 'Município' }} – {{ $prefeitura->uf ?? 'PI' }}
        e a empresa {{ $contrato->dados_contratante['razao_social'] ?? 'CONTRATADA' }}, cujo objeto é {{ $vm->objetoContrato }}.
    </div>

    <div class="clause-content" style="text-indent: 0;">
        O aditivo proposto refere-se a:<br>
        <span class="check-box">{{ $chkAcrescimo }}</span> acréscimo de quantitativo/valor;<br>
        <span class="check-box">{{ $chkSupressao }}</span> supressão de quantitativo/valor;<br>
        <span class="check-box">{{ $chkPrazo }}</span> prorrogação de prazo;<br>
        <span class="check-box">{{ $chkAmbos }}</span> acréscimo/supressão de valor e prorrogação de prazo.
    </div>

    <div class="clause-content" style="text-indent: 0;">
        Constam nos autos, entre outros documentos:
        <ul class="list-content">
            <li>solicitação e justificativa do setor demandante;</li>
            <li>solicitação e documentação de regularidade da contratada;</li>
            @if($itemDotacao)
                <li>{{ $itemDotacao }}</li>
            @endif
            @if($vm->ehObras())
                <li>Parecer Técnico da Engenharia, com planilha do aditivo.</li>
            @else
                <li>quadro de itens alterados (quando aplicável).</li>
            @endif
        </ul>
    </div>
    <div class="clause-content">
        É o relatório. Passa-se à análise jurídica.
    </div>

    <div class="section-title">II – DA COMPETÊNCIA DA ASSESSORIA JURÍDICA</div>
    <div class="clause-content">
        Nos termos da Lei nº 14.133/2021, compete à Assessoria Jurídica analisar a legalidade do procedimento, restringindo-se aos aspectos jurídico-formais, não adentrando no mérito administrativo ou na conveniência e oportunidade do ato, que são de responsabilidade da Administração.
    </div>

    <div class="section-title">III – DO CABIMENTO DE ADITIVO</div>
    <div class="clause-content">
        A Lei nº 14.133/2021 autoriza expressamente a alteração dos contratos administrativos, inclusive aqueles que tenham por objeto compras e serviços comuns ou obras, desde que atendidos os requisitos legais.
    </div>

    @if($vm->envolveValor())
    <div class="clause-content">
        <strong>Aditivo de Valor (Acréscimo e/ou Supressão):</strong><br>
        O art. 124, inciso I, alínea "{{ $vm->alineaArt124() }}", da Lei nº 14.133/2021, permite a alteração quantitativa do
        contrato para melhor adequação às finalidades de interesse público, desde que haja justificativa formal, o
        aditivo esteja relacionado ao objeto originalmente contratado, não ocorra descaracterização do objeto e sejam
        observados os limites legais previstos no art. 125 da Lei nº 14.133/2021. No caso em análise, não há inovação
        indevida ou alteração substancial do objeto.
    </div>
    @endif

    @if($vm->envolvePrazo())
    <div class="clause-content">
        <strong>Aditivo de Prazo:</strong><br>
        @if($vm->contratoContinuo)
            Nos contratos de serviços e fornecimentos contínuos, o art. 107 da Lei nº 14.133/2021 admite a prorrogação
            sucessiva do prazo contratual, respeitada a vigência máxima decenal, desde que presentes os requisitos legais
            (interesse da Administração, preços e condições vantajosos, regularidade da contratada e disponibilidade orçamentária).
        @else
            Nos contratos com escopo predefinido, o art. 111 da Lei nº 14.133/2021 estabelece que o prazo de vigência
            será prorrogado quando o objeto não for concluído no período firmado, devendo a prorrogação ser formalizada
            por termo aditivo com indicação do novo prazo.
        @endif
        No caso concreto, verifica-se o atendimento dos requisitos legais, não se verificando impedimento jurídico à
        formalização da prorrogação.
    </div>
    @if($paragrafoResponsabilidade)
    <div class="clause-content">
        {{ $paragrafoResponsabilidade }}
    </div>
    @endif
    @endif

    <div class="section-title">IV – DAS CONDIÇÕES PARA A FORMALIZAÇÃO</div>
    <div class="clause-content" style="text-indent: 0;">
        Para a regularidade do aditivo, recomenda-se que:
        <ul class="list-content" style="list-style-type: none; margin-left: 0;">
            <li>✓ o termo aditivo seja devidamente motivado;</li>
            <li>✓ haja autorização expressa da autoridade competente;</li>
            <li>✓ sejam mantidas as cláusulas essenciais do contrato;</li>
            @if($vm->exigeDotacaoOrcamentaria())
                <li>✓ exista prévia dotação orçamentária;</li>
            @endif
            <li>✓ seja providenciada a publicação do termo aditivo nos meios legais.</li>
        </ul>
    </div>

    <div class="section-title">V – CONCLUSÃO</div>
    <div class="clause-content">
        Diante do exposto, esta Assessoria Jurídica OPINA FAVORAVELMENTE à celebração de {{ $vm->tituloTipoAlteracao() }} AO
        CONTRATO Nº {{ $vm->numeroContrato }}, nos termos propostos, por estar em conformidade com a Lei nº 14.133/2021, desde
        que seja providenciada a publicação do termo aditivo nos meios legais.
    </div>

    <div class="clause-content">
        É o parecer.
    </div>

    <div style="margin-top: 40px; text-align: left;">
        {{ $prefeitura->cidade ?? 'Cidade' }}, {{ \Carbon\Carbon::parse($data_selecionada ?? now())->translatedFormat('d \d\e F \d\e Y') }}.
    </div>

    @if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
        @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas')
    @else
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ mb_strtoupper($incidente->nome_parecerista ?? 'Assessor Jurídico / Parecerista') }}</strong><br>
            {{ $incidente->cargo_parecerista ?? 'Assessor Jurídico / Parecerista' }}<br>
            {{ $incidente->oab_parecerista ?? 'OAB/UF nº XXXXXX' }}
        </div>
    @endif
</body>
</html>
