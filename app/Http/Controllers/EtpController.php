<?php

namespace App\Http\Controllers;

use App\Models\EtpItem;
use App\Models\Unidade;
use App\Models\User;
use App\Services\EtpItemService;
use App\Services\EtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EtpController extends Controller
{
    protected $etpService;
    protected $etpItemService;

    public function __construct(EtpService $etpService, EtpItemService $etpItemService)
    {
        $this->etpService = $etpService;
        $this->etpItemService = $etpItemService;
    }

    public function index(Request $request)
    {
        Log::info('Acessando listagem de ETPs', [
            'user_id' => auth()->id(),
            'prefeitura_id' => auth()->user()->prefeitura_id,
            'filters' => $request->only(['status'])
        ]);

        $prefeituraId = auth()->user()->prefeitura_id;
        $filters = $request->only(['status']);
        $etps = $this->etpService->getByPrefeituraId($prefeituraId, $filters);

        Log::info('ETPs listados com sucesso', [
            'user_id' => auth()->id(),
            'total_etps' => $etps->count()
        ]);

        return view('Admin.Etps.index', compact('etps', 'filters'));
    }

    public function create()
    {
        Log::info('Acessando formulário de criação de ETP', [
            'user_id' => auth()->id(),
            'prefeitura_id' => auth()->user()->prefeitura_id
        ]);

        $prefeituraId = auth()->user()->prefeitura_id;

        $secretarias = Unidade::where('prefeitura_id', $prefeituraId)->orderBy('nome', 'asc')->get();

        $itens = $this->etpItemService->getAllForSelect();

        Log::info('Dados carregados para criação de ETP', [
            'user_id' => auth()->id(),
            'total_secretarias' => $secretarias->count(),
            'total_itens' => $itens->count()
        ]);

        return view('Admin.Etps.create', compact('secretarias', 'itens'));
    }

    public function store(Request $request)
    {
        Log::info('Iniciando criação de ETP', [
            'user_id' => auth()->id(),
            'prefeitura_id' => auth()->user()->prefeitura_id,
            'action_type' => $request->input('action_type'),
            'tipo_contratacao' => $request->input('tipo_contratacao'),
            'modalidade' => $request->input('modalidade'),
            'is_ajax' => $request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0'
        ]);

        // Validação base
        $rules = [
            'secretaria_id'       => 'required|exists:unidades,id',
            'servidor_responsavel' => 'required|string|max:255',
            'objeto_licitacao'    => 'required|string',
            'justificativa_necessidade' => 'required|string',
            'modalidade'          => 'required|in:pregao,concorrencia,dispensa,inexigibilidade',
            'tipo_contratacao'    => 'required_if:modalidade,pregao,dispensa|in:item,lote,servicos,compras,obras',
            'dotacao_orcamentaria' => 'required|string',
            'prazo_entrega'       => 'required|string',
            'cotacao_path'        => 'nullable|file|max:90240',
            'action_type'         => 'nullable|in:salvar,concluir',
            'should_redirect'     => 'nullable|in:0,1',
        ];

        // Para itens sem lote
        $rules['itens']                   = 'required_if:tipo_contratacao,item|array';
        $rules['itens.*.item_id']         = 'required_if:tipo_contratacao,item|exists:etp_itens,id';
        $rules['itens.*.unidade']         = 'required_if:tipo_contratacao,item|string|max:100';
        $rules['itens.*.quantidade']      = 'required_if:tipo_contratacao,item|numeric|min:0.01';

        // Para lotes (O segredo é remover o índice fixo do item na regra se for usar IDs como chaves)
        $rules['lotes']                       = 'required_if:tipo_contratacao,lote|array';
        $rules['lotes.*.nome']                = 'required_if:tipo_contratacao,lote|string|max:255';
        $rules['lotes.*.itens']               = 'required_if:tipo_contratacao,lote|array';
        // Usamos '*' duas vezes para validar qualquer chave dentro de itens
        $rules['lotes.*.itens.*.item_id']     = 'required_if:tipo_contratacao,lote';
        $rules['lotes.*.itens.*.unidade']     = 'required_if:tipo_contratacao,lote|string|max:100';
        $rules['lotes.*.itens.*.quantidade']  = 'required_if:tipo_contratacao,lote|numeric|min:0.01';

        try {
            $request->validate($rules);
            Log::info('Validação do ETP passou', ['user_id' => auth()->id()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Falha na validação do ETP', [
                'user_id' => auth()->id(),
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        $data = $request->all();
        $data['prefeitura_id'] = auth()->user()->prefeitura_id;

        // Define status conforme o botão clicado:
        //   "Salvar"   → pendente  (rascunho, não enviado para análise)
        //   "Concluir" → em_analise (envia para análise do admin)
        $data['status'] = ($request->input('action_type') === 'concluir')
            ? 'em_analise'
            : 'pendente';

        Log::info('Status definido para ETP', [
            'user_id' => auth()->id(),
            'action_type' => $request->input('action_type'),
            'status' => $data['status']
        ]);

        try {
            $etp = $this->etpService->store($data, $request->file('cotacao_path'));

            Log::info('ETP criado com sucesso', [
                'user_id' => auth()->id(),
                'etp_id' => $etp->id,
                'status' => $etp->status,
                'prefeitura_id' => $etp->prefeitura_id
            ]);

            $mensagem = $data['status'] === 'em_analise'
                ? 'ETP concluído e enviado para análise com sucesso.'
                : 'ETP salvo como rascunho. Você pode editá-lo a qualquer momento.';

            // Verifica se é uma requisição AJAX (salvar rascunho sem redirecionar)
            if ($request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0') {
                Log::info('Retornando resposta JSON para criação de ETP', [
                    'user_id' => auth()->id(),
                    'etp_id' => $etp->id,
                    'status' => $data['status']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'etp_id' => $etp->id,
                    'status' => $data['status']
                ]);
            }

            // Redirecionamento normal para conclusão
            Log::info('Redirecionando após criação de ETP', [
                'user_id' => auth()->id(),
                'etp_id' => $etp->id
            ]);

            return redirect()
                ->route('admin.etps.index')
                ->with('success', $mensagem);
                
        } catch (\Exception $e) {
            Log::error('Erro ao criar ETP', [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Se for AJAX, retorna erro em JSON
            if ($request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar ETP: ' . $e->getMessage()
                ], 422);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar ETP: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        Log::info('Visualizando ETP', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'prefeitura_id' => auth()->user()->prefeitura_id
        ]);

        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            Log::warning('Tentativa de acesso negado a ETP', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'etp_prefeitura_id' => $etp->prefeitura_id,
                'user_prefeitura_id' => auth()->user()->prefeitura_id
            ]);
            abort(403, 'Acesso negado.');
        }

        Log::info('ETP visualizado com sucesso', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'status' => $etp->status
        ]);

        return view('Admin.Etps.show', compact('etp'));
    }

    public function edit($id)
    {
        Log::info('Acessando edição de ETP', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'prefeitura_id' => auth()->user()->prefeitura_id
        ]);

        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            Log::warning('Tentativa de edição negada - permissão', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'etp_prefeitura_id' => $etp->prefeitura_id,
                'user_prefeitura_id' => auth()->user()->prefeitura_id
            ]);
            abort(403, 'Acesso negado.');
        }

        if ($etp->status !== 'pendente') {
            Log::warning('Tentativa de edição de ETP não pendente', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'status' => $etp->status
            ]);

            return redirect()
                ->route('admin.etps.show', $id)
                ->with('error', 'Apenas ETPs com status "pendente" podem ser editados.');
        }

        $prefeituraId = auth()->user()->prefeitura_id;
        $secretarias  = Unidade::where('prefeitura_id', $prefeituraId)->orderBy('nome', 'asc')->get();
        $itens        = $this->etpItemService->getAllForSelect();

        Log::info('Dados carregados para edição de ETP', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'total_secretarias' => $secretarias->count(),
            'total_itens' => $itens->count()
        ]);

        return view('Admin.Etps.edit', compact('etp', 'secretarias', 'itens'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Iniciando atualização de ETP', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'action_type' => $request->input('action_type'),
            'tipo_contratacao' => $request->input('tipo_contratacao'),
            'is_ajax' => $request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0'
        ]);

        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            Log::warning('Tentativa de atualização negada - permissão', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'etp_prefeitura_id' => $etp->prefeitura_id,
                'user_prefeitura_id' => auth()->user()->prefeitura_id
            ]);
            abort(403, 'Acesso negado.');
        }

        if ($etp->status !== 'pendente') {
            Log::warning('Tentativa de atualização de ETP não pendente', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'status_atual' => $etp->status
            ]);

            return redirect()
                ->route('admin.etps.show', $id)
                ->with('error', 'Apenas ETPs com status "pendente" podem ser editados.');
        }

        $rules = [
            'secretaria_id'       => 'required|exists:unidades,id',
            'servidor_responsavel' => 'required|string|max:255',
            'objeto_licitacao'    => 'required|string',
            'justificativa_necessidade' => 'required|string',
            'modalidade'          => 'required|in:pregao,concorrencia,dispensa,inexigibilidade',
            'tipo_contratacao'    => 'required_if:modalidade,pregao,dispensa|in:item,lote,servicos,compras,obras',
            'dotacao_orcamentaria' => 'required|string',
            'prazo_entrega'       => 'required|string',
            'cotacao_path'        => 'nullable|file|max:90240',
            'action_type'         => 'nullable|in:salvar,concluir',
            'should_redirect'     => 'nullable|in:0,1',
        ];

        // Para itens sem lote
        $rules['itens']                   = 'required_if:tipo_contratacao,item|array';
        $rules['itens.*.item_id']         = 'required_if:tipo_contratacao,item|exists:etp_itens,id';
        $rules['itens.*.unidade']         = 'required_if:tipo_contratacao,item|string|max:100';
        $rules['itens.*.quantidade']      = 'required_if:tipo_contratacao,item|numeric|min:0.01';

        // Para lotes (O segredo é remover o índice fixo do item na regra se for usar IDs como chaves)
        $rules['lotes']                       = 'required_if:tipo_contratacao,lote|array';
        $rules['lotes.*.nome']                = 'required_if:tipo_contratacao,lote|string|max:255';
        $rules['lotes.*.itens']               = 'required_if:tipo_contratacao,lote|array';
        // Usamos '*' duas vezes para validar qualquer chave dentro de itens
        $rules['lotes.*.itens.*.item_id']     = 'required_if:tipo_contratacao,lote';
        $rules['lotes.*.itens.*.unidade']     = 'required_if:tipo_contratacao,lote|string|max:100';
        $rules['lotes.*.itens.*.quantidade']  = 'required_if:tipo_contratacao,lote|numeric|min:0.01';

        try {
            $request->validate($rules);
            Log::info('Validação da atualização passou', [
                'user_id' => auth()->id(),
                'etp_id' => $id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Falha na validação da atualização', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        $data = $request->all();

        // Define status conforme o botão clicado
        $data['status'] = ($request->input('action_type') === 'concluir')
            ? 'em_analise'
            : 'pendente';

        Log::info('Status definido para atualização', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'action_type' => $request->input('action_type'),
            'novo_status' => $data['status']
        ]);

        try {
            $etp = $this->etpService->update($id, $data, $request->file('cotacao_path'));

            Log::info('ETP atualizado com sucesso', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'novo_status' => $etp->status
            ]);

            $mensagem = $data['status'] === 'em_analise'
                ? 'ETP concluído e enviado para análise com sucesso.'
                : 'ETP salvo como rascunho com sucesso.';

            // Verifica se é uma requisição AJAX (salvar rascunho sem redirecionar)
            if ($request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0') {
                Log::info('Retornando resposta JSON para atualização', [
                    'user_id' => auth()->id(),
                    'etp_id' => $id,
                    'status' => $data['status']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'etp_id' => $etp->id,
                    'status' => $data['status']
                ]);
            }

            // Redirecionamento normal para conclusão
            Log::info('Redirecionando após atualização', [
                'user_id' => auth()->id(),
                'etp_id' => $id
            ]);

            return redirect()
                ->route('admin.etps.show', $id)
                ->with('success', $mensagem);
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar ETP', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar ETP: ' . $e->getMessage()
                ], 422);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar ETP: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        Log::info('Iniciando exclusão de ETP', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'prefeitura_id' => auth()->user()->prefeitura_id
        ]);

        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            Log::warning('Tentativa de exclusão negada - permissão', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'etp_prefeitura_id' => $etp->prefeitura_id,
                'user_prefeitura_id' => auth()->user()->prefeitura_id
            ]);
            abort(403, 'Acesso negado.');
        }

        if ($etp->status !== 'pendente') {
            Log::warning('Tentativa de exclusão de ETP não pendente', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'status' => $etp->status
            ]);

            return redirect()
                ->route('admin.etps.show', $id)
                ->with('error', 'Apenas ETPs com status "pendente" podem ser excluídos.');
        }

        try {
            $this->etpService->delete($id);

            Log::info('ETP excluído com sucesso', [
                'user_id' => auth()->id(),
                'etp_id' => $id
            ]);

            return redirect()
                ->route('admin.etps.index')
                ->with('success', 'ETP excluído com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir ETP', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return back()->with('error', 'Erro ao excluir ETP: ' . $e->getMessage());
        }
    }

    public function criarItemRapido(Request $request)
    {
        Log::info('Iniciando criação rápida de item', [
            'user_id' => auth()->id(),
            'descricao_item' => $request->descricao_item
        ]);

        try {
            $request->validate([
                'descricao_item' => 'required|string|max:500',
                'unidade_medida' => 'nullable|string|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validação falhou na criação rápida de item', [
                'user_id' => auth()->id(),
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        try {
            // Usa firstOrCreate para evitar duplicatas idênticas (case-insensitive)
            $item = EtpItem::whereRaw('LOWER(descricao_item) = ?', [strtolower(trim($request->descricao_item))])
                ->first();

            if ($item) {
                Log::info('Item já existente retornado', [
                    'user_id' => auth()->id(),
                    'item_id' => $item->id,
                    'descricao' => $item->descricao_item
                ]);

                return response()->json([
                    'success' => true,
                    'item'    => $item,
                    'message' => 'Item já existente retornado.',
                ]);
            }

            $item = EtpItem::create([
                'descricao_item' => trim($request->descricao_item),
                'unidade_medida' => $request->unidade_medida ? trim($request->unidade_medida) : null,
            ]);

            Log::info('Novo item criado com sucesso', [
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'descricao' => $item->descricao_item
            ]);

            return response()->json([
                'success' => true,
                'item'    => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar item rápido', [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importarItensEtp(Request $request)
    {
        Log::info('Iniciando importação de itens via Excel', [
            'user_id' => auth()->id(),
            'arquivo_original' => $request->file('arquivo_excel')?->getClientOriginalName(),
            'tamanho' => $request->file('arquivo_excel')?->getSize()
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'arquivo_excel' => [
                    'required',
                    'file',
                    'max:10240',
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                            $fail('O arquivo deve ser do tipo: xlsx, xls ou csv.');
                        }
                    }
                ]
            ]);

            if ($validator->fails()) {
                Log::warning('Validação do arquivo falhou', [
                    'user_id' => auth()->id(),
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $arquivo     = $request->file('arquivo_excel');
            $spreadsheet = IOFactory::load($arquivo->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            Log::info('Arquivo carregado', [
                'user_id' => auth()->id(),
                'total_linhas' => count($rows)
            ]);

            if (count($rows) < 2) {
                Log::warning('Arquivo sem dados suficientes', [
                    'user_id' => auth()->id(),
                    'linhas' => count($rows)
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'A planilha precisa ter pelo menos uma linha de dados.'
                ], 422);
            }

            $cabecalho = $rows[0];
            $cabecalhoNormalizado = array_map(function ($valor) {
                $valor = trim($valor);
                $valor = preg_replace('/^\xEF\xBB\xBF/', '', $valor);
                $valor = iconv('UTF-8', 'ASCII//TRANSLIT', $valor);
                return strtolower($valor);
            }, $cabecalho);

            $mapa = ['descricao' => null, 'unidade' => null, 'quantidade' => null];
            foreach ($cabecalhoNormalizado as $index => $coluna) {
                if (str_contains($coluna, 'descri'))  $mapa['descricao']  = $index;
                if (str_contains($coluna, 'unidad'))  $mapa['unidade']    = $index;
                if (str_contains($coluna, 'quant'))   $mapa['quantidade'] = $index;
            }

            if (in_array(null, $mapa, true)) {
                Log::warning('Cabeçalho inválido', [
                    'user_id' => auth()->id(),
                    'cabecalho_encontrado' => $cabecalho,
                    'mapa' => $mapa
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cabeçalho inválido. Use: Descricao | Unidade | Quantidade'
                ], 422);
            }

            $itensImportados = [];
            $erros = [];

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                if ($index === 0) continue;
                $linhaExcel = $index + 1;

                $descricao  = trim($row[$mapa['descricao']]  ?? '');
                $unidade    = trim($row[$mapa['unidade']]    ?? '');
                $quantidade = trim($row[$mapa['quantidade']] ?? '');

                if ($descricao === '' && $unidade === '' && $quantidade === '') continue;

                if ($descricao === '' || $unidade === '' || $quantidade === '') {
                    $erros[] = "Linha {$linhaExcel}: campos obrigatórios não preenchidos.";
                    Log::warning('Linha com campos vazios', [
                        'user_id' => auth()->id(),
                        'linha' => $linhaExcel,
                        'descricao' => $descricao,
                        'unidade' => $unidade,
                        'quantidade' => $quantidade
                    ]);
                    continue;
                }

                if (!is_numeric($quantidade) || (int)$quantidade <= 0) {
                    $erros[] = "Linha {$linhaExcel}: quantidade inválida.";
                    Log::warning('Quantidade inválida', [
                        'user_id' => auth()->id(),
                        'linha' => $linhaExcel,
                        'quantidade' => $quantidade
                    ]);
                    continue;
                }

                $item = EtpItem::whereRaw('LOWER(descricao_item) = ?', [strtolower($descricao)])->first();
                if (!$item) {
                    Log::info('Criando novo item durante importação', [
                        'user_id' => auth()->id(),
                        'descricao' => $descricao
                    ]);
                    $item = EtpItem::create(['descricao_item' => $descricao]);
                }

                $itensImportados[] = [
                    'item_id'   => $item->id,
                    'descricao' => $item->descricao_item,
                    'unidade'   => $unidade,
                    'quantidade' => (int)$quantidade
                ];
            }

            DB::commit();

            Log::info('Importação concluída', [
                'user_id' => auth()->id(),
                'itens_importados' => count($itensImportados),
                'erros' => count($erros)
            ]);

            return response()->json([
                'success' => true,
                'itens'   => $itensImportados,
                'erros'   => $erros
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na importação', [
                'user_id' => auth()->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarPdf($id)
    {
        Log::info('Iniciando geração de PDF', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'prefeitura_id' => auth()->user()->prefeitura_id
        ]);

        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            Log::warning('Tentativa de gerar PDF negada - permissão', [
                'user_id' => auth()->id(),
                'etp_id' => $id,
                'etp_prefeitura_id' => $etp->prefeitura_id,
                'user_prefeitura_id' => auth()->user()->prefeitura_id
            ]);
            abort(403, 'Acesso negado.');
        }

        Log::info('PDF gerado com sucesso', [
            'user_id' => auth()->id(),
            'etp_id' => $id
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('Admin.Etps.pdf.solicitacao', compact('etp'))
            ->setPaper('a4', 'portrait');

        $filename = 'ETP-' . str_pad($etp->id, 4, '0', STR_PAD_LEFT) . '_' . $etp->created_at->format('Y') . '_solicitacao.pdf';

        return $pdf->download($filename);
    }

    public function exportItens($id)
    {
        Log::info('Iniciando exportação de itens para Excel', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'prefeitura_id' => auth()->user()->prefeitura_id
        ]);

        $etp = $this->etpService->findById($id);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Itens');

        $headers = ['Descrição do Item', 'Unidade', 'Quantidade', 'Lote'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        $row = 2;

        if ($etp->tipo_contratacao === 'lote' && $etp->lotes->count() > 0) {
            foreach ($etp->lotes as $lote) {
                foreach ($lote->itens as $item) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $item->descricao_item);
                    $sheet->setCellValueByColumnAndRow(2, $row, $item->pivot->unidade);
                    $sheet->setCellValueByColumnAndRow(3, $row, $item->pivot->quantidade);
                    $sheet->setCellValueByColumnAndRow(4, $row, $lote->nome);
                    $row++;
                }
            }
        } else {
            foreach ($etp->itens as $item) {
                $sheet->setCellValueByColumnAndRow(1, $row, $item->descricao_item);
                $sheet->setCellValueByColumnAndRow(2, $row, $item->pivot->unidade);
                $sheet->setCellValueByColumnAndRow(3, $row, $item->pivot->quantidade);
                $sheet->setCellValueByColumnAndRow(4, $row, '');
                $row++;
            }
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $etpNumFile = 'ETP-' . str_pad($etp->id, 4, '0', STR_PAD_LEFT) . '-' . $etp->created_at->format('Y');
        $filename   = $etpNumFile . '_itens.xlsx';
        $writer     = new Xlsx($spreadsheet);

        Log::info('Exportação concluída', [
            'user_id' => auth()->id(),
            'etp_id' => $id,
            'total_itens' => $row - 2,
            'filename' => $filename
        ]);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}