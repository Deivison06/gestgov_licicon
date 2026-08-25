@extends('layouts.app')

@section('page-title', 'Documentos do Aditivo')
@section('page-subtitle', 'Gerenciar e gerar documentos para o Incidente Contratual')

@section('content')

    @php
        $docsTotal = count($documentos);
        $docsGerados = 0;
        $documentosProcesso = $processo->documentos->where('incidente_id', $incidente->id);
        
        foreach ($documentos as $tipoProg => $docProg) {
            $doc = $documentosProcesso->firstWhere('tipo_documento', $tipoProg);
            if ($doc && $doc->gerado_em) $docsGerados++;
        }
        $docsPercent = $docsTotal > 0 ? (int) round(($docsGerados / $docsTotal) * 100) : 0;
    @endphp

    <div class="mb-4">
        <a href="{{ route('admin.processos.show', $processo->id) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para o Processo
        </a>
    </div>

    <!-- Seção de Documentos -->
    <div class="mb-8">
        <div class="px-6 py-5 bg-white border border-gray-200 shadow-sm rounded-t-xl">
            <div class="flex flex-col items-start justify-between gap-3 lg:flex-row lg:items-center">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Gerar Documentos do Aditivo</h3>
                    <span class="text-sm text-gray-500">Contrato Nº {{ $contrato->numero_contrato }}</span>
                </div>
                <div class="flex flex-col items-end gap-3 lg:flex-row lg:items-center w-full lg:w-auto">
                    <div class="w-full lg:w-72">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-600">Documentos gerados</span>
                            <span class="text-xs font-semibold {{ $docsPercent === 100 ? 'text-green-700' : 'text-gray-700' }}">
                                {{ $docsGerados }}/{{ $docsTotal }}
                            </span>
                        </div>
                        <div class="w-full h-2 overflow-hidden bg-gray-200 rounded-full">
                            <div class="h-2 rounded-full transition-all duration-300 {{ $docsPercent === 100 ? 'bg-green-500' : 'bg-[#009496]' }}"
                                 style="width: {{ $docsPercent }}%"></div>
                        </div>
                    </div>
                    <form action="{{ route('admin.incidentes.destroy', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja reverter e excluir este aditivo? Esta ação não pode ser desfeita.');" class="ml-0 lg:ml-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 transition-colors duration-200 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 hover:text-red-700">
                            <i class="fas fa-trash-alt"></i> Reverter Aditivo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.incidentes.atualizar-campos', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tipo" value="{{ $incidente->tipo }}">
            <input type="hidden" name="categoria" value="{{ $incidente->categoria }}">
            
            <div class="overflow-x-auto bg-white border-x border-b border-gray-200 shadow-sm rounded-b-xl">
                <!-- Área de Mensagens -->
                <div id="message-container" class="p-4"></div>

                @if(session('success'))
                    <div class="p-4 mx-4 mt-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="p-4 mx-4 mt-4 text-sm text-red-800 bg-red-100 border border-red-200 rounded-lg">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-end px-6 pt-4 pb-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-[#009496] rounded-md hover:bg-[#007b85]">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </div>

                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-700 uppercase">
                            Documentos
                        </th>
                        <th class="w-40 px-6 py-4 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">
                            Data
                        </th>
                        <th class="w-48 px-6 py-4 text-xs font-semibold tracking-wider text-center text-gray-700 uppercase">
                            Ações
                        </th>
                    </tr>
                    </thead>
                    @php
                        $initialData = $documentosProcesso->pluck('data_selecionada', 'tipo_documento')
                            ->mapWithKeys(fn($d, $t) => ["data_doc_$t" => $d])->toArray();
                    @endphp
                    <tbody class="bg-white divide-y divide-gray-200" x-data="formFieldAditivo({{ json_encode($initialData) }})">
                    @foreach ($documentos as $tipo => $doc)
                        @php
                            $dataId = 'data_' . $tipo;
                            $accordionId = "accordion-collapse-{$tipo}";
                            $temCampos = !empty($doc['campos']);
                            $documentoGerado = $documentosProcesso->firstWhere('tipo_documento', $tipo);
                            $dataSelecionada = $documentoGerado->data_selecionada ?? '';
                        @endphp

                        <tr class="transition-colors duration-150 hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-2 h-2 mr-3 rounded-full" style="background-color: {{ $doc['cor'] }};"></div>

                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $doc['titulo'] }}

                                        @if ($documentoGerado && $documentoGerado->gerado_em)
                                            <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded-full align-middle">
                                                <i class="fas fa-check-circle"></i>
                                                Gerado em {{ \Carbon\Carbon::parse($documentoGerado->gerado_em)->format('d/m/Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($temCampos)
                                    <button type="button"
                                            class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-1 text-xs font-medium text-[#009496] bg-[#009496]/10 rounded-md hover:bg-[#009496]/20 transition-colors"
                                            onclick="document.getElementById('{{ $accordionId }}').classList.toggle('hidden')">
                                        <i class="fas fa-sliders-h text-[10px]"></i>
                                        <span class="collapse-text">Definir Campos</span>
                                    </button>
                                @endif
                            </td>

                            <td class="flex items-center gap-2 px-6 py-4 text-center">
                                <div class="relative">
                                    <input type="date"
                                           class="w-40 px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496]"
                                           id="{{ $dataId }}"
                                           x-model="data_doc_{{ $tipo }}"
                                           @blur="saveField('data_doc_{{ $tipo }}')"
                                           value="{{ $dataSelecionada }}">
                                    <span x-show="confirmed['data_doc_{{ $tipo }}']" x-cloak
                                          class="absolute -right-1.5 -top-1.5 flex items-center justify-center w-4 h-4 text-white bg-green-500 rounded-full shadow"
                                          title="Data salva">
                                        <i class="fas fa-check text-[8px]"></i>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <button type="button"
                                        onclick="gerarPdfAditivo('{{ $contrato->id }}', '{{ $incidente->id }}', '{{ $tipo }}', document.getElementById('{{ $dataId }}').value)"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-white transition-colors duration-200 bg-[#009496] rounded-md hover:bg-[#007b85] focus:outline-none focus:ring-2 focus:ring-[#009496] focus:ring-offset-2">
                                    <i class="fas fa-file-pdf"></i> Gerar PDF
                                </button>
                            </td>
                        </tr>

                        @if ($temCampos)
                            <tr>
                                <td colspan="3" class="p-0">
                                    <div id="{{ $accordionId }}" class="hidden">
                                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                                            <h4 class="mb-3 text-sm font-semibold text-gray-700">Campos do Documento</h4>
                                            <div class="grid gap-4">
                                                @foreach ($doc['campos'] as $campo)
                                                    <div>
                                                        <label class="block mb-1 text-sm font-medium text-gray-700">{{ $campo['label'] }}</label>
                                                        
                                                        @if($campo['tipo'] === 'textarea')
                                                            <div class="relative" x-data="{ openIA: false, queryIA: '{{ $campo['value'] }}', loadingIA: false }">
                                                                <textarea name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]" rows="4" x-model="queryIA">{{ $campo['value'] }}</textarea>
                                                                @if(isset($campo['ia']) && $campo['ia'])
                                                                    <x-ia-popover name="{{ $tipo }}_{{ $campo['name'] }}" :processoId="$processo->id" />
                                                                @endif
                                                            </div>
                                                        @elseif($campo['tipo'] === 'file')
                                                            <input type="file" name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#009496] file:text-white hover:file:bg-[#007b85]">
                                                            @if($campo['value'])
                                                                <a href="{{ Storage::url($campo['value']) }}" target="_blank" class="inline-block mt-2 text-xs font-medium text-blue-600 hover:underline">
                                                                    <i class="fas fa-file-pdf"></i> Visualizar arquivo atual
                                                                </a>
                                                            @endif
                                                        @elseif($campo['tipo'] === 'number')
                                                            <input type="number" name="{{ $campo['name'] }}" id="{{ $tipo }}_{{ $campo['name'] }}" step="{{ $campo['step'] ?? '1' }}" value="{{ $campo['value'] }}" class="block w-full px-3 py-2 text-sm border-gray-300 rounded-md shadow-sm focus:ring-[#009496] focus:border-[#009496]">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="flex justify-end mt-4">
                                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 bg-[#009496] rounded-md hover:bg-[#007b85]">
                                                    <i class="fas fa-save"></i> Salvar Informações
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <script>
        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
            const html = `
                <div class="p-4 mx-4 mt-4 text-sm border rounded-lg ${alertClass}" role="alert">
                    <span class="font-medium">${type === 'success' ? 'Sucesso!' : 'Erro!'}</span> ${message}
                </div>
            `;
            container.innerHTML = html;
            setTimeout(() => container.innerHTML = '', 5000);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('formFieldAditivo', (initialData) => ({
                ...initialData,
                confirmed: {},
                
                saveField(field) {
                    const value = this[field];
                    
                    fetch(`{{ route('admin.incidentes.documentos.salvar-campo', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ [field]: value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.confirmed[field] = true;
                            setTimeout(() => {
                                this.confirmed[field] = false;
                            }, 2000);
                        } else {
                            showMessage(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('Erro ao salvar o campo.', 'error');
                    });
                }
            }));
        });

        function gerarPdfAditivo(contratoId, incidenteId, tipo, dataSelecionada) {
            let url = `/admin/contratos/${contratoId}/incidentes/${incidenteId}/pdf/${tipo}`;
            if (dataSelecionada) {
                // Força o salvamento da data antes de abrir o PDF
                fetch(`{{ route('admin.incidentes.documentos.salvar-campo', ['contrato_id' => $contrato->id, 'incidente_id' => $incidente->id]) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ [`data_doc_${tipo}`]: dataSelecionada })
                }).then(() => {
                    window.open(url, '_blank');
                    setTimeout(() => location.reload(), 2000);
                });
            } else {
                window.open(url, '_blank');
                setTimeout(() => location.reload(), 2000);
            }
        }
    </script>
@endsection
