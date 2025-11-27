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
    @endif
</div>
