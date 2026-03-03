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
        $prefeituraId = auth()->user()->prefeitura_id;
        $filters = $request->only(['status']);
        $etps = $this->etpService->getByPrefeituraId($prefeituraId, $filters);

        return view('Admin.Etps.index', compact('etps', 'filters'));
    }

    public function create()
    {
        $prefeituraId = auth()->user()->prefeitura_id;

        $secretarias = Unidade::where('prefeitura_id', $prefeituraId)->orderBy('nome', 'asc')->get();

        $itens = $this->etpItemService->getAllForSelect();

        return view('Admin.Etps.create', compact('secretarias', 'itens'));
    }

    public function store(Request $request)
    {
        // Validação base
        $rules = [
            'secretaria_id'       => 'required|exists:unidades,id',
            'servidor_responsavel' => 'required|string|max:255',
            'objeto_licitacao'    => 'required|string',
            'justificativa_necessidade' => 'required|string',
            'modalidade'          => 'required|in:pregao,concorrencia,dispensa,inexigibilidade',
            'tipo_contratacao'    => 'required_if:modalidade,pregao,dispensa|in:item,lote,servicos,compras,obras',
            'dotacao_orcamentaria' => 'required|string|max:255',
            'prazo_entrega'       => 'required|string|max:255',
            'cotacao_path'        => 'nullable|file|max:10240',
            'action_type'         => 'nullable|in:salvar,concluir',
            'should_redirect'     => 'nullable|in:0,1',
        ];

        // Validação para itens (sem lote)
        $rules['itens']                   = 'required_if:tipo_contratacao,item|array';
        $rules['itens.*.item_id']         = 'required_if:tipo_contratacao,item|exists:etp_itens,id';
        $rules['itens.*.unidade']         = 'required_if:tipo_contratacao,item|string|max:100';
        $rules['itens.*.quantidade']      = 'required_if:tipo_contratacao,item|integer|min:1';

        // Validação para lotes
        $rules['lotes']                       = 'required_if:tipo_contratacao,lote|array';
        $rules['lotes.*.nome']                = 'required_if:tipo_contratacao,lote|string|max:255';
        $rules['lotes.*.itens']               = 'required_if:tipo_contratacao,lote|array';
        $rules['lotes.*.itens.*.item_id']     = 'required_if:tipo_contratacao,lote|exists:etp_itens,id';
        $rules['lotes.*.itens.*.unidade']     = 'required_if:tipo_contratacao,lote|string|max:100';
        $rules['lotes.*.itens.*.quantidade']  = 'required_if:tipo_contratacao,lote|integer|min:1';

        $request->validate($rules);

        $data = $request->all();
        $data['prefeitura_id'] = auth()->user()->prefeitura_id;

        // Define status conforme o botão clicado:
        //   "Salvar"   → pendente  (rascunho, não enviado para análise)
        //   "Concluir" → em_analise (envia para análise do admin)
        $data['status'] = ($request->input('action_type') === 'concluir')
            ? 'em_analise'
            : 'pendente';

        try {
            $etp = $this->etpService->store($data, $request->file('cotacao_path'));

            $mensagem = $data['status'] === 'em_analise'
                ? 'ETP concluído e enviado para análise com sucesso.'
                : 'ETP salvo como rascunho. Você pode editá-lo a qualquer momento.';

            // Verifica se é uma requisição AJAX (salvar rascunho sem redirecionar)
            if ($request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0') {
                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'etp_id' => $etp->id,
                    'status' => $data['status']
                ]);
            }

            // Redirecionamento normal para conclusão
            return redirect()
                ->route('admin.etps.index')
                ->with('success', $mensagem);
                
        } catch (\Exception $e) {
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
        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            abort(403, 'Acesso negado.');
        }

        return view('Admin.Etps.show', compact('etp'));
    }

    public function edit($id)
    {
        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            abort(403, 'Acesso negado.');
        }

        if ($etp->status !== 'pendente') {
            return redirect()
                ->route('admin.etps.show', $id)
                ->with('error', 'Apenas ETPs com status "pendente" podem ser editados.');
        }

        $prefeituraId = auth()->user()->prefeitura_id;
        $secretarias  = Unidade::where('prefeitura_id', $prefeituraId)->orderBy('nome', 'asc')->get();
        $itens        = $this->etpItemService->getAllForSelect();

        return view('Admin.Etps.edit', compact('etp', 'secretarias', 'itens'));
    }

    public function update(Request $request, $id)
    {
        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            abort(403, 'Acesso negado.');
        }

        if ($etp->status !== 'pendente') {
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
            'dotacao_orcamentaria' => 'required|string|max:255',
            'prazo_entrega'       => 'required|string|max:255',
            'cotacao_path'        => 'nullable|file|max:10240',
            'action_type'         => 'nullable|in:salvar,concluir',
            'should_redirect'     => 'nullable|in:0,1',
        ];

        $rules['itens']                   = 'required_if:tipo_contratacao,item|array';
        $rules['itens.*.item_id']         = 'required_if:tipo_contratacao,item|exists:etp_itens,id';
        $rules['itens.*.unidade']         = 'required_if:tipo_contratacao,item|string|max:100';
        $rules['itens.*.quantidade']      = 'required_if:tipo_contratacao,item|integer|min:1';

        $rules['lotes']                       = 'required_if:tipo_contratacao,lote|array';
        $rules['lotes.*.nome']                = 'required_if:tipo_contratacao,lote|string|max:255';
        $rules['lotes.*.itens']               = 'required_if:tipo_contratacao,lote|array';
        $rules['lotes.*.itens.*.item_id']     = 'required_if:tipo_contratacao,lote|exists:etp_itens,id';
        $rules['lotes.*.itens.*.unidade']     = 'required_if:tipo_contratacao,lote|string|max:100';
        $rules['lotes.*.itens.*.quantidade']  = 'required_if:tipo_contratacao,lote|integer|min:1';

        $request->validate($rules);

        $data = $request->all();

        // Define status conforme o botão clicado
        $data['status'] = ($request->input('action_type') === 'concluir')
            ? 'em_analise'
            : 'pendente';

        try {
            $etp = $this->etpService->update($id, $data, $request->file('cotacao_path'));

            $mensagem = $data['status'] === 'em_analise'
                ? 'ETP concluído e enviado para análise com sucesso.'
                : 'ETP salvo como rascunho com sucesso.';

            // Verifica se é uma requisição AJAX (salvar rascunho sem redirecionar)
            if ($request->ajax() || $request->wantsJson() || $request->input('should_redirect') == '0') {
                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'etp_id' => $etp->id,
                    'status' => $data['status']
                ]);
            }

            // Redirecionamento normal para conclusão
            return redirect()
                ->route('admin.etps.show', $id)
                ->with('success', $mensagem);
                
        } catch (\Exception $e) {
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
        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            abort(403, 'Acesso negado.');
        }

        if ($etp->status !== 'pendente') {
            return redirect()
                ->route('admin.etps.show', $id)
                ->with('error', 'Apenas ETPs com status "pendente" podem ser excluídos.');
        }

        try {
            $this->etpService->delete($id);

            return redirect()
                ->route('admin.etps.index')
                ->with('success', 'ETP excluído com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao excluir ETP: ' . $e->getMessage());
        }
    }

    public function criarItemRapido(Request $request)
    {
        $request->validate([
            'descricao_item' => 'required|string|max:500',
        ]);

        try {
            // Usa firstOrCreate para evitar duplicatas idênticas (case-insensitive)
            $item = EtpItem::whereRaw('LOWER(descricao_item) = ?', [strtolower(trim($request->descricao_item))])
                ->first();

            if ($item) {
                return response()->json([
                    'success' => true,
                    'item'    => $item,
                    'message' => 'Item já existente retornado.',
                ]);
            }

            $item = EtpItem::create([
                'descricao_item' => trim($request->descricao_item),
            ]);

            return response()->json([
                'success' => true,
                'item'    => $item,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importarItensEtp(Request $request)
    {
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
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $arquivo     = $request->file('arquivo_excel');
            $spreadsheet = IOFactory::load($arquivo->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            if (count($rows) < 2) {
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
                    continue;
                }

                if (!is_numeric($quantidade) || (int)$quantidade <= 0) {
                    $erros[] = "Linha {$linhaExcel}: quantidade inválida.";
                    continue;
                }

                $item = EtpItem::whereRaw('LOWER(descricao_item) = ?', [strtolower($descricao)])->first();
                if (!$item) {
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

            return response()->json([
                'success' => true,
                'itens'   => $itensImportados,
                'erros'   => $erros
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao importar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerarPdf($id)
    {
        $etp = $this->etpService->findById($id);

        if ($etp->prefeitura_id !== auth()->user()->prefeitura_id) {
            abort(403, 'Acesso negado.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('Admin.Etps.pdf.solicitacao', compact('etp'))
            ->setPaper('a4', 'portrait');

        $filename = 'ETP-' . str_pad($etp->id, 4, '0', STR_PAD_LEFT) . '_' . $etp->created_at->format('Y') . '_solicitacao.pdf';

        return $pdf->download($filename);
    }

    public function exportItens($id)
    {
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

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}