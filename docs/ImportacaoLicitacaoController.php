<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\Item;
use App\Models\Secretaria;
use App\Services\LicitacoesService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportacaoLicitacaoController extends Controller
{
    protected $service;

    public function __construct(LicitacoesService $service)
    {
        $this->service = $service;
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $contratosExternos = $this->service->buscarContratosDisponiveis();

        $codigosExistentes = Contrato::whereNotNull('codigo_integracao')
            ->pluck('codigo_integracao')
            ->toArray();

        foreach ($contratosExternos as &$contrato) {
            $contrato['ja_importado'] = in_array($contrato['codigo_integracao'], $codigosExistentes);
        }
        unset($contrato);

        $colecao = collect($contratosExternos);

        // Obter prefeituras únicas para o filtro ANTES de filtrar a coleção
        $listaPrefeituras = $colecao->pluck('prefeitura_nome')->filter()->unique()->sort()->values();

        // Aplicar filtros
        if ($request->filled('pesquisa')) {
            $pesquisa = strtolower($request->pesquisa);
            $colecao = $colecao->filter(function ($c) use ($pesquisa) {
                return str_contains(strtolower($c['numero_processo'] ?? ''), $pesquisa) ||
                       str_contains(strtolower($c['numero_contrato'] ?? ''), $pesquisa) ||
                       str_contains(strtolower($c['objeto'] ?? ''), $pesquisa);
            });
        }

        if ($request->filled('prefeitura_nome')) {
            $prefNome = $request->prefeitura_nome;
            $colecao = $colecao->filter(function ($c) use ($prefNome) {
                return ($c['prefeitura_nome'] ?? '') === $prefNome;
            });
        }

        if ($request->filled('fornecedor')) {
            $forn = strtolower($request->fornecedor);
            $colecao = $colecao->filter(function ($c) use ($forn) {
                $razao = strtolower($c['fornecedor']['razao_social'] ?? '');
                $cnpj = strtolower($c['fornecedor']['cnpj'] ?? '');
                return str_contains($razao, $forn) || str_contains($cnpj, $forn);
            });
        }

        if ($request->filled('situacao')) {
            $situacao = $request->situacao;
            if ($situacao === 'importado') {
                $colecao = $colecao->filter(fn($c) => $c['ja_importado']);
            } elseif ($situacao === 'pendente') {
                $colecao = $colecao->filter(fn($c) => !$c['ja_importado']);
            }
        }

        $paginaAtual = LengthAwarePaginator::resolveCurrentPage();
        $porPagina = 10;

        $colecaoOrdenada = $colecao->sortBy('ja_importado')->values();

        $itensAtuais = $colecaoOrdenada->slice(($paginaAtual - 1) * $porPagina, $porPagina);

        $contratosPaginados = new LengthAwarePaginator(
            $itensAtuais,
            $colecaoOrdenada->count(),
            $porPagina,
            $paginaAtual,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $query = Secretaria::with('prefeitura');

        if (auth()->user()->prefeitura_id) {
            $query->where('prefeitura_id', auth()->user()->prefeitura_id);
        }

        $todasSecretarias = $query->orderBy('nome')->get();

        $mapaSecretarias = [];
        foreach ($todasSecretarias as $sec) {
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $sec->prefeitura->cnpj);

            $mapaSecretarias[$cnpjLimpo][] = [
                'id' => $sec->id,
                'nome' => $sec->nome
            ];
        }

        return view('contratos.importacao.index', [
            'contratosExternos' => $contratosPaginados,
            'mapaSecretarias' => $mapaSecretarias,
            'listaPrefeituras' => $listaPrefeituras
        ]);
    }

    public function store(Request $request)
    {
        // Validação
        $request->validate([
            'secretaria_id' => 'nullable|exists:secretarias,id',
            'dados_contrato' => 'required|json'
        ]);

        $dados = json_decode($request->dados_contrato, true);

        $cnpjLimpo = preg_replace('/[^0-9]/', '', $dados['prefeitura_cnpj'] ?? '');
        $cnpjFormatado = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpjLimpo);

        $prefeitura = \App\Models\Prefeitura::where(function($query) use ($cnpjLimpo, $cnpjFormatado) {
            $query->where('cnpj', $cnpjLimpo)
                ->orWhere('cnpj', $cnpjFormatado);
        })->first();

        if (!$prefeitura && auth()->user()->prefeitura_id) {
            $prefeitura = \App\Models\Prefeitura::find(auth()->user()->prefeitura_id);
        }

        if (!$prefeitura) {
            return back()->with('error', 'Prefeitura não identificada pelo CNPJ (' . $cnpjFormatado . '). Verifique o cadastro.');
        }

        $prefeituraIdDestino = $prefeitura->id;
        $secretariaId = $request->secretaria_id;

        DB::beginTransaction();

        try {
            if (empty($secretariaId)) {
                $secretariaGenerica = \App\Models\Secretaria::firstOrCreate(
                    [
                        'prefeitura_id' => $prefeituraIdDestino,
                        'nome' => 'NÃO INFORMADA'
                    ],
                    [
                        'titular' => 'Não Informado'
                    ]
                );
                $secretariaId = $secretariaGenerica->id;
            }

            if (Contrato::where('codigo_integracao', $dados['codigo_integracao'])->exists()) {
                return back()->with('error', 'Este contrato já foi importado anteriormente.');
            }

            $empresa = Empresa::firstOrCreate(
                [
                    'cnpj' => $dados['fornecedor']['cnpj'],
                    'prefeitura_id' => $prefeituraIdDestino
                ],
                [
                    'nome' => $dados['fornecedor']['razao_social'],
                    'representante' => $dados['fornecedor']['representante'] ?? 'Não informado',
                    'rua' => $dados['fornecedor']['endereco'] ?? 'Endereço Externo',
                    'numero' => 'S/N',
                    'bairro' => 'Centro',
                    'cidade' => 'Importado',
                    'cep' => '00000-000'
                ]
            );

            $contrato = Contrato::create([
                'empresa_id' => $empresa->id,
                'prefeitura_id' => $prefeituraIdDestino,
                'codigo_integracao' => $dados['codigo_integracao'],
                'numero_processo' => $dados['numero_processo'],
                'numero_contrato' => $dados['numero_contrato'],
                // 'modalidade' => 'Pregão',
                'modalidade' => $dados['modalidade'] ?? 'Pregão',
                'tipo_contrato' => 'Fornecimento',
                'objeto' => strip_tags(html_entity_decode($dados['objeto'])),
                'valor_total' => $dados['valor_total_vencedor'],
                'data_assinatura' => $dados['data_assinatura'],
                'data_inicio' => $dados['data_assinatura'],
                'data_finalizacao' => \Carbon\Carbon::parse($dados['data_assinatura'])->addYear(),
            ]);

            // Vincula a secretaria ao contrato via pivot
            $contrato->secretarias()->sync([$secretariaId]);

            $itensPorLote = collect($dados['itens'])->groupBy('lote_numero');

            foreach ($itensPorLote as $numeroLote => $itensDoLote) {

                $nomeLote = ($numeroLote == 0 || empty($numeroLote))
                    ? "Lote Único"
                    : "Lote {$numeroLote}";

                $lote = Lote::create([
                    'contrato_id' => $contrato->id,
                    'nome' => $nomeLote
                ]);

                foreach ($itensDoLote as $itemExterno) {
                    Item::create([
                        'empresa_id' => $empresa->id,
                        'lote_id' => $lote->id,
                        'descricao' => $itemExterno['descricao'],
                        'unidade' => $itemExterno['unidade'],
                        'quantidade' => $itemExterno['quantidade'],
                        'valor_unitario' => $itemExterno['valor_unitario'],
                        'valor_total' => $itemExterno['valor_total']
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('contratos.index')->with('success', 'Contrato importado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na importação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao importar: ' . $e->getMessage());
        }
    }
}
