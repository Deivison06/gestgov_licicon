{{-- resources/views/Admin/Processos/partials/forms.blade.php --}}
<div class="p-3 mb-3 bg-white border border-gray-200 rounded-lg">
    {{-- Grupo: IDENTIFICAÇÃO DO ÓRGÃO REQUISITANTE --}}
    @if($campo === 'secretaria')
    <div class="mb-6">
        <div class="pb-2 mb-4 border-b-2 border-gray-300">
            <h3 class="text-lg font-bold text-gray-800">IDENTIFICAÇÃO DO ÓRGÃO REQUISITANTE</h3>
        </div>

        <x-form-field name="secretaria" label="Secretaria" />

        {{-- Unidade/Setor com select especial --}}
        <div class="flex items-start mb-4 space-x-2">
            <div class="flex-1">
                <label for="unidade_setor" class="block mb-1 text-sm font-medium text-gray-700">
                    Unidade / Setor / Departamento
                </label>
                <select id="unidade_setor" x-model="unidade_setor" @change="onUnidadeChange" :disabled="confirmed.unidade_setor" class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-[#009496] focus:border-[#009496] sm:text-sm">
                    <option value="">Selecione uma unidade</option>
                    @foreach ($processo->prefeitura->unidades as $unidade)
                    <option value="{{ $unidade->nome }}" data-responsavel="{{ $unidade->servidor_responsavel }}" {{ ($processo->detalhe->unidade_setor ?? '') == $unidade->nome ? 'selected' : '' }}>
                        {{ $unidade->nome }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex pt-6 space-x-1">
                <button type="button" @click="saveField('unidade_setor')" x-show="!confirmed.unidade_setor" :disabled="!unidade_setor" class="flex items-center justify-center w-8 h-8 transition-colors duration-200 rounded-lg" :class="!unidade_setor ? 'bg-gray-400 cursor-not-allowed text-white' : 'bg-green-500 hover:bg-green-600 text-white'">
                    ✓
                </button>
                <button type="button" @click="toggleConfirm('unidade_setor')" x-show="confirmed.unidade_setor" class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-lg hover:bg-red-600">
                    ✗
                </button>
            </div>
        </div>

        <x-form-field name="servidor_responsavel" label="Servidor Responsável" />

    </div>

    {{-- Campos de Texto Simples --}}
    @elseif($campo === 'justificativa')
    <x-form-field name="justificativa" label="Justificativa da Necessidade da Contratação" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'descricao_necessidade')
    <x-form-field name="descricao_necessidade" label="DESCRIÇÃO DA NECESSIDADE" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'descricao_necessidade_autorizacao')
    <x-form-field name="descricao_necessidade_autorizacao" label="DESCRIÇÃO DA NECESSIDADE DE AUTORIZAÇÃO PARA ELABORAÇÃO DO ETP" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'incluir_requisito_cada_caso_concreto')
    <x-form-field name="incluir_requisito_cada_caso_concreto" label="REQUISITOS REFERENTES A CADA CASO CONCRETO" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'solucoes_disponivel_mercado')
    <x-form-field name="solucoes_disponivel_mercado" label="SOLUÇÕES DISPONÍVEIS NO MERCADO" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'solucao_escolhida')
    <x-form-field name="solucao_escolhida" label="SOLUÇÃO ESCOLHIDA" />

    @elseif($campo === 'justificativa_solucao_escolhida')
    <x-form-field name="justificativa_solucao_escolhida" label="JUSTIFICATIVA DA SOLUÇÃO ESCOLHIDA" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'especificacao_servicos_imovel')
        @if ($processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE && $processo->tipo_contratacao === \App\Enums\TipoContratacaoEnum::IMOVEL)
            <x-form-field name="especificacao_servicos_imovel" label="ESPECIFICAÇÃO DOS SERVIÇOS DO IMÓVEL" type="textarea" rows="5" />
        @else
            <x-form-field name="especificacao_servicos_imovel" label="ESPECIFICAÇÃO DOS SERVIÇOS" type="textarea" rows="5" disabled />
        @endif

    @elseif($campo === 'razao_escolha_contratado')
    <x-form-field name="razao_escolha_contratado" label="RAZÃO DA ESCOLHA DO CONTRATADO" type="textarea" rows="5" />

    @elseif($campo === 'obrigacoes_contratado_extras')
    <x-form-field name="obrigacoes_contratado_extras" label="OBRIGAÇÕES DO CONTRATADO EXTRAS" type="textarea" rows="5" />

    @elseif($campo === 'obrigacoes_contratante_extras')
    <x-form-field name="obrigacoes_contratante_extras" label="OBRIGAÇÕES DO CONTRATANTE EXTRAS" type="textarea" rows="5" />

    @elseif($campo === 'resultado_pretendidos')
    <x-form-field name="resultado_pretendidos" label="RESULTADOS PRETENDIDOS" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'impacto_ambiental')
    <x-form-field name="impacto_ambiental" label="IMPACTOS AMBIENTAIS" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'riscos_extra')
    <x-form-field name="riscos_extra" label="RISCOS EXTRAS" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'problema_resolvido')
    <x-form-field name="problema_resolvido" label="Problema Resumido" type="textarea" rows="5" :ia="true" :iaProcessoId="$processo->id" />

    @elseif($campo === 'nome_equipe_planejamento')
    <x-form-field name="nome_equipe_planejamento" label="EQUIPE DE PLANEJAMENTO" />

    @elseif($campo === 'responsavel_equipe_planejamento' && !($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA &&
        $processo->tipo_procedimento === \App\Enums\TipoProcedimentoEnum::OBRA))
        <x-form-field name="responsavel_equipe_planejamento" label="RESPONSAVEL EQUIPE DE PLANEJAMENTO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'servidor_responsavel')->toArray()" placeholder="Selecione um Responsavel" />

    @elseif($campo === 'agente_contratacao')
    <x-form-field name="agente_contratacao" label="Agente contratação" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'servidor_responsavel')->toArray()" placeholder="Selecione um Responsavel" />

    @elseif($campo === 'prazo_entrega')
    <x-form-field name="prazo_entrega" label="Prazo de Entrega / Execução" />

    {{-- ATUALIZADO: Agora inclui DISPENSA também --}}
    @elseif($campo === 'local_entrega' && ($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO || $processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA))
    <x-form-field name="local_entrega" label="Local(is) e Horário(s) de Entrega" />

    @elseif($campo === 'fiscais')
    <x-form-field name="fiscais" label="Fiscal(is) Indicado(s)" />

    @elseif($campo === 'gestor')
    <x-form-field name="gestor" label="Gestor Indicado" />

    @elseif($campo === 'valor_estimado')
    <div class="flex items-start mb-4 space-x-2">
        <div class="flex-1">
            <div class="flex items-center justify-between mb-1">
                <label for="valor_estimado" class="block text-sm font-medium text-gray-700">Valor Estimado</label>
                @if($processo->etp && in_array($processo->modalidade, [\App\Enums\ModalidadeEnum::DISPENSA, \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO], true))
                <button type="button" @click="puxarValorEstimadoPesquisaPreco()"
                    class="flex items-center gap-1 px-2 py-1 text-xs font-medium text-white transition-colors rounded-md bg-[#009496] hover:bg-[#007779] shadow-sm"
                    title="Preencher com o valor total da pesquisa de preço">
                    <i class="fas fa-sync-alt"></i><span>Puxar da Pesquisa de Preço</span>
                </button>
                @endif
            </div>
            <input type="text" id="valor_estimado" name="valor_estimado" x-model="valor_estimado"
                :disabled="confirmed.valor_estimado"
                class="block w-full mt-1 border-gray-300 rounded-lg shadow-sm sm:text-sm focus:ring-[#009496] focus:border-[#009496] disabled:bg-gray-100 disabled:cursor-not-allowed">
        </div>
        <div class="flex pt-6 space-x-1">
            <button type="button" @click="saveField('valor_estimado')" x-show="!confirmed.valor_estimado"
                class="flex items-center justify-center w-8 h-8 text-white transition-colors duration-200 bg-green-500 rounded-lg hover:bg-green-600" title="Confirmar">
                ✓
            </button>
            <button type="button" @click="toggleConfirm('valor_estimado')" x-show="confirmed.valor_estimado"
                class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-lg hover:bg-red-600" title="Editar">
                ✗
            </button>
        </div>
    </div>

    @elseif($campo === 'dotacao_orcamentaria')
    <x-form-field name="dotacao_orcamentaria" label="CASO A LICITAÇÃO NÃO SEJA DO TIPO SRP, DESCREVA ABAIXO A DOTAÇÃO ORÇAMENTÁRIA" type="textarea" rows="5" />

    @elseif($campo === 'tratamento_diferenciado_MEs_eEPPs')
    <x-form-field name="tratamento_diferenciado_MEs_eEPPs" label="TRATAMENTO DIFERENCIA A MEs e EPPs" type="textarea" rows="5" />

    @elseif($campo === 'intervalo_lances' &&
        $processo->modalidade !== \App\Enums\ModalidadeEnum::DISPENSA)
    <x-form-field name="intervalo_lances" label="INTERVALO ENTRE OS LANCES" />

    @elseif($campo === 'portal')
    <x-form-field name="portal" label="PORTAL UTILIZADO" />

    @elseif($campo === 'regularidade_fisica')
    <x-form-field name="regularidade_fisica" label="Regularidade Fiscal e Trabalhista:" type="textarea" rows="5" />

    @elseif($campo === 'qualificacao_economica')
    <x-form-field name="qualificacao_economica" label="Qualificação Econômico-financeira:" type="textarea" rows="5" />

    @elseif($campo === 'exigencias_tecnicas')
    <x-form-field name="exigencias_tecnicas" label="EXIGÊNCIAS TÉCNICAS" type="textarea" rows="5" />

    @elseif($campo === 'numero_items')
    <x-form-field name="numero_items" label="Numero Items" />

    @elseif($campo === 'orgao_responsavel')
    <x-form-field name="orgao_responsavel" label="Órgão Responsável" />

    @elseif($campo === 'cnpj')
    <x-form-field name="cnpj" label="CNPJ" />

    @elseif($campo === 'endereco')
    <x-form-field name="endereco" label="Endereço" />

    @elseif($campo === 'responsavel')
    <x-form-field name="responsavel" label="Responsável" />

    @elseif($campo === 'cpf_responsavel')
    <x-form-field name="cpf_responsavel" label="CPF Responsável" />

    @elseif($campo === 'razao_social')
    <x-form-field name="razao_social" label="Razão Social" />

    @elseif($campo === 'cnpj_empresa_vencedora')
    <x-form-field name="cnpj_empresa_vencedora" label="CNPJ Empresa Vencedora" />

    @elseif($campo === 'endereco_empresa_vencedora')
    <x-form-field name="endereco_empresa_vencedora" label="Endereço Empresa Vencedora" />

    @elseif($campo === 'representante_legal_empresa')
    <x-form-field name="representante_legal_empresa" label="Representante Legal Empresa" />

    @elseif($campo === 'cpf_representante')
    <x-form-field name="cpf_representante" label="CPF Representante" />

    @elseif(
    $campo === 'endereco_imovel' && $processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE && $processo->tipo_contratacao === \App\Enums\TipoContratacaoEnum::IMOVEL)
    <x-form-field name="endereco_imovel" label="Endereço do Imóvel" />

    @elseif($campo === 'prazo_inicio_prestacao_servico')
    <x-form-field name="prazo_inicio_prestacao_servico" label="Prazo Início Prestação Serviço" type="datetime" />

    @elseif($campo === 'prazo_final_prestacao_servico')
    <x-form-field name="prazo_final_prestacao_servico" label="Prazo Final Prestação Serviço" type="datetime" />

    @elseif($campo === 'valor_mensal' && $processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE && $processo->tipo_contratacao === \App\Enums\TipoContratacaoEnum::IMOVEL)
    <x-form-field name="valor_mensal" label="Valor Mensal" />

{{--    @elseif($campo === 'valor_total')--}}
{{--    <x-form-field name="valor_total" label="Valor Total" />--}}

    {{-- Campos Radio --}}
    @elseif($campo === 'tipo_srp')
    <x-form-field name="tipo_srp" label="Esse Processo é do tipo SRP?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'prevista_plano_anual')
    <x-form-field name="prevista_plano_anual" label="A CONTRATAÇÃO ESTÁ PREVISTA NO PLANO DE CONTRATAÇÃO ANUAL?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    {{-- ATUALIZADO: Agora inclui DISPENSA também --}}
    @elseif($campo === 'contratacoes_anteriores' && ($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO || $processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA))
    <x-form-field name="contratacoes_anteriores" label="Houve contratações anteriores?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'objeto_continuado')
    <x-form-field name="objeto_continuado" label="Objeto Continuado?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'inversao_fase' && $processo->modalidade !== \App\Enums\ModalidadeEnum::INEXIGIBILIDADE)
    <x-form-field name="inversao_fase" label="Documento contém inversão de fase?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'exigencia_garantia_proposta')
    <x-form-field name="exigencia_garantia_proposta" label="EXIGÊNCIA DE GARANTIA DE PROPOSTA" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'exigencia_garantia_contrato')
    <x-form-field name="exigencia_garantia_contrato" label="EXIGÊNCIA DE GARANTIA DE CONTRATO" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'participacao_exclusiva_mei_epp')
    <x-form-field name="participacao_exclusiva_mei_epp" label="Itens destinados à participação exclusiva para MEI/ME/EPP até R$ 80.000,00" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'reserva_cotas_mei_epp')
    <x-form-field name="reserva_cotas_mei_epp" label="Itens com reserva de cotas destinados à participação exclusiva para MEI/ME/EPP" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'prioridade_contratacao_mei_epp')
    <x-form-field name="prioridade_contratacao_mei_epp" label="Prioridade de contratação para MEI/ME/EPP sediadas local ou regionalmente (até 10%)" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    {{-- Campos Checkbox --}}
    @elseif($campo === 'instrumento_vinculativo')
    <x-form-field name="instrumento_vinculativo" label="Instrumento Vinculativo" type="checkbox" :options="[
            'contrato' => 'Contrato',
            'ata_registro_precos' => 'Ata de Registro de Preços',
            'outro' => 'Outro'
        ]" />

    @elseif($campo === 'prazo_vigencia')
    <x-form-field name="prazo_vigencia" label="Prazo de Vigência do Objeto" type="checkbox" :options="[
            'exercicio_financeiro' => 'Exercício financeiro da contratação (até 31/12)',
            '12_meses' => 'Vigência de 12 meses',
            'outro' => 'Outro'
        ]" />

    {{-- Campos File --}}
    {{-- ATUALIZADO: Agora inclui DISPENSA também --}}
    @elseif($campo === 'itens_e_seus_quantitativos_xml')
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-gray-800">📦 Itens e Seus Quantitativos</label>
            @php
                $temItensEtp = $processo->etp && $processo->etp->all_itens->count() > 0;
            @endphp
            @if($temItensEtp)
            <a href="{{ route('admin.processos.pesquisa_preco_itens', $processo->id) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Pesquisar preços por item
            </a>
            @endif
        </div>

        {{-- Caso haja ETP vinculado --}}
        @if($processo->etp)
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-link text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-emerald-900">ETP Inteligente Vinculado</h5>
                            <p class="text-xs text-emerald-700 font-medium">
                                Identificador: <span class="font-bold">ETP-{{ str_pad($processo->etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $processo->etp->created_at->format('Y') }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.etps.show', $processo->etp->id) }}" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                            <i class="fas fa-eye mr-1.5"></i> Ver ETP
                        </a>
                        <button type="button" @click="desvincularEtp()"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-red-700 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-all shadow-sm">
                            <i class="fas fa-unlink mr-1.5"></i> Desvincular
                        </button>
                    </div>
                </div>
                
                <div class="mt-3 pt-3 border-t border-emerald-100">
                    <p class="text-xs text-emerald-800">
                        <i class="fas fa-info-circle mr-1"></i> Os itens deste ETP serão usados automaticamente na geração dos documentos.
                    </p>

                    {{-- Expandir/recolher itens do ETP --}}
                    @php
                        $isLote = $processo->etp->tipo_contratacao === 'lote';
                        $etpItens = $processo->etp->all_itens;
                    @endphp
                    @if($etpItens->count() > 0)
                    <div x-data="{ openEtpItens: false }" class="mt-2">
                        <button type="button" @click="openEtpItens = !openEtpItens"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors">
                            <i :class="openEtpItens ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-[9px]"></i>
                            <span x-text="openEtpItens ? 'Recolher itens' : 'Ver {{ $etpItens->count() }} {{ $etpItens->count() === 1 ? 'item' : 'itens' }} {{ $isLote ? '(distribuídos em lotes)' : '' }}'"></span>
                        </button>

                        <div x-show="openEtpItens" x-transition class="mt-2 overflow-x-auto rounded-lg border border-emerald-200">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-emerald-100 text-emerald-800 uppercase text-[10px] font-bold">
                                    <tr>
                                        <th class="px-2 py-1.5 w-8 text-center">#</th>
                                        <th class="px-2 py-1.5">Descrição</th>
                                        <th class="px-2 py-1.5 w-16 text-center">Und.</th>
                                        <th class="px-2 py-1.5 w-20 text-right">Qtd.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-50">
                                    @if($isLote)
                                        @foreach($processo->etp->lotes as $lote)
                                            <tr class="bg-emerald-50/80">
                                                <td colspan="4" class="px-2 py-1.5 font-bold text-emerald-900 border-y border-emerald-100">
                                                    <i class="fas fa-layer-group mr-1"></i> Lote: {{ $lote->nome }}
                                                </td>
                                            </tr>
                                            @foreach($lote->itens as $idx => $item)
                                            <tr class="bg-white hover:bg-emerald-50/50">
                                                <td class="px-2 py-1.5 text-center">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">{{ $idx + 1 }}</span>
                                                </td>
                                                <td class="px-2 py-1.5 text-gray-700">{{ $item->descricao_item }}</td>
                                                <td class="px-2 py-1.5 text-center text-gray-500">{{ $item->pivot->unidade ?? '-' }}</td>
                                                <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item->pivot->quantidade }}</td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    @else
                                        @foreach($etpItens as $idx => $item)
                                        <tr class="bg-white hover:bg-emerald-50/50">
                                            <td class="px-2 py-1.5 text-center">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">{{ $idx + 1 }}</span>
                                            </td>
                                            <td class="px-2 py-1.5 text-gray-700">{{ $item->descricao_item }}</td>
                                            <td class="px-2 py-1.5 text-center text-gray-500">{{ $item->pivot->unidade ?? '-' }}</td>
                                            <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item->pivot->quantidade }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="bg-emerald-50 border-t border-emerald-200">
                                        <td colspan="3" class="px-2 py-1.5 text-[10px] text-emerald-700 font-semibold uppercase">Total Geral</td>
                                        <td class="px-2 py-1.5 text-right text-xs font-bold text-emerald-700">{{ $etpItens->count() }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Campo de upload normal + Botão de busca --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-grow">
                    <x-form-field name="itens_e_seus_quantitativos_xml" label="" type="file" accept=".xml, .xlsx, .xls, .csv" />
                </div>
                <div class="flex items-end pb-1">
                    <button type="button" @click="abrirModalSelecaoEtp()"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all shadow-md group">
                        <i class="fas fa-search-plus mr-2 group-hover:scale-110 transition-transform"></i>
                        Buscar ETP Inteligente
                    </button>
                </div>
            </div>
            <p class="text-[10px] text-gray-500 italic mt-1">* Vincular um ETP dispensa a necessidade de fazer o upload do arquivo XLS/XML.</p>
        @endif
    </div>

    @elseif($campo === 'projeto_basico_pdf')
    @php
        $etpFornecePdfProjetoBasico = $processo->etp
            && !empty($processo->etp->cotacao_path)
            && in_array($processo->modalidade, [\App\Enums\ModalidadeEnum::CONCORRENCIA, \App\Enums\ModalidadeEnum::INEXIGIBILIDADE]);
        $temAnexoManualProjetoBasico = app(\App\Services\ProcessoDocumentoService::class)->existeAnexoManual($processo, 'projeto_basico_pdf');
        $projetoBasicoDoEtp = $etpFornecePdfProjetoBasico && !$temAnexoManualProjetoBasico;
    @endphp
    @if($projetoBasicoDoEtp)
        <div x-data="{ forcarUploadProjetoBasico: false }">
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm" x-show="!forcarUploadProjetoBasico">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-file-pdf text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-emerald-900">Projeto Básico do ETP Vinculado</h5>
                            <p class="text-xs text-emerald-700">O PDF anexado no ETP será usado automaticamente como Projeto Básico deste processo.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.etps.show', $processo->etp->id) }}" target="_blank"
                       class="flex-shrink-0 inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                        <i class="fas fa-eye mr-1.5"></i> Ver ETP
                    </a>
                </div>
                <div class="mt-3 pt-3 border-t border-emerald-100">
                    <button type="button" @click="forcarUploadProjetoBasico = true"
                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 underline transition-colors">
                        Enviar meu próprio PDF mesmo assim
                    </button>
                </div>
            </div>
            <div x-show="forcarUploadProjetoBasico" x-cloak>
                <p class="text-[10px] text-amber-600 italic mb-1">
                    ⚠️ Isso pode gerar divergência entre o ETP e o Projeto Básico do processo.
                </p>
                <x-form-field name="projeto_basico_pdf" label="📎 Anexar PDF Projeto Básico" type="file" accept="application/pdf" />
            </div>
        </div>
    @else
        <x-form-field name="projeto_basico_pdf" label="📎 Anexar PDF Projeto Básico" type="file" accept="application/pdf" />
        @if($etpFornecePdfProjetoBasico && $temAnexoManualProjetoBasico)
            <div class="mt-2 flex items-center gap-2 p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-[11px] text-amber-700 flex-1">
                    Há um PDF anexado manualmente. O ETP vinculado também possui um PDF disponível.
                </p>
                <button type="button" @click="removerAnexoEtp('projeto_basico_pdf')"
                        class="flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-all">
                    <i class="fas fa-rotate-left mr-1"></i> Voltar a usar o PDF do ETP
                </button>
            </div>
        @endif
    @endif

    @elseif($campo === 'itens_especificaca_quantitativos_xml')
    @if($processo->etp)
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <i class="fas fa-link text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-emerald-900">ETP Inteligente Vinculado</p>
                <p class="text-xs text-emerald-700">As especificações e quantitativos serão gerenciados pelo ETP. O upload de XLS está desabilitado.</p>
            </div>
            <a href="{{ route('admin.etps.show', $processo->etp->id) }}" target="_blank"
               class="flex-shrink-0 inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                <i class="fas fa-eye mr-1.5"></i> Ver ETP
            </a>
        </div>
    @else
        <x-form-field name="itens_especificaca_quantitativos_xml" label="📦 Itens e Seus quantitativos e especificações" type="file" accept=".xml, .xlsx, .xls, .csv" />
    @endif

    @elseif($campo === 'descricao_e_quantitativos_itens_xml')
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-gray-800">📦 Descrição e Quantitativos dos Itens</label>
            @php
                $itensXlsRaw = $processo->detalhe->descricao_e_quantitativos_itens_xml ?? [];
                $temItensXls = is_array($itensXlsRaw) && count($itensXlsRaw) > 0;
            @endphp
            @if($temItensXls)
            <a href="{{ route('admin.processos.pesquisa_preco_itens', $processo->id) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Pesquisar preços por item
            </a>
            @endif
        </div>

        @if($processo->etp)
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-link text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-emerald-900">ETP Inteligente Vinculado</h5>
                            <p class="text-xs text-emerald-700 font-medium">
                                Identificador: <span class="font-bold">ETP-{{ str_pad($processo->etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $processo->etp->created_at->format('Y') }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.etps.show', $processo->etp->id) }}" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                            <i class="fas fa-eye mr-1.5"></i> Ver ETP
                        </a>
                        @if($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA)
                        <button type="button" @click="desvincularEtp()"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-red-700 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-all shadow-sm">
                            <i class="fas fa-unlink mr-1.5"></i> Desvincular
                        </button>
                        @endif
                    </div>
                </div>
                
                <div class="mt-3 pt-3 border-t border-emerald-100">
                    <p class="text-xs text-emerald-800">
                        <i class="fas fa-info-circle mr-1"></i> Os itens deste ETP serão usados automaticamente na geração dos documentos. O upload de XLS está desabilitado.
                    </p>

                    {{-- Expandir/recolher itens do ETP --}}
                    @php
                        $isLote = $processo->etp->tipo_contratacao === 'lote';
                        $etpItens = $processo->etp->all_itens;
                    @endphp
                    @if($etpItens->count() > 0)
                    <div x-data="{ openEtpItens: false }" class="mt-2">
                        <button type="button" @click="openEtpItens = !openEtpItens"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors">
                            <i :class="openEtpItens ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-[9px]"></i>
                            <span x-text="openEtpItens ? 'Recolher itens' : 'Ver {{ $etpItens->count() }} {{ $etpItens->count() === 1 ? 'item' : 'itens' }} {{ $isLote ? '(distribuídos em lotes)' : '' }}'"></span>
                        </button>

                        <div x-show="openEtpItens" x-transition class="mt-2 overflow-x-auto rounded-lg border border-emerald-200">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-emerald-100 text-emerald-800 uppercase text-[10px] font-bold">
                                    <tr>
                                        <th class="px-2 py-1.5 w-8 text-center">#</th>
                                        <th class="px-2 py-1.5">Descrição</th>
                                        <th class="px-2 py-1.5 w-16 text-center">Und.</th>
                                        <th class="px-2 py-1.5 w-20 text-right">Qtd.</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-emerald-50">
                                    @if($isLote)
                                        @foreach($processo->etp->lotes as $lote)
                                            <tr class="bg-emerald-50/80">
                                                <td colspan="4" class="px-2 py-1.5 font-bold text-emerald-900 border-y border-emerald-100">
                                                    <i class="fas fa-layer-group mr-1"></i> Lote: {{ $lote->nome }}
                                                </td>
                                            </tr>
                                            @foreach($lote->itens as $idx => $item)
                                            <tr class="bg-white hover:bg-emerald-50/50">
                                                <td class="px-2 py-1.5 text-center">
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">{{ $idx + 1 }}</span>
                                                </td>
                                                <td class="px-2 py-1.5 text-gray-700">{{ $item->descricao_item }}</td>
                                                <td class="px-2 py-1.5 text-center text-gray-500">{{ $item->pivot->unidade ?? '-' }}</td>
                                                <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item->pivot->quantidade }}</td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    @else
                                        @foreach($etpItens as $idx => $item)
                                        <tr class="bg-white hover:bg-emerald-50/50">
                                            <td class="px-2 py-1.5 text-center">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">{{ $idx + 1 }}</span>
                                            </td>
                                            <td class="px-2 py-1.5 text-gray-700">{{ $item->descricao_item }}</td>
                                            <td class="px-2 py-1.5 text-center text-gray-500">{{ $item->pivot->unidade ?? '-' }}</td>
                                            <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item->pivot->quantidade }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="bg-emerald-50 border-t border-emerald-200">
                                        <td colspan="3" class="px-2 py-1.5 text-[10px] text-emerald-700 font-semibold uppercase">Total Geral</td>
                                        <td class="px-2 py-1.5 text-right text-xs font-bold text-emerald-700">{{ $etpItens->count() }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            {{-- DISPENSA: a vinculação do ETP acontece aqui, na Formalização da Demanda,
                 pois o fluxo de dispensa não possui a seção de Estudo Técnico (onde as
                 demais modalidades vinculam o ETP). Sem este botão, não havia como
                 vincular um ETP num processo de dispensa. --}}
            @if($processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA)
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-grow">
                        <x-form-field name="descricao_e_quantitativos_itens_xml" label="" type="file" accept=".xml, .xlsx, .xls, .csv" />
                    </div>
                    <div class="flex items-end pb-1">
                        <button type="button" @click="abrirModalSelecaoEtp()"
                                class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-white bg-[#009496] rounded-lg hover:bg-[#007a7a] transition-all shadow-md group">
                            <i class="fas fa-search-plus mr-2 group-hover:scale-110 transition-transform"></i>
                            Buscar ETP Inteligente
                        </button>
                    </div>
                </div>
                <p class="text-[10px] text-gray-500 italic mt-1">* Vincular um ETP dispensa a necessidade de fazer o upload do arquivo XLS/XML.</p>
            @else
                <x-form-field name="descricao_e_quantitativos_itens_xml" label="" type="file" accept=".xml, .xlsx, .xls, .csv" />
            @endif
        @endif

        {{-- Preview de itens já importados via XLS --}}
        @php $itensXls = $processo->detalhe->descricao_e_quantitativos_itens_xml ?? []; @endphp
        @if(is_array($itensXls) && count($itensXls) > 0)
        <div x-data="{ openXlsItens: false }" class="mt-2">
            <button type="button" @click="openXlsItens = !openXlsItens"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700 hover:text-teal-900 transition-colors">
                <i :class="openXlsItens ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-[9px]"></i>
                <span x-text="openXlsItens ? 'Recolher itens' : '{{ count($itensXls) }} {{ count($itensXls) === 1 ? 'item importado' : 'itens importados' }} — clique para ver'"></span>
            </button>

            <div x-show="openXlsItens" x-transition class="mt-2 overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="px-2 py-1.5 w-8 text-center">#</th>
                            <th class="px-2 py-1.5">Descrição</th>
                            <th class="px-2 py-1.5 w-16 text-center">Und.</th>
                            <th class="px-2 py-1.5 w-20 text-right">Qtd.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($itensXls as $idx => $item)
                        <tr class="bg-white hover:bg-gray-50">
                            <td class="px-2 py-1.5 text-center text-gray-400">{{ $item['numero'] ?? ($idx + 1) }}</td>
                            <td class="px-2 py-1.5 text-gray-700">{{ $item['descricao'] }}</td>
                            <td class="px-2 py-1.5 text-center text-gray-500">{{ $item['und'] ?? '-' }}</td>
                            <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item['quantidade'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t border-gray-200">
                            <td colspan="3" class="px-2 py-1.5 text-[10px] text-gray-500 font-semibold uppercase">Total</td>
                            <td class="px-2 py-1.5 text-right text-xs font-bold text-gray-600">{{ count($itensXls) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif
    </div>

    @elseif($campo === 'info_extras')
    <x-form-field name="info_extras" label="Informações Extras" type="textarea" rows="5" />

    @elseif($campo === 'tipo_relatorio_analise_mercado')
    <div class="space-y-3">
        <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-bold text-gray-700 uppercase">Tipo de Relatório</label>
            <span x-show="confirmed.tipo_relatorio_analise_mercado" class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Salvo
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach(['tce' => 'Painel TCE', 'fornecedor_local' => 'Fornecedor Local', 'cesta_preco' => 'Cesta de Preços', 'pncp' => 'PNCP'] as $valor => $label)
            <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer transition-colors"
                   :class="tipo_relatorio_analise_mercado === '{{ $valor }}' ? 'bg-blue-50 border-blue-400 text-blue-800' : 'bg-white border-gray-200 hover:bg-gray-50'">
                <input type="radio"
                       name="tipo_relatorio_analise_mercado"
                       value="{{ $valor }}"
                       x-model="tipo_relatorio_analise_mercado"
                       @change="saveField('tipo_relatorio_analise_mercado')"
                       class="sr-only">
                <span class="text-xs font-semibold">{{ $label }}</span>
            </label>
            @endforeach
        </div>

        {{-- Links contextuais conforme tipo selecionado --}}
        <div x-show="tipo_relatorio_analise_mercado === 'fornecedor_local'" x-cloak class="mt-2">
            <a href="{{ route('admin.processos.pesquisa_preco_fornecedor_local', $processo->id) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-all">
                <i class="fas fa-store mr-1"></i> Inserir preços de fornecedores locais
            </a>
            @php $countFl = !empty($processo->detalhe?->fornecedor_local_precos) ? count($processo->detalhe->fornecedor_local_precos) : 0; @endphp
            @if($countFl > 0)
            <span class="ml-2 inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200">
                <i class="fas fa-check-circle"></i> {{ $countFl }} {{ Str::plural('item', $countFl) }} com preços
            </span>
            @endif
        </div>

        {{-- Link TCE (apenas quando tipo = tce e ETP vinculado) --}}
        @if($processo->etp)
        <div x-show="tipo_relatorio_analise_mercado === 'tce' || !tipo_relatorio_analise_mercado" x-cloak class="mt-2">
            <a href="{{ route('admin.processos.pesquisa_preco_tce', $processo->id) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all">
                <i class="fas fa-balance-scale mr-1"></i> Inserir preços TCE
            </a>
            @php
                $painelPrecoTce = $processo->detalhe?->painel_preco_tce;
                // Legado: alguns registros ficaram com JSON codificado em dobro; decodifica de novo se ainda vier como string.
                $painelPrecoTce = is_string($painelPrecoTce) ? (json_decode($painelPrecoTce, true) ?? []) : ($painelPrecoTce ?? []);
                $countTceLink = count($painelPrecoTce);
            @endphp

            @if($countTceLink)
                <span class="ml-2 inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-full border border-blue-200">
                    <i class="fas fa-check-circle"></i>
                    {{ $countTceLink }} {{ Str::plural('preço', $countTceLink) }} vinculado{{ $countTceLink > 1 ? 's' : '' }}
                </span>
            @endif
        </div>
        @endif

        <div x-show="['cesta_preco', 'pncp'].includes(tipo_relatorio_analise_mercado)" x-cloak class="mt-2">
            <a href="{{ route('admin.processos.pesquisa_preco_itens', $processo->id) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all">
                <i class="fas fa-search mr-1.5"></i> Pesquisar preços no PNCP
            </a>
            @php $countPesq = $processo->pesquisaPrecoItens()->where('orgao_nome', '!=', 'PREÇOS DO FORNECEDOR LOCAL')->count(); @endphp
            @if($countPesq > 0)
            <span class="ml-2 inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-200">
                <i class="fas fa-check-circle"></i> {{ $countPesq }} {{ Str::plural('referência', $countPesq) }} PNCP
            </span>
            @endif
        </div>

        {{-- Card completo do ETP vinculado — visível em todos os tipos de relatório --}}
        @if($processo->etp)
        @php
            $isLoteRelatorio  = $processo->etp->tipo_contratacao === 'lote';
            $etpItensRelatorio = $processo->etp->all_itens;
        @endphp
        <div class="mt-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-link text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-sm font-bold text-emerald-900">ETP Inteligente Vinculado</h5>
                        <p class="text-xs text-emerald-700 font-medium">
                            Identificador: <span class="font-bold">ETP-{{ str_pad($processo->etp->id, 4, '0', STR_PAD_LEFT) }}/{{ $processo->etp->created_at->format('Y') }}</span>
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.etps.show', $processo->etp->id) }}" target="_blank"
                   class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                    <i class="fas fa-eye mr-1.5"></i> Ver ETP
                </a>
            </div>

            @if($etpItensRelatorio->count() > 0)
            <div class="mt-3 pt-3 border-t border-emerald-100">
                <div x-data="{ openEtpRelatorio: false }">
                    <button type="button" @click="openEtpRelatorio = !openEtpRelatorio"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition-colors">
                        <i :class="openEtpRelatorio ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-[9px]"></i>
                        <span x-text="openEtpRelatorio ? 'Recolher itens' : 'Ver {{ $etpItensRelatorio->count() }} {{ $etpItensRelatorio->count() === 1 ? 'item' : 'itens' }} {{ $isLoteRelatorio ? '(distribuídos em lotes)' : '' }}'"></span>
                    </button>

                    <div x-show="openEtpRelatorio" x-transition class="mt-2 overflow-x-auto rounded-lg border border-emerald-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-emerald-100 text-emerald-800 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="px-2 py-1.5 w-8 text-center">#</th>
                                    <th class="px-2 py-1.5">Descrição</th>
                                    <th class="px-2 py-1.5 w-16 text-center">Und.</th>
                                    <th class="px-2 py-1.5 w-20 text-right">Qtd.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-50">
                                @if($isLoteRelatorio)
                                    @foreach($processo->etp->lotes as $lote)
                                        <tr class="bg-emerald-50/80">
                                            <td colspan="4" class="px-2 py-1.5 font-bold text-emerald-900 border-y border-emerald-100">
                                                <i class="fas fa-layer-group mr-1"></i> Lote: {{ $lote->nome }}
                                            </td>
                                        </tr>
                                        @foreach($lote->itens as $idx => $item)
                                        <tr class="bg-white hover:bg-emerald-50/50">
                                            <td class="px-2 py-1.5 text-center">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">{{ $idx + 1 }}</span>
                                            </td>
                                            <td class="px-2 py-1.5 text-gray-700">{{ $item->descricao_item }}</td>
                                            <td class="px-2 py-1.5 text-center text-gray-500">{{ $item->pivot->unidade ?? '-' }}</td>
                                            <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item->pivot->quantidade }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    @foreach($etpItensRelatorio as $idx => $item)
                                    <tr class="bg-white hover:bg-emerald-50/50">
                                        <td class="px-2 py-1.5 text-center">
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">{{ $idx + 1 }}</span>
                                        </td>
                                        <td class="px-2 py-1.5 text-gray-700">{{ $item->descricao_item }}</td>
                                        <td class="px-2 py-1.5 text-center text-gray-500">{{ $item->pivot->unidade ?? '-' }}</td>
                                        <td class="px-2 py-1.5 text-right font-medium text-gray-700">{{ $item->pivot->quantidade }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50 border-t border-emerald-200">
                                    <td colspan="3" class="px-2 py-1.5 text-[10px] text-emerald-700 font-semibold uppercase">Total Geral</td>
                                    <td class="px-2 py-1.5 text-right text-xs font-bold text-emerald-700">{{ $etpItensRelatorio->count() }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    @elseif($campo === 'painel_preco_tce')
    <div x-show="tipo_relatorio_analise_mercado === 'tce' || !tipo_relatorio_analise_mercado" x-cloak>
    <div class="space-y-2">
        @if($processo->etp)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.processos.pesquisa_preco_tce', $processo->id) }}"
                   target="_blank"
                   class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-blue-600 border border-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                    <i class="fas fa-balance-scale mr-1.5"></i> Inserir preços TCE
                </a>
                <a href="{{ route('admin.processos.pesquisa_preco_itens', $processo->id) }}"
                   target="_blank"
                   class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-300 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                    <i class="fas fa-search mr-1.5"></i> Pesquisar no PNCP
                </a>
            </div>
            @php
                $painelPrecoTceCard = $processo->detalhe?->painel_preco_tce;
                // Legado: alguns registros ficaram com JSON codificado em dobro; decodifica de novo se ainda vier como string.
                $painelPrecoTceCard = is_string($painelPrecoTceCard) ? (json_decode($painelPrecoTceCard, true) ?? []) : ($painelPrecoTceCard ?? []);
                $countTce = count($painelPrecoTceCard);
            @endphp
            @if($countTce > 0)
            <div class="text-xs font-medium text-blue-700 flex items-center gap-1.5">
                <i class="fas fa-check-circle"></i>
                {{ $countTce }} {{ Str::plural('item', $countTce) }} com preços TCE preenchidos.
            </div>
            @endif
        @else
            <x-form-field name="painel_preco_tce" label="📊 Painel de Preço TCE" type="file" accept=".xlsx, .xls, .csv" />

            {{-- Alternativa: pesquisa via PNCP --}}
            <div class="flex items-center gap-3 pt-1">
                <span class="text-xs text-gray-400">ou</span>
                <a href="{{ route('admin.pesquisa_preco.index', ['processo_id' => $processo->id]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Pesquisar no PNCP
                </a>
                @php $countPncp = $processo->pesquisaPrecoItens()->count(); @endphp
                @if($countPncp > 0)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ $countPncp }} {{ Str::plural('item', $countPncp) }} do PNCP
                </span>
                @endif
            </div>
        @endif
    </div>
    </div>{{-- /x-show tce --}}

    @elseif($campo === 'anexo_pdf_analise_mercado')
    @php
        $etpForneceAnaliseMercado = $processo->etp
            && !empty($processo->etp->cotacao_path)
            && in_array($processo->modalidade, [\App\Enums\ModalidadeEnum::PREGAO_ELETRONICO, \App\Enums\ModalidadeEnum::DISPENSA]);
        $temAnexoManualAnaliseMercado = app(\App\Services\ProcessoDocumentoService::class)->existeAnexoManual($processo, 'anexo_pdf_analise_mercado');
        $analiseMercadoDoEtpDisponivel = $etpForneceAnaliseMercado && !$temAnexoManualAnaliseMercado;
    @endphp
    <div x-show="tipo_relatorio_analise_mercado !== 'pncp'" x-cloak x-data="{ forcarUploadAnaliseMercado: false }">
        @if($analiseMercadoDoEtpDisponivel)
        <div x-show="['fornecedor_local', 'cesta_preco'].includes(tipo_relatorio_analise_mercado) && !forcarUploadAnaliseMercado" x-cloak>
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-file-pdf text-lg"></i>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-emerald-900">PDF do ETP Vinculado</h5>
                            <p class="text-xs text-emerald-700">
                                <span x-text="tipo_relatorio_analise_mercado === 'fornecedor_local' ? 'A Cotação do Fornecedor anexada no ETP será usada automaticamente ao final deste documento.' : 'O PDF anexado no ETP será usado automaticamente ao final deste documento.'"></span>
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.etps.show', $processo->etp->id) }}" target="_blank"
                       class="flex-shrink-0 inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-all shadow-sm">
                        <i class="fas fa-eye mr-1.5"></i> Ver ETP
                    </a>
                </div>
                <div class="mt-3 pt-3 border-t border-emerald-100">
                    <button type="button" @click="forcarUploadAnaliseMercado = true"
                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 underline transition-colors">
                        Enviar meu próprio PDF mesmo assim
                    </button>
                </div>
            </div>
        </div>
        @endif

        <div @if($analiseMercadoDoEtpDisponivel) x-show="!['fornecedor_local', 'cesta_preco'].includes(tipo_relatorio_analise_mercado) || forcarUploadAnaliseMercado" x-cloak @endif>
            @if($analiseMercadoDoEtpDisponivel)
            <p class="text-[10px] text-amber-600 italic mb-1" x-show="forcarUploadAnaliseMercado">
                ⚠️ Isso pode gerar divergência entre o ETP e a Análise de Mercado do processo.
            </p>
            @endif
            <div class="flex items-start mb-4 space-x-2">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <label for="anexo_pdf_analise_mercado" class="block text-sm font-medium text-gray-700">
                            <span x-text="{
                                'tce':             '📎 Anexar PDF — Painel TCE',
                                'fornecedor_local':'📎 Anexar PDF — Fornecedor Local',
                                'cesta_preco':     '📎 Anexar PDF — Cesta de Preços'
                            }[tipo_relatorio_analise_mercado] || '📎 Anexar PDF à Análise de Mercado'"></span>
                        </label>
                    </div>
                    <input type="file"
                           id="anexo_pdf_analise_mercado"
                           name="anexo_pdf_analise_mercado"
                           :disabled="confirmed.anexo_pdf_analise_mercado"
                           accept="application/pdf"
                           class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007779]">
                </div>
                <div class="flex pt-6 space-x-1">
                    <button type="button" @click="saveField('anexo_pdf_analise_mercado')"
                            x-show="!confirmed.anexo_pdf_analise_mercado"
                            class="flex items-center justify-center w-8 h-8 transition-colors duration-200 rounded-lg bg-green-500 hover:bg-green-600 text-white"
                            title="Confirmar">✓</button>
                    <button type="button" @click="toggleConfirm('anexo_pdf_analise_mercado')"
                            x-show="confirmed.anexo_pdf_analise_mercado"
                            class="flex items-center justify-center w-8 h-8 transition-colors duration-200 rounded-lg bg-red-500 hover:bg-red-600 text-white"
                            title="Editar">✗</button>
                </div>
            </div>

            @if($etpForneceAnaliseMercado && $temAnexoManualAnaliseMercado)
            <div x-show="['fornecedor_local', 'cesta_preco'].includes(tipo_relatorio_analise_mercado)" x-cloak
                 class="mt-2 flex items-center gap-2 p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-[11px] text-amber-700 flex-1">
                    Há um PDF anexado manualmente. O ETP vinculado também possui um PDF disponível.
                </p>
                <button type="button" @click="removerAnexoEtp('anexo_pdf_analise_mercado')"
                        class="flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-all">
                    <i class="fas fa-rotate-left mr-1"></i> Voltar a usar o PDF do ETP
                </button>
            </div>
            @endif
        </div>
    </div>

    @elseif($campo === 'empresa_vencedora_pdf')
    <x-form-field name="empresa_vencedora_pdf" label="📎 Anexar PDF à Empresa Vencedora" type="file" accept="application/pdf" />

    @elseif($campo === 'anexar_minuta')
    <x-form-field name="anexar_minuta" label="📎 Anexar PDF à Minutas" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_pdf_publicacoes')
    <x-form-field name="anexo_pdf_publicacoes" label="📎 Anexar PDF à Publicações" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_pdf_minuta_contrato')
    @php
        $temAnexoManualMinutaContrato = app(\App\Services\ProcessoDocumentoService::class)->existeAnexoManual($processo, 'anexo_pdf_minuta_contrato');
        $temModeloMinutaContratoAutomatica = app(\App\Services\ProcessoPdfService::class)->temModeloMinutaContratoAutomatica($processo);
    @endphp

    @if(!$temAnexoManualMinutaContrato && $temModeloMinutaContratoAutomatica)
    <div class="mb-2 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between gap-3">
        <p class="text-[11px] text-emerald-700 flex-1">
            Nenhum PDF anexado manualmente. A Minuta do Contrato será gerada automaticamente a partir do modelo padrão
            do processo.
        </p>
        <a href="{{ route('admin.processos.minuta-contrato.visualizar', $processo) }}" target="_blank"
           class="flex-shrink-0 inline-flex items-center px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-all">
            <i class="fas fa-eye mr-1"></i> Visualizar minuta gerada automaticamente
        </a>
    </div>
    @elseif(!$temModeloMinutaContratoAutomatica)
    <p class="mt-1 text-[11px] text-amber-600">
        Este processo ainda não possui um modelo automático de Minuta do Contrato — o upload manual é obrigatório.
    </p>
    @endif

    <x-form-field name="anexo_pdf_minuta_contrato" label="📎 Anexar PDF Minuta do Contrato" type="file" accept="application/pdf" />
    <p class="mt-1 text-[11px] text-gray-500">
        Se nenhum PDF for anexado aqui, a Minuta do Contrato é gerada automaticamente a partir do modelo padrão do processo.
    </p>

    @elseif($campo === 'clausulas_minuta_especiais' && $processo->tipo_procedimento === \App\Enums\TipoProcedimentoEnum::COMPRAS)
    <x-form-field name="clausulas_minuta_especiais" label="Cláusulas condicionais da Minuta do Contrato (gerada automaticamente)" type="checkbox" :options="[
            'merenda_escolar' => 'Aquisição de gêneros alimentícios / merenda escolar',
            'veiculos' => 'Aquisição de veículos',
        ]" />

    @elseif($campo === 'motivos_fracasso')
    <x-form-field name="motivos_fracasso" label="Motivo(s) do fracasso do certame anterior" type="checkbox" :options="[
            'desclassificadas' => 'Todas as propostas apresentadas foram desclassificadas por desconformidade com o Edital',
            'inabilitados' => 'Todos os licitantes habilitados/convocados foram inabilitados por descumprimento das exigências editalícias',
            'ambos' => 'Ocorreu a desclassificação e inabilitação de todos os licitantes participantes da disputa',
        ]" />

    @elseif($campo === 'anexo_pdf_ata_sessao_fracassada')
    <x-form-field name="anexo_pdf_ata_sessao_fracassada" label="📎 Anexar PDF da Ata da Sessão (certame fracassado)" type="file" accept="application/pdf" />

    {{-- Campos Select --}}
    {{-- ATUALIZADO: Agora inclui DISPENSA também --}}
    @elseif($campo === 'encaminhamento_pesquisa_preco')
        {{-- 🔧 MODIFICAÇÃO: Condição única para ambos os modos --}}
        @if($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO ||
            $processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA)
            <x-form-field name="encaminhamento_pesquisa_preco"
                            label="Encaminhamento para pesquisa de Preços"
                            type="select"
                            :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()"
                            placeholder="Selecione uma unidade" />
        @endif

    @elseif($campo === 'encaminhamento_doacao_orcamentaria')
        {{-- 🔧 MODIFICAÇÃO: Condição única para ambos os modos --}}
        @if($processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO ||
            $processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA  || $processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE)
            <x-form-field name="encaminhamento_doacao_orcamentaria"
                            label="Encaminhamento para doação orçamentária"
                            type="select"
                            :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()"
                            placeholder="Selecione uma unidade" />
        @endif



    @elseif($campo === 'encaminhamento_elaborar_editais')
        @if ($processo->modalidade == \App\Enums\ModalidadeEnum::INEXIGIBILIDADE)
            <x-form-field name="encaminhamento_elaborar_editais" label="Encaminhamento para ELABORAÇÃO DE MINUTA DE CONTRATO " type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />
        @else
            <x-form-field name="encaminhamento_elaborar_editais" label="Encaminhamento para ELABORAÇÃO DE EDITAL E MINUTA DE CONTRATO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />
        @endif

    @elseif($campo === 'encaminhamento_elaborar_termo_referencia')
        <x-form-field name="encaminhamento_elaborar_termo_referencia" label="Encaminhamento para ELABORAÇÃO DO TERMO DE REFERÊNCIA" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

    @elseif($campo === 'encaminhamento_controle_interno' && $processo->modalidade === \App\Enums\ModalidadeEnum::INEXIGIBILIDADE)
        <x-form-field name="encaminhamento_controle_interno" label="Encaminhamento para CONTROLE INTERNO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

    @elseif($campo === 'encaminhamento_elaborar_projeto_basico')
        @if ($processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA || $processo->modalidade === \App\Enums\ModalidadeEnum::DISPENSA)
            <x-form-field name="encaminhamento_elaborar_projeto_basico" label="Encaminhamento para ELABORAÇÀO DE PROJETO BÁSICO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />
        @endif

    @elseif($campo === 'encaminhamento_parecer_juridico')
        <x-form-field name="encaminhamento_parecer_juridico" label="Encaminhamento para ELABORAÇÃO DE PARECER JURÍDICO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />
    
    @elseif($campo === 'encaminhamento_autorizacao_abertura')
    <x-form-field name="encaminhamento_autorizacao_abertura" label="Encaminhamento para AUTORIZAÇÃO DE ABERTURA DE PROCEDIMENTO PELA AUTORIDADE COMPETENTE" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

    @elseif($campo === 'pregoeiro')
    <x-form-field name="pregoeiro" label="PREGOEIRO" type="select" :options="$processo->prefeitura->unidades->pluck('servidor_responsavel', 'servidor_responsavel')->toArray()" placeholder="Selecione uma unidade" />

    {{-- Campos Data e Hora Simplificados --}}
    @elseif($campo === 'data_hora')
    <x-form-field
        name="data_hora"
        label="📅 Data e Hora - ENTREGA E ABERTURA DAS PROPOSTAS"
        type="datetime"
    />

    @elseif($campo === 'exige_atestado')
    <x-form-field name="exige_atestado" label="O Edital exigira Atestado de Capacidade Técnica?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'data_hora_limite_edital')
    <x-form-field
        name="data_hora_limite_edital"
        label="📅 Data e Hora - DATA LIMITE PARA ENVIO DE PROPOSTAS"
        type="datetime"
    />

    @elseif($campo === 'data_hora_fase_edital' &&
        $processo->modalidade !== \App\Enums\ModalidadeEnum::DISPENSA)
    <x-form-field
        name="data_hora_fase_edital"
        label="📅 Data e Hora - DATA DA SESSÃO PÚBLICA E FASE DE LANCES"
        type="datetime"
    />

    {{-- Campo padrão para qualquer outro --}}
    {{-- @else
    <x-form-field name="{{ $campo }}" label="{{ ucfirst(str_replace('_', ' ', $campo)) }}" /> --}}
    @endif

    {{-- Campos "Outro" especiais para checkboxes --}}
    @if($campo === 'instrumento_vinculativo')
    <div class="mt-2" x-show="instrumento_vinculativo.includes('outro')">
        <x-form-field name="instrumento_vinculativo_outro" label="Especifique o outro instrumento vinculativo" />
    </div>
    @endif

    @if($campo === 'prazo_vigencia')
    <div class="mt-2" x-show="prazo_vigencia.includes('outro')">
        <x-form-field name="prazo_vigencia_outro" label="Especifique o outro prazo de vigência" />
    </div>
    @endif
</div>
