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
    <x-form-field name="justificativa" label="Justificativa da Necessidade da Contratação" type="textarea" rows="5" />

    @elseif($campo === 'descricao_necessidade')
    <x-form-field name="descricao_necessidade" label="DESCRIÇÃO DA NECESSIDADE" type="textarea" rows="5" />

    @elseif($campo === 'descricao_necessidade_autorizacao')
    <x-form-field name="descricao_necessidade_autorizacao" label="DESCRIÇÃO DA NECESSIDADE DE AUTORIZAÇÃO" type="textarea" rows="5" />

    @elseif($campo === 'incluir_requisito_cada_caso_concreto')
    <x-form-field name="incluir_requisito_cada_caso_concreto" label="REQUISITOS REFERENTES A CADA CASO CONCRETO" type="textarea" rows="5" />

    @elseif($campo === 'solucoes_disponivel_mercado')
    <x-form-field name="solucoes_disponivel_mercado" label="SOLUÇÕES DISPONÍVEIS NO MERCADO" type="textarea" rows="5" />

    @elseif($campo === 'solucao_escolhida')
    <x-form-field name="solucao_escolhida" label="SOLUÇÃO ESCOLHIDA" />

    @elseif($campo === 'justificativa_solucao_escolhida')
    <x-form-field name="justificativa_solucao_escolhida" label="JUSTIFICATIVA DA SOLUÇÃO ESCOLHIDA" type="textarea" rows="5" />

    @elseif($campo === 'resultado_pretendidos')
    <x-form-field name="resultado_pretendidos" label="RESULTADOS PRETENDIDOS" type="textarea" rows="5" />

    @elseif($campo === 'impacto_ambiental')
    <x-form-field name="impacto_ambiental" label="IMPACTOS AMBIENTAIS" type="textarea" rows="5" />

    @elseif($campo === 'riscos_extra')
    <x-form-field name="riscos_extra" label="RISCOS EXTRAS" type="textarea" rows="5" />

    @elseif($campo === 'problema_resolvido')
    <x-form-field name="problema_resolvido" label="Problema Resumido" type="textarea" rows="5" />

    @elseif($campo === 'nome_equipe_planejamento')
    <x-form-field name="nome_equipe_planejamento" label="EQUIPE DE PLANEJAMENTO" />

    @elseif($campo === 'responsavel_equipe_planejamento')
    <x-form-field name="responsavel_equipe_planejamento" label="RESPONSAVEL EQUIPE DE PLANEJAMENTO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'servidor_responsavel')->toArray()" placeholder="Selecione um Responsavel" />

    @elseif($campo === 'agente_contratacao')
    <x-form-field name="agente_contratacao" label="Agente contratação" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'servidor_responsavel')->toArray()" placeholder="Selecione um Responsavel" />

    @elseif($campo === 'prazo_entrega')
    <x-form-field name="prazo_entrega" label="Prazo de Entrega / Execução" />

    @elseif($campo === 'local_entrega' && $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
    <x-form-field name="local_entrega" label="Local(is) e Horário(s) de Entrega" />

    @elseif($campo === 'fiscais')
    <x-form-field name="fiscais" label="Fiscal(is) Indicado(s)" />

    @elseif($campo === 'gestor')
    <x-form-field name="gestor" label="Gestor Indicado" />

    @elseif($campo === 'valor_estimado')
    <x-form-field name="valor_estimado" label="Valor Estimado" />

    @elseif($campo === 'dotacao_orcamentaria')
    <x-form-field name="dotacao_orcamentaria" label="CASO A LICITAÇÃO NÃO SEJA DO TIPO SRP, DESCREVA ABAIXO A DOTAÇÃO ORÇAMENTÁRIA" type="textarea" rows="5" />

    @elseif($campo === 'tratamento_diferenciado_MEs_eEPPs')
    <x-form-field name="tratamento_diferenciado_MEs_eEPPs" label="TRATAMENTO DIFERENCIA A MEs e EPPs" type="textarea" rows="5" />

    @elseif($campo === 'intervalo_lances')
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

    {{-- Campos Radio --}}
    @elseif($campo === 'tipo_srp')
    <x-form-field name="tipo_srp" label="Esse Processo é do tipo SRP?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'prevista_plano_anual')
    <x-form-field name="prevista_plano_anual" label="A CONTRATAÇÃO ESTÁ PREVISTA NO PLANO DE CONTRATAÇÃO ANUAL?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'contratacoes_anteriores' && $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
    <x-form-field name="contratacoes_anteriores" label="Houve contratações anteriores?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'objeto_continuado')
    <x-form-field name="objeto_continuado" label="Objeto Continuado?" type="radio" :options="['sim' => 'Sim', 'nao' => 'Não']" />

    @elseif($campo === 'inversao_fase')
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
    @elseif($campo === 'itens_e_seus_quantitativos_xml' && $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
    <x-form-field name="itens_e_seus_quantitativos_xml" label="📦 Itens e Seus Quantitativos" type="file" accept=".xml, .xlsx, .xls, .csv" />

    @elseif($campo === 'projeto_basico_pdf')
    <x-form-field name="projeto_basico_pdf" label="📎 Anexar PDF Projeto Básico" type="file" accept="application/pdf" />

    @elseif($campo === 'itens_especificaca_quantitativos_xml')
    <x-form-field name="itens_especificaca_quantitativos_xml" label="📦 Itens e Seus quantitativos e especificações" type="file" accept=".xml, .xlsx, .xls, .csv" />

    @elseif($campo === 'info_extras')
    <x-form-field name="info_extras" label="Informações Extras" type="textarea" rows="5" />

    @elseif($campo === 'painel_preco_tce')
    <x-form-field name="painel_preco_tce" label="📊 Painel de Preço TCE" type="file" accept=".xlsx, .xls, .csv" />

    @elseif($campo === 'anexo_pdf_analise_mercado')
    <x-form-field name="anexo_pdf_analise_mercado" label="📎 Anexar PDF à Análise de Mercado" type="file" accept="application/pdf" />

    @elseif($campo === 'anexar_minuta')
    <x-form-field name="anexar_minuta" label="📎 Anexar PDF à Minutas" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_pdf_publicacoes')
    <x-form-field name="anexo_pdf_publicacoes" label="📎 Anexar PDF à Publicações" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_pdf_minuta_contrato')
    <x-form-field name="anexo_pdf_minuta_contrato" label="📎 Anexar PDF Minuta do Contrato" type="file" accept="application/pdf" />

    {{-- Campos Select --}}
    @elseif($campo === 'encaminhamento_pesquisa_preco' && $processo->modalidade === \App\Enums\ModalidadeEnum::PREGAO_ELETRONICO)
    <x-form-field name="encaminhamento_pesquisa_preco" label="Encaminhamento para pesquisa de Preços" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

    @elseif($campo === 'encaminhamento_doacao_orcamentaria')
    <x-form-field name="encaminhamento_doacao_orcamentaria" label="Encaminhamento para doação orçamentária" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

    @elseif($campo === 'encaminhamento_elaborar_editais')
    <x-form-field name="encaminhamento_elaborar_editais" label="Encaminhamento para ELABORAÇÃO DE EDITAL E MINUTA DE CONTRATO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

    @elseif($campo === 'encaminhamento_elaborar_projeto_basico' && $processo->modalidade === \App\Enums\ModalidadeEnum::CONCORRENCIA)
    <x-form-field name="encaminhamento_elaborar_projeto_basico" label="Encaminhamento para ELABORAÇÀO DE PROJETO BÁSICO" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'nome')->toArray()" placeholder="Selecione uma unidade" />

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
    {{-- No forms.blade.php --}}
    @elseif($campo === 'data_hora_limite_edital')
    <x-form-field
        name="data_hora_limite_edital"
        label="📅 Data e Hora - DATA LIMITE PARA ENVIO DE PROPOSTAS"
        type="datetime"
    />
    {{-- No forms.blade.php --}}
    @elseif($campo === 'data_hora_fase_edital')
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
