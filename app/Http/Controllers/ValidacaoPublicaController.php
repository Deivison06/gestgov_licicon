<?php

namespace App\Http\Controllers;

use App\Services\Assinatura\ValidacaoPublicaService;
use Illuminate\Http\Request;

/**
 * Endpoint público (sem autenticação) para validar a autenticidade de documentos
 * assinados digitalmente. Acesso via QR code do PDF ou digitando o código.
 */
class ValidacaoPublicaController extends Controller
{
    public function __construct(
        private readonly ValidacaoPublicaService $service
    ) {}

    /**
     * GET /autenticar
     * Formulário para digitar o código verificador.
     */
    public function formulario()
    {
        return view('Autenticar.formulario');
    }

    /**
     * GET /autenticar/{codigo}
     * Mostra o resultado da validação. Acessado direto via QR code.
     */
    public function consultar(Request $request, string $codigo)
    {
        $resultado = $this->service->consultar(
            $codigo,
            $request->ip(),
            substr((string) $request->userAgent(), 0, 500)
        );

        return view('Autenticar.resultado', [
            'codigo'    => strtoupper(trim($codigo)),
            'resultado' => $resultado,
        ]);
    }

    /**
     * POST /autenticar/buscar
     * Submit do formulário — redireciona para a página de resultado.
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'min:5', 'max:30'],
        ], [
            'codigo.required' => 'Informe o código verificador.',
            'codigo.min'      => 'Código muito curto.',
            'codigo.max'      => 'Código muito longo.',
        ]);

        return redirect()->route('autenticar.consultar', [
            'codigo' => strtoupper(trim($request->input('codigo'))),
        ]);
    }

    /**
     * GET /autenticar/{codigo}/download
     * Serve o PDF assinado inline.
     */
    public function download(string $codigo)
    {
        $caminho = $this->service->caminhoDownload($codigo);

        if (!$caminho) {
            abort(404, 'Documento não disponível para download.');
        }

        return response()->file($caminho, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="documento-assinado.pdf"',
        ]);
    }
}
