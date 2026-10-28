{{-- Bloco de assinatura padrão, repetido em todos os templates de aditivo.
     Uso: @include('Admin.Processos.pdf.aditivos.partials.bloco-assinaturas', [
         'nomeFallback' => $incidente->nome_solicitante ?? ...,
         'cargoFallback' => $incidente->cargo_solicitante ?? 'Secretário(a)',
     ])
     Quando há seleção de assinantes via assinatura eletrônica (processo com
     Processo vinculado), ignora os fallbacks e lista todos os assinantes. --}}
@if(isset($documentoSelecao) && is_array($documentoSelecao->assinantes) && count($documentoSelecao->assinantes) > 0)
    @foreach($documentoSelecao->assinantes as $assinante)
        <div class="signature-block">
            ___________________________________<br>
            <strong>{{ \App\Helpers\NomeProprio::formatar($assinante['responsavel']) }}</strong><br>
            {{ \App\Helpers\Assinatura::cargo($assinante) }}
            @if(!empty($assinante['portaria']))
                <br>Portaria: {{ $assinante['portaria'] }}
            @endif
        </div>
    @endforeach
@else
    <div class="signature-block">
        ___________________________________<br>
        <strong>{{ \App\Helpers\NomeProprio::formatar($nomeFallback ?? ($prefeitura->autoridade_competente ?? 'Prefeito')) }}</strong><br>
        {{ $cargoFallback ?? 'Secretário(a)' }}
    </div>
@endif
