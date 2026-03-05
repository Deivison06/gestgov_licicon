<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EtpItemService;

class EtpItemController extends Controller
{
    protected $etpItemService;

    public function __construct(EtpItemService $etpItemService)
    {
        $this->etpItemService = $etpItemService;
    }

    public function index(Request $request)
    {
        $descricao = $request->get('descricao');

        $itens = $this->etpItemService->getAllPaged(15, $descricao);

        return view('Admin.EtpItens.index', compact('itens', 'descricao'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'descricao_item' => 'required|string'
        ]);

        try {

            $item = $this->etpItemService->store($request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item criado com sucesso.',
                    'item' => $item
                ]);
            }

            return redirect()->route('admin.etp_itens.index')
                ->with('success', 'Item criado com sucesso.');

        } catch (\Exception $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'descricao_item' => 'required|string'
        ]);

        try {

            $item = $this->etpItemService->update($id, $request->all());

            // Se for requisição AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item atualizado com sucesso.',
                    'item' => $item
                ]);
            }

            // Requisição normal
            return redirect()
                ->route('admin.etp_itens.index')
                ->with('success', 'Item atualizado com sucesso.');

        } catch (\Exception $e) {

            \Log::error('Erro ao atualizar item ETP', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar item.'
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function destroy($id)
    {
         try {
             $this->etpItemService->delete($id);
             return redirect()->route('admin.etp_itens.index')->with('success', 'Item excluído com sucesso.');
         } catch (\Exception $e) {
             return redirect()->back()->with('error', $e->getMessage());
         }
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'arquivo_excel' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $count = $this->etpItemService->importarExcel($request->file('arquivo_excel'));
            return redirect()->route('admin.etp_itens.index')->with('success', "Importação concluída. $count itens foram adicionados ao catálogo.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
