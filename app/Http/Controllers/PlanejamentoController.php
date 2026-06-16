<?php

namespace App\Http\Controllers;

use App\Models\Prefeitura;
use App\Models\Processo;
use App\Models\ProcessoNota;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanejamentoController extends Controller
{
    public static function statusConfig(): array
    {
        return [
            'em_elaboracao' => [
                'label'     => 'Elaboração',
                'cor_col'   => 'border-indigo-200 bg-indigo-50/50',
                'cor_head'  => 'bg-indigo-600 text-white',
                'cor_badge' => 'bg-indigo-100 text-indigo-700',
            ],
            'aguardando_sessao' => [
                'label'     => 'Aguardando Sessão',
                'cor_col'   => 'border-amber-200 bg-amber-50/50',
                'cor_head'  => 'bg-amber-500 text-white',
                'cor_badge' => 'bg-amber-100 text-amber-700',
            ],
            'em_andamento' => [
                'label'     => 'Em Andamento',
                'cor_col'   => 'border-emerald-200 bg-emerald-50/50',
                'cor_head'  => 'bg-emerald-600 text-white',
                'cor_badge' => 'bg-emerald-100 text-emerald-700',
            ],
            'em_recurso' => [
                'label'     => 'Em Recurso',
                'cor_col'   => 'border-orange-200 bg-orange-50/50',
                'cor_head'  => 'bg-orange-500 text-white',
                'cor_badge' => 'bg-orange-100 text-orange-700',
            ],
            'concluida' => [
                'label'     => 'Concluída',
                'cor_col'   => 'border-green-200 bg-green-50/50',
                'cor_head'  => 'bg-green-600 text-white',
                'cor_badge' => 'bg-green-100 text-green-700',
            ],
        ];
    }

    public function index(Request $request): View
    {
        $prefeituras  = Prefeitura::orderBy('nome')->get();
        $statusConfig = self::statusConfig();

        $processos = Processo::with('prefeitura', 'notas')
            ->when($request->filled('prefeitura_id'), fn($q) => $q->where('prefeitura_id', $request->prefeitura_id))
            ->when($request->filled('status'), fn($q) => $q->where('planejamento_status', $request->status))
            ->when($request->filled('data_de'), fn($q) => $q->where('planejamento_data_abertura', '>=', $request->data_de))
            ->when($request->filled('data_ate'), fn($q) => $q->where('planejamento_data_abertura', '<=', $request->data_ate))
            ->orderBy('updated_at', 'desc')
            ->get();

        $colunas = [];
        foreach (array_keys($statusConfig) as $status) {
            $colunas[$status] = $processos->filter(fn($p) => $p->planejamento_status === $status)->values();
        }

        return view('Admin.Planejamento.index', compact('prefeituras', 'colunas', 'processos', 'statusConfig'));
    }

    public function show(Processo $processo): View
    {
        $processo->load(['prefeitura', 'notas.user', 'detalhe']);
        $statusConfig = self::statusConfig();

        return view('Admin.Planejamento.show', compact('processo', 'statusConfig'));
    }

    public function updateStatus(Request $request, Processo $processo): RedirectResponse
    {
        $novoStatus = $request->input('planejamento_status');

        match ($novoStatus) {
            'aguardando_sessao' => $this->avancarParaAguardando($request, $processo),
            'concluida'         => $this->concluir($processo),
            'em_recurso'        => $this->iniciarRecurso($processo),
            default             => abort(422, 'Transição de status inválida.'),
        };

        return back()->with('sucesso', 'Status do processo atualizado.');
    }

    public function storeNota(Request $request, Processo $processo): RedirectResponse
    {
        $request->validate([
            'texto' => ['required', 'string', 'max:1000'],
            'anexo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif'],
        ]);

        $anexoPath = null;
        $anexoNome = null;

        if ($request->hasFile('anexo')) {
            $file      = $request->file('anexo');
            $anexoNome = $file->getClientOriginalName();
            $anexoPath = $file->store("processos/{$processo->id}/notas", 'public');
        }

        ProcessoNota::create([
            'processo_id'     => $processo->id,
            'user_id'         => auth()->id(),
            'status_em_vigor' => $processo->planejamento_status,
            'texto'           => $request->texto,
            'anexo_path'      => $anexoPath,
            'anexo_nome'      => $anexoNome,
        ]);

        return back()->with('sucesso', 'Nota adicionada com sucesso.');
    }

    private function avancarParaAguardando(Request $request, Processo $processo): void
    {
        abort_if(
            $processo->planejamento_status !== 'em_elaboracao',
            422,
            'Transição inválida para Aguardando Sessão.'
        );

        $request->validate([
            'data_abertura' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'data_abertura.required'       => 'A data de abertura é obrigatória.',
            'data_abertura.after_or_equal' => 'A data de abertura não pode ser no passado.',
        ]);

        $processo->update([
            'planejamento_status'        => 'aguardando_sessao',
            'planejamento_data_abertura' => Carbon::parse($request->data_abertura),
        ]);
    }

    private function concluir(Processo $processo): void
    {
        abort_if(
            ! in_array($processo->planejamento_status, ['em_andamento', 'em_recurso']),
            422,
            'Transição inválida para Concluída.'
        );

        $processo->update(['planejamento_status' => 'concluida']);
    }

    private function iniciarRecurso(Processo $processo): void
    {
        abort_if(
            $processo->planejamento_status !== 'em_andamento',
            422,
            'Transição inválida para Em Recurso.'
        );

        $processo->update([
            'planejamento_status'      => 'em_recurso',
            'planejamento_fim_recurso' => Processo::calcularFimRecursoSessao(Carbon::now()),
        ]);
    }
}
