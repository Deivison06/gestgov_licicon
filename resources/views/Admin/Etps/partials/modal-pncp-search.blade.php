<!-- Modal Buscar no PNCP -->
<div id="modal-pncp-search" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh] mx-4">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-2xl">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Buscar no PNCP</h3>
                <p class="text-xs text-gray-500 mt-1">Busque descrições oficiais diretamente no Portal Nacional de Contratações Públicas</p>
            </div>
            <button type="button" onclick="closeModalPncp()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="p-6 flex flex-col flex-1 overflow-hidden">
            <!-- Barra de Busca -->
            <div class="flex gap-2 mb-4">
                <input 
                    type="text" 
                    id="pncp-modal-search-input" 
                    placeholder="Digite o nome do item para buscar... (mínimo 3 caracteres)"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#009496] focus:ring-[#009496] text-sm"
                >
                <button 
                    type="button" 
                    id="pncp-modal-search-btn"
                    onclick="executePncpSearch(1)"
                    class="px-5 py-2 bg-[#009496] text-white rounded-md font-semibold hover:bg-[#007f7c] transition text-sm flex items-center gap-2 whitespace-nowrap"
                >
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>

            <!-- Mensagem de Erro/Alerta -->
            <div id="pncp-modal-alert" class="hidden p-3 mb-4 text-sm rounded-lg"></div>

            <!-- Área de Resultados (Scrollable) -->
            <div class="flex-1 overflow-y-auto border border-gray-100 rounded-lg p-2 bg-gray-50/50 min-h-[250px]" id="pncp-modal-results-container">
                <!-- Estado Inicial -->
                <div class="flex flex-col items-center justify-center h-full py-16 text-gray-400" id="pncp-modal-placeholder">
                    <div class="p-4 bg-white rounded-full shadow-sm mb-3">
                        <i class="fas fa-cloud text-3xl text-[#009496]/40"></i>
                    </div>
                    <p class="text-sm font-medium">Digite um termo para iniciar a busca no PNCP</p>
                    <p class="text-xs text-gray-400 mt-1">Ex: Papel sulfite, Cesta básica, Pintura predial</p>
                </div>

                <!-- Loading Spinner -->
                <div class="hidden flex-col items-center justify-center h-full py-16" id="pncp-modal-loading">
                    <div class="w-10 h-10 border-4 border-[#009496]/20 border-t-[#009496] rounded-full animate-spin mb-3"></div>
                    <p class="text-sm text-gray-500 animate-pulse" id="pncp-modal-loading-msg">Consultando contratações...</p>
                </div>

                <!-- Lista de itens encontrados -->
                <div class="hidden space-y-3" id="pncp-modal-results-list"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let pncpDescriptionsCache = [];
    let currentPncpTerm = '';
    let currentPncpPage = 1;

    function openModalPncp() {
        document.getElementById('modal-pncp-search').classList.remove('hidden');
        document.getElementById('pncp-modal-search-input').focus();
    }

    function closeModalPncp() {
        document.getElementById('modal-pncp-search').classList.add('hidden');
        document.getElementById('pncp-modal-search-input').value = '';
        document.getElementById('pncp-modal-results-list').innerHTML = '';
        document.getElementById('pncp-modal-results-list').classList.add('hidden');
        document.getElementById('pncp-modal-placeholder').classList.remove('hidden');
        document.getElementById('pncp-modal-loading').classList.add('hidden');
        document.getElementById('pncp-modal-alert').classList.add('hidden');
        pncpDescriptionsCache = [];
    }

    function highlightText(text, term) {
        if (!term || !text) return text || '';
        const escapedTerm = term.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const regex = new RegExp(`(${escapedTerm})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5 font-semibold">$1</mark>');
    }

    function executePncpSearch(page = 1) {
        const input = document.getElementById('pncp-modal-search-input');
        const term = input.value.trim();
        
        const alertContainer = document.getElementById('pncp-modal-alert');
        const placeholder = document.getElementById('pncp-modal-placeholder');
        const loading = document.getElementById('pncp-modal-loading');
        const resultsList = document.getElementById('pncp-modal-results-list');
        const loadingMsg = document.getElementById('pncp-modal-loading-msg');

        alertContainer.classList.add('hidden');

        if (term.length < 3) {
            alertContainer.className = 'p-3 mb-4 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg';
            alertContainer.textContent = 'Por favor, digite pelo menos 3 caracteres para buscar.';
            alertContainer.classList.remove('hidden');
            return;
        }

        currentPncpTerm = term;
        currentPncpPage = page;
        pncpDescriptionsCache = [];

        placeholder.classList.add('hidden');
        resultsList.classList.add('hidden');
        loading.classList.remove('hidden');
        loadingMsg.textContent = 'Buscando contratações públicas...';

        const searchUrl = `{{ route('admin.pncp.mercado.search') }}?termo=${encodeURIComponent(term)}&pagina=${page}`;

        // Busca contratações no PNCP
        fetch(searchUrl)
            .then(handleCsrfExpirado)
            .then(r => r.ok ? r.json() : Promise.reject('Erro ao comunicar com o servidor.'))
            .then(data => {
                if (!data.success || !data.data) {
                    throw new Error(data.message || 'Erro ao consultar o PNCP.');
                }
                
                const contratacoes = data.data;
                if (!contratacoes.data || contratacoes.data.length === 0) {
                    throw new Error('Nenhuma contratação encontrada com este termo.');
                }
                
                loadingMsg.textContent = 'Carregando detalhes dos itens...';
                
                // Mapeia contratações e faz requisições em paralelo para buscar itens oficiais
                const promises = contratacoes.data.map(c => {
                    const cnpj = c.orgaoEntidade.cnpj;
                    const ano = c.anoCompra;
                    const seq = c.sequencialCompra;
                    const itemsUrl = `{{ url('admin/pncp/items') }}/${cnpj}/${ano}/${seq}`;
                    
                    return fetch(itemsUrl)
                        .then(r => r.ok ? r.json() : null)
                        .then(itemsData => {
                            if (!itemsData || !itemsData.success || !itemsData.data) return [];
                            return itemsData.data
                                .filter(item => {
                                    const desc = (item.descricao || '').toLowerCase();
                                    const searchWords = term.toLowerCase().split(' ');
                                    return searchWords.every(word => desc.includes(word));
                                })
                                .map(item => ({ item, contratacao: c }));
                        })
                        .catch(() => []);
                });

                return Promise.all(promises).then(results => {
                    const allItems = results.flat();
                    if (allItems.length === 0) {
                        throw new Error('Nenhum item correspondente encontrado nas contratações.');
                    }
                    return { items: allItems, meta: contratacoes };
                });
            })
            .then(({ items, meta }) => {
                loading.classList.add('hidden');
                renderPncpResults(items, meta);
            })
            .catch(err => {
                loading.classList.add('hidden');
                placeholder.classList.remove('hidden');
                alertContainer.className = 'p-3 mb-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg';
                alertContainer.textContent = err.message || 'Erro inesperado ao consultar o PNCP.';
                alertContainer.classList.remove('hidden');
            });
    }

    function renderPncpResults(allItems, meta) {
        const resultsList = document.getElementById('pncp-modal-results-list');
        resultsList.innerHTML = '';

        // Exibe contador de resultados
        const headerDiv = document.createElement('div');
        headerDiv.className = 'flex justify-between items-center pb-2 border-b border-gray-100 mb-3';
        headerDiv.innerHTML = `
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Itens Correspondentes Encontrados</span>
            <span class="px-2 py-0.5 text-xs font-bold text-[#009496] bg-[#009496]/10 rounded-full">${allItems.length} itens</span>
        `;
        resultsList.appendChild(headerDiv);

        // Adiciona cards à lista
        allItems.forEach((entry, idx) => {
            const item = entry.item;
            const c = entry.contratacao;
            const descricao = item.descricao || '(Sem descrição)';
            
            pncpDescriptionsCache.push({
                descricao: descricao,
                unidade: item.unidadeMedida || '',
                quantidade: item.quantidade || 1
            });
            const cacheIndex = pncpDescriptionsCache.length - 1;

            const card = document.createElement('div');
            card.className = 'bg-white border border-gray-100 rounded-xl p-4 shadow-sm hover:border-[#009496]/40 transition duration-200 flex flex-col justify-between';
            
            const tipo = item.tipoItem || (item.materialOuServicoNome) || '';
            const isMaterial = tipo.toLowerCase().includes('material') || tipo === 'M';
            const badgeClass = isMaterial ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200';
            const badgeLabel = tipo || 'Item';

            card.innerHTML = `
                <div class="flex justify-between items-start gap-4 mb-3">
                    <p class="text-sm font-medium text-gray-800 flex-1 leading-relaxed">${highlightText(descricao, currentPncpTerm)}</p>
                    <button 
                        type="button" 
                        onclick="selectPncpDescription(${cacheIndex})" 
                        class="px-4 py-2 text-xs font-bold text-white bg-[#009496] hover:bg-[#007f7c] rounded-lg transition-all duration-200 shadow-sm hover:shadow whitespace-nowrap"
                    >
                        Selecionar
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 border-t border-gray-50 pt-2">
                    <span class="px-2 py-0.5 rounded-full font-bold text-[9px] uppercase tracking-wide border ${badgeClass}">
                        ${badgeLabel}
                    </span>
                    <span class="font-medium text-gray-700 truncate max-w-[200px]" title="${c.orgaoEntidade.razaoSocial}">${c.orgaoEntidade.razaoSocial}</span>
                    <span class="text-gray-300">|</span>
                    <span class="font-semibold text-gray-600">${c.uf || ''}</span>
                    <span>${c.municipio || ''}</span>
                    <span class="text-gray-300">|</span>
                    <span>Qtd: ${item.quantidade ?? '?'} ${item.unidadeMedida || ''}</span>
                </div>
            `;
            resultsList.appendChild(card);
        });

        if (meta.totalPaginas > 1) {
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'flex justify-center items-center gap-2 pt-4 border-t border-gray-100 mt-4';
            
            const btnClass = 'px-3 py-1.5 text-xs font-bold rounded-lg border transition-all';
            
            if (currentPncpPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = `${btnClass} bg-white text-gray-600 border-gray-200 hover:border-[#009496] hover:text-[#009496]`;
                prevBtn.innerHTML = '← Anterior';
                prevBtn.onclick = () => executePncpSearch(currentPncpPage - 1);
                paginationContainer.appendChild(prevBtn);
            }

            const pageIndicator = document.createElement('span');
            pageIndicator.className = 'text-xs text-gray-500 font-semibold px-2';
            pageIndicator.textContent = `Pág. ${currentPncpPage} / ${meta.totalPaginas}`;
            paginationContainer.appendChild(pageIndicator);

            if (currentPncpPage < meta.totalPaginas) {
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = `${btnClass} bg-white text-gray-600 border-gray-200 hover:border-[#009496] hover:text-[#009496]`;
                nextBtn.innerHTML = 'Próxima →';
                nextBtn.onclick = () => executePncpSearch(currentPncpPage + 1);
                paginationContainer.appendChild(nextBtn);
            }

            resultsList.appendChild(paginationContainer);
        }

        resultsList.classList.remove('hidden');
    }

    function selectPncpDescription(cacheIndex) {
        const entry = pncpDescriptionsCache[cacheIndex];
        if (!entry) return;

        // Visual loading feedback
        const selectBtn = document.querySelector(`button[onclick="selectPncpDescription(${cacheIndex})"]`);
        const originalBtnText = selectBtn.innerHTML;
        selectBtn.disabled = true;
        selectBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Importando...';

        // Call the quick-create API route
        fetch('{{ route('admin.etps.criar-item-rapido') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                descricao_item: entry.descricao,
                unidade_medida: entry.unidade
            })
        })
        .then(handleCsrfExpirado)
        .then(r => r.ok ? r.json() : Promise.reject('Falha ao cadastrar item.'))
        .then(data => {
            if (data.success) {
                const item = data.item;
                
                // Add the item to the checkbox list options
                adicionarItemAosListas(item);
                
                // Get contract type
                const selectedTipo = document.querySelector('input[name="tipo_contratacao"]:checked')?.value || 'item';

                if (selectedTipo === 'lote' || selectedTipo === 'obras') {
                    const loteCards = document.querySelectorAll('.lote-card');
                    if (loteCards.length === 0) {
                        alert('Por favor, crie um lote antes de adicionar itens.');
                        return;
                    }

                    let targetIdx = null;
                    if (loteCards.length === 1) {
                        targetIdx = loteCards[0].id.replace('lote-', '');
                    } else {
                        let optionsText = '';
                        const parsedLotes = [];
                        loteCards.forEach((loteCard, idx) => {
                            const actualIdx = loteCard.id.replace('lote-', '');
                            const nomeInput = loteCard.querySelector('input[name^="lotes["][name$="[nome]"]');
                            const nome = nomeInput ? nomeInput.value.trim() : `Lote ${parseInt(actualIdx) + 1}`;
                            optionsText += `${idx + 1} - ${nome}\n`;
                            parsedLotes.push(actualIdx);
                        });
                        const choice = prompt(`Em qual lote deseja adicionar o item "${item.descricao_item}"?\n\n${optionsText}\nDigite o número do lote (1 a ${loteCards.length}):`);
                        if (!choice) return; // User cancelled
                        const choiceIdx = parseInt(choice) - 1;
                        if (isNaN(choiceIdx) || choiceIdx < 0 || choiceIdx >= loteCards.length) {
                            alert('Número de lote inválido.');
                            return;
                        }
                        targetIdx = parsedLotes[choiceIdx];
                    }

                    if (targetIdx !== null) {
                        const loteCheckbox = document.querySelector(`#lista_itens_lote_${targetIdx} .item-checkbox[value="${item.id}"]`);
                        if (loteCheckbox) {
                            loteCheckbox.checked = true;
                            toggleItemSelecionado(loteCheckbox, parseInt(targetIdx));
                        }
                    }
                } else {
                    // Automatically select (check) this newly created item globally
                    const globalCheckbox = document.querySelector(`#lista_itens_global .item-checkbox[value="${item.id}"]`);
                    if (globalCheckbox) {
                        globalCheckbox.checked = true;
                        toggleItemSelecionado(globalCheckbox, null);
                    }
                }

                closeModalPncp();
            } else {
                alert('Erro ao importar item: ' + (data.message || 'Erro desconhecido.'));
            }
        })
        .catch(err => {
            alert('Erro na integração do PNCP: ' + err.message);
        })
        .finally(() => {
            selectBtn.disabled = false;
            selectBtn.innerHTML = originalBtnText;
        });
    }



    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('pncp-modal-search-input');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    executePncpSearch(1);
                }
            });
        }
    });
</script>
