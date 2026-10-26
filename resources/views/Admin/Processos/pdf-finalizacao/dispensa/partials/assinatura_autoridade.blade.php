{{-- Bloco de data e assinatura da autoridade competente (Ato, Adjudicação e Homologação da Dispensa) --}}
<div class="footer-signature">
    {{ $processo->prefeitura->cidade }},
    {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
</div>

@if ($hasSelectedAssinantes)
    {{-- Renderiza APENAS O PRIMEIRO assinante da lista --}}
    @php
        $primeiroAssinante = $assinantes[0];
    @endphp

    <div style="margin-top: 40px; text-align: center;">
        <div class="signature-block" style="display: inline-block; margin: 0 40px;">
            ___________________________________<br>
            <p style="line-height: 1.2;">
                {{ $primeiroAssinante['responsavel'] }} <br>
                <span>{{ $primeiroAssinante['unidade_nome'] }}</span>
            </p>
        </div>
    </div>
@else
    {{-- Bloco Padrão (Fallback) --}}
    <div class="signature-block" style="margin-top: 40px; text-align: center;">
        ___________________________________<br>
        <p style="line-height: 1.2;">
            {{ $processo->prefeitura->autoridade_competente }} <br>
            <span style="color: red;">[Cargo/Título Padrão - A ser ajustado]</span>
        </p>
    </div>
@endif
