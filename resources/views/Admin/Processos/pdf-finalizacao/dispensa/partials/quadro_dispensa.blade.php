{{-- Quadro-resumo da contratação (Adjudicação e Homologação da Dispensa) --}}
<table style="width:100%; border-collapse:collapse; font-size:10px;">
    <tr>
        <td style="border:1px solid #000; padding:6px; width:30%; font-weight:bold;">
            OBJETO A SER CONTRATADO
        </td>
        <td style="border:1px solid #000; padding:6px; width:70%;">
            {!! strip_tags($processo->objeto) !!}
        </td>
    </tr>

    <tr>
        <td style="border:1px solid #000; padding:6px; font-weight:bold;">
            ADJUDICATÁRIO
        </td>
        <td style="border:1px solid #000; padding:6px;">
            {{ $contratadoTexto }}
        </td>
    </tr>

    @if ($exibirPrazoExecucao)
        <tr>
            <td style="border:1px solid #000; padding:6px; font-weight:bold;">
                PRAZO DE EXECUÇÃO
            </td>
            <td style="border:1px solid #000; padding:6px;">
                ________ dias
            </td>
        </tr>
    @endif

    <tr>
        <td style="border:1px solid #000; padding:6px; font-weight:bold;">
            PRAZO DE VIGÊNCIA
        </td>
        <td style="border:1px solid #000; padding:6px;">
            {{ $textoVigencia }}
        </td>
    </tr>

    <tr>
        <td style="border:1px solid #000; padding:6px; font-weight:bold;">
            VALOR TOTAL
        </td>
        <td style="border:1px solid #000; padding:6px;">
            {{ $valorTotalTexto }}
        </td>
    </tr>

    <tr>
        <td style="border:1px solid #000; padding:6px; font-weight:bold;">
            FUNDAMENTO LEGAL
        </td>
        <td style="border:1px solid #000; padding:6px;">
            {{ $fundamentoLegal }}
        </td>
    </tr>
</table>
