{{-- resources/views/Admin/Processos/partials/forms.blade.php --}}
<div class="p-3 mb-3 bg-white border border-gray-200 rounded-lg">

    @if($campo === 'anexo_atos_sessao')
    <x-form-field name="anexo_atos_sessao" label="📎 Anexar PDF Atos da Sessão" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_proposta')
    <x-form-field name="anexo_proposta" label="📎 Anexar PDF Proposta" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_proposta_readequada')
    <x-form-field name="anexo_proposta_readequada" label="📎 Anexar PDF Proposta Readequada" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_habilitacao')
    <x-form-field name="anexo_habilitacao" label="📎 Anexar PDF Habilitação" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_recurso_contratacoes')
    <x-form-field name="anexo_recurso_contratacoes" label="📎 Anexar PDF Projeto Básico" type="file" accept="application/pdf" />

    @elseif($campo === 'anexo_publicacoes')
    <x-form-field name="anexo_publicacoes" label="📎 Anexar PDF Projeto Básico" type="file" accept="application/pdf" />

    {{-- campos STRING --}}

    @elseif($campo === 'orgao_responsavel')
    <x-form-field name="orgao_responsavel" label="Órgão Responsável pela Assinatura do Contrato" type="text" />

    @elseif($campo === 'cargo_responsavel')
    <x-form-field name="cargo_responsavel" label="Cargo do Responsavel" type="text" />

    @elseif($campo === 'cnpj')
    <x-form-field name="cnpj" label="CNPJ" type="text" />

    @elseif($campo === 'endereco')
    <x-form-field name="endereco" label="Endereço" type="text" />

    @elseif($campo === 'responsavel')
    <x-form-field name="responsavel" label="Responsavel" type="text" />

    @elseif($campo === 'cpf_responsavel')
    <x-form-field name="cpf_responsavel" label="CPF" type="text" />

    @elseif($campo === 'razao_social')
    <x-form-field name="razao_social" label="Razão Social da Empresa" type="text" />

    @elseif($campo === 'cnpj_empresa_vencedora')
    <x-form-field name="cnpj_empresa_vencedora" label="CNPJ da Empresa vencedora" type="text" />

    @elseif($campo === 'endereco_empresa_vencedora')
    <x-form-field name="endereco_empresa_vencedora" label="Endereço da empresa vencedora" type="text" />

    @elseif($campo === 'representante_legal_empresa')
    <x-form-field name="representante_legal_empresa" label="Representante Legal" type="text" />

    @elseif($campo === 'cpf_representante')
    <x-form-field name="cpf_representante" label="CPF do Representante" type="text" />

    @elseif($campo === 'valor_total')
    <x-form-field name="valor_total" label="Valor Total" type="text" />

    @elseif($campo === 'numero_ata_registro_precos')
    <x-form-field name="numero_ata_registro_precos" label="Numero ATA" type="text" />

    @elseif($campo === 'cargo_controle_interno')
    <x-form-field name="cargo_controle_interno" label="Cargo controle interno" type="select" :options="$processo->prefeitura->unidades->pluck('nome', 'servidor_responsavel')->toArray()" placeholder="Selecione um Responsavel" />
    @endif
</div>
