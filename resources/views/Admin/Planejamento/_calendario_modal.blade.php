{{-- Calendário de Planejamento — Alpine.js nativo, sem dependências externas --}}
{{-- Script do componente Alpine 'planejamentoPainel' --}}
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('planejamentoPainel', () => ({
        modalAgendar: false,
        modalCalendario: false,
        processoId: null,
        dataSessao: '',
        dataMinima: '{{ now()->toDateString() }}',
        tipoData: 'sessao',
        statusFiltro: 'todos',
        modalidadeFiltro: '',

        viewMode: 'month',
        currentDate: new Date(),
        eventos: [],
        loadingEventos: false,

        modalDetalhes: false,
        eventoSelecionado: null,

        // ── Helpers ──────────────────────────────────────────────

        dateToStr(date) {
            const yr = date.getFullYear();
            const mo = String(date.getMonth() + 1).padStart(2, '0');
            const dt = String(date.getDate()).padStart(2, '0');
            return `${yr}-${mo}-${dt}`;
        },

        eventosParaDia(date) {
            const str = this.dateToStr(date);
            return this.eventos.filter(e => e.start === str);
        },

        // ── Geração das grades ────────────────────────────────────

        diasMes() {
            const year  = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const firstDay  = new Date(year, month, 1);
            const lastDay   = new Date(year, month + 1, 0);
            const startDow  = firstDay.getDay(); // 0=Dom
            const todayStr  = this.dateToStr(new Date());
            const days = [];

            // Padding início (dias do mês anterior)
            for (let i = 0; i < startDow; i++) {
                const d = new Date(year, month, 1 - startDow + i);
                days.push({ date: d, currentMonth: false, isToday: false });
            }

            // Dias do mês atual
            for (let i = 1; i <= lastDay.getDate(); i++) {
                const d = new Date(year, month, i);
                days.push({ date: d, currentMonth: true, isToday: this.dateToStr(d) === todayStr });
            }

            // Padding fim — completar 6 semanas (42 células)
            let extra = 1;
            while (days.length < 42) {
                days.push({ date: new Date(year, month + 1, extra++), currentMonth: false, isToday: false });
            }

            return days;
        },

        diasSemana() {
            const base = new Date(this.currentDate);
            base.setDate(base.getDate() - base.getDay()); // recua para o Domingo
            const todayStr = this.dateToStr(new Date());
            const nomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

            return Array.from({ length: 7 }, (_, i) => {
                const d = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
                const dStr = this.dateToStr(d);
                return {
                    date: d,
                    dateStr: dStr,
                    nomeDia: nomes[d.getDay()],
                    mesAbrev: d.toLocaleDateString('pt-BR', { month: 'short' }),
                    isToday: dStr === todayStr,
                };
            });
        },

        // ── Navegação ────────────────────────────────────────────

        prevPeriodo() {
            const d = new Date(this.currentDate);
            this.viewMode === 'month'
                ? d.setMonth(d.getMonth() - 1)
                : d.setDate(d.getDate() - 7);
            this.currentDate = d;
        },

        nextPeriodo() {
            const d = new Date(this.currentDate);
            this.viewMode === 'month'
                ? d.setMonth(d.getMonth() + 1)
                : d.setDate(d.getDate() + 7);
            this.currentDate = d;
        },

        irParaHoje() {
            this.currentDate = new Date();
        },

        periodoLabel() {
            if (this.viewMode === 'month') {
                return this.currentDate.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
            }
            const dias  = this.diasSemana();
            const first = dias[0].date;
            const last  = dias[6].date;
            const opts  = { day: 'numeric', month: 'short' };
            return `${first.toLocaleDateString('pt-BR', opts)} – ${last.toLocaleDateString('pt-BR', opts)}, ${last.getFullYear()}`;
        },

        // ── Dados ────────────────────────────────────────────────

        async fetchEventos() {
            this.loadingEventos = true;
            try {
                const params = new URLSearchParams({
                    tipo: this.tipoData,
                    status: this.statusFiltro,
                    ...(this.modalidadeFiltro ? { modalidade: this.modalidadeFiltro } : {}),
                });
                const res = await fetch(`{{ route('admin.planejamento.eventos') }}?${params}`);
                this.eventos = await res.json();
            } catch (e) {
                console.error('Erro ao carregar eventos:', e);
            } finally {
                this.loadingEventos = false;
            }
        },

        initCalendario() {
            this.fetchEventos();
        },

        alternarStatus(status) {
            this.statusFiltro = status;
            this.fetchEventos();
        },

        alternarModalidade(modalidade) {
            this.modalidadeFiltro = this.modalidadeFiltro === modalidade ? '' : modalidade;
            this.fetchEventos();
        },

        // ── Modal de detalhes ────────────────────────────────────

        abrirDetalhes(evento) {
            const [yr, mo, dt] = evento.start.split('-').map(Number);
            const date = new Date(yr, mo - 1, dt);
            this.eventoSelecionado = {
                title:         evento.title,
                objeto:        evento.extendedProps.objeto,
                status:        evento.extendedProps.status,
                cor:           evento.backgroundColor,
                url:           evento.url,
                dataFormatted: date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' }),
            };
            this.modalDetalhes = true;
        },
    }));
});
</script>
@endpush

<div x-show="modalCalendario"
    class="fixed inset-0 z-[3000] overflow-y-auto"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">

    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="modalCalendario = false"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-6xl overflow-hidden border border-gray-100">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 space-y-4">

                {{-- Linha 1: título + filtros + fechar --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Calendário de Planejamento</h3>
                            <p class="text-xs text-gray-500 font-medium">Visualize prazos e sessões agendadas</p>
                        </div>

                        <div class="inline-flex p-1 bg-gray-100 rounded-xl border border-gray-200">
                            <button @click="alternarStatus('todos')"
                                :class="statusFiltro === 'todos' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all duration-200">
                                Todos
                            </button>
                            <button @click="alternarStatus('aguardando_sessao')"
                                :class="statusFiltro === 'aguardando_sessao' ? 'bg-white text-amber-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Aguardando
                            </button>
                            <button @click="alternarStatus('em_andamento')"
                                :class="statusFiltro === 'em_andamento' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Andamento
                            </button>
                            <button @click="alternarStatus('em_recurso')"
                                :class="statusFiltro === 'em_recurso' ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                Recurso
                            </button>
                        </div>
                    </div>

                    <button @click="modalCalendario = false"
                        class="bg-white border border-gray-200 p-2 rounded-xl text-gray-400 hover:text-red-500 hover:border-red-100 transition-all duration-200 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Linha 2: filtros de modalidade --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-semibold text-gray-400 shrink-0">Modalidade</span>
                    <span class="w-px h-3.5 bg-gray-200 shrink-0"></span>
                    @foreach(\App\Enums\ModalidadeEnum::cases() as $mod)
                        <button @click="alternarModalidade('{{ $mod->value }}')"
                            :class="modalidadeFiltro === '{{ $mod->value }}'
                                ? 'bg-teal-700 text-white border-teal-700 shadow-sm'
                                : 'bg-white text-gray-500 border-gray-200 hover:border-teal-300 hover:text-teal-700'"
                            class="text-xs font-semibold px-2.5 py-1 rounded-full border transition-all duration-150">
                            {{ $mod->getDisplayName() }}
                        </button>
                    @endforeach
                </div>

                {{-- Linha 3: navegação + troca de view --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button @click="prevPeriodo()"
                            class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button @click="irParaHoje()"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 transition-colors shadow-sm">
                            Hoje
                        </button>
                        <button @click="nextPeriodo()"
                            class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <span class="text-base font-extrabold text-gray-900 capitalize ml-1" x-text="periodoLabel()"></span>
                    </div>

                    <div class="inline-flex p-1 bg-gray-100 rounded-xl border border-gray-200">
                        <button @click="viewMode = 'month'"
                            :class="viewMode === 'month' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200">
                            Mês
                        </button>
                        <button @click="viewMode = 'week'"
                            :class="viewMode === 'week' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200">
                            Semana
                        </button>
                    </div>
                </div>
            </div>

            {{-- Corpo --}}
            <div class="p-6">

                {{-- Loading --}}
                <div x-show="loadingEventos" class="flex items-center justify-center py-20">
                    <div class="w-8 h-8 border-2 border-teal-600 border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- Vista: Mês --}}
                <div x-show="!loadingEventos && viewMode === 'month'">
                    <div class="grid grid-cols-7 mb-1">
                        <template x-for="nome in ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']" :key="nome">
                            <div class="text-center text-xs font-bold text-gray-400 uppercase tracking-wider py-2" x-text="nome"></div>
                        </template>
                    </div>
                    <div class="grid grid-cols-7 gap-px bg-gray-100 rounded-xl overflow-hidden border border-gray-100">
                        <template x-for="(dia, idx) in diasMes()" :key="idx">
                            <div class="bg-white min-h-[88px] p-1.5 flex flex-col"
                                :class="{ 'opacity-40': !dia.currentMonth }">
                                <span class="text-xs font-bold self-start mb-1 w-6 h-6 flex items-center justify-center rounded-full leading-none shrink-0"
                                    :class="dia.isToday ? 'bg-teal-600 text-white' : 'text-gray-500'">
                                    <span x-text="dia.date.getDate()"></span>
                                </span>
                                <div class="flex flex-col gap-0.5 overflow-hidden">
                                    <template x-for="evento in eventosParaDia(dia.date)" :key="evento.id">
                                        <button @click="abrirDetalhes(evento)"
                                            class="w-full text-left rounded px-1.5 py-0.5 text-[10px] font-bold truncate text-white leading-tight transition-all hover:brightness-110 active:scale-95"
                                            :style="`background-color: ${evento.backgroundColor}`"
                                            x-text="evento.title">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Vista: Semana --}}
                <div x-show="!loadingEventos && viewMode === 'week'">
                    <div class="grid grid-cols-7 gap-px bg-gray-100 rounded-xl overflow-hidden border border-gray-100">
                        <template x-for="dia in diasSemana()" :key="dia.dateStr">
                            <div class="bg-white flex flex-col"
                                :class="dia.isToday ? 'bg-teal-50/60' : ''">
                                {{-- Cabeçalho do dia --}}
                                <div class="px-2 py-3 text-center border-b"
                                    :class="dia.isToday ? 'border-teal-100' : 'border-gray-100'">
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="dia.nomeDia"></div>
                                    <div class="w-8 h-8 mx-auto mt-1 flex items-center justify-center rounded-full text-sm font-extrabold"
                                        :class="dia.isToday ? 'bg-teal-600 text-white' : 'text-gray-700'"
                                        x-text="dia.date.getDate()">
                                    </div>
                                    <div class="text-[9px] text-gray-400 mt-0.5" x-text="dia.mesAbrev"></div>
                                </div>
                                {{-- Eventos --}}
                                <div class="p-1.5 min-h-[140px] flex flex-col gap-1">
                                    <template x-for="evento in eventosParaDia(dia.date)" :key="evento.id">
                                        <button @click="abrirDetalhes(evento)"
                                            class="w-full text-left rounded-lg px-2 py-1.5 leading-tight text-white transition-all hover:brightness-110 active:scale-95"
                                            :style="`background-color: ${evento.backgroundColor}`">
                                            <div class="text-[10px] font-bold truncate" x-text="evento.title"></div>
                                            <div class="text-[9px] font-medium opacity-80 truncate mt-0.5" x-text="evento.extendedProps.status"></div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sub-modal: Detalhes do Evento --}}
    <div x-show="modalDetalhes"
        class="fixed inset-0 z-[4000] flex items-center justify-center p-4"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">

        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-[2px]" @click="modalDetalhes = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100" @click.stop>
            <div :style="`background-color: ${eventoSelecionado?.cor}`" class="h-2 w-full"></div>

            <div class="p-6 space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Sessão Pública</span>
                        <h4 class="text-xl font-bold text-gray-900 leading-tight" x-text="eventoSelecionado?.title"></h4>
                    </div>
                    <button @click="modalDetalhes = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Status Atual</span>
                        <span class="text-sm font-bold text-gray-700 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :style="`background-color: ${eventoSelecionado?.cor}`"></span>
                            <span x-text="eventoSelecionado?.status"></span>
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Data Agendada</span>
                        <span class="text-sm font-bold text-gray-700" x-text="eventoSelecionado?.dataFormatted"></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase">Objeto do Processo</span>
                    <p class="text-sm text-gray-600 leading-relaxed bg-gray-50/50 rounded-xl p-4 border border-gray-100 italic"
                        x-text="eventoSelecionado?.objeto"></p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="modalDetalhes = false"
                        class="flex-1 px-4 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200">
                        Fechar
                    </button>
                    <a :href="eventoSelecionado?.url"
                        class="flex-1 px-4 py-2.5 bg-teal-700 hover:bg-teal-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-teal-700/20 text-center transition-all duration-200 flex items-center justify-center gap-2">
                        Ver Processo Completo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
