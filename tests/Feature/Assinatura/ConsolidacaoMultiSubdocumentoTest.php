<?php

namespace Tests\Feature\Assinatura;

use App\Models\AssinaturaDigital;
use App\Models\Documento;
use App\Models\DocumentoSelecaoAssinantes;
use App\Models\DocumentoVersao;
use App\Models\User;
use App\Services\Assinatura\AssinaturaConsolidacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\Assinatura\Helpers\CriaProcessoMinimoTrait;
use Tests\TestCase;

/**
 * Documentos que reúnem subdocumentos com assinantes diferentes (ex.: estudo_tecnico
 * = ETP + Mapa de Riscos) devem gerar UMA página de assinatura por subdocumento,
 * cada uma com o seu respectivo assinante (mapeamento posicional pela seleção).
 */
class ConsolidacaoMultiSubdocumentoTest extends TestCase
{
    use RefreshDatabase;
    use CriaProcessoMinimoTrait;

    private AssinaturaConsolidacaoService $service;
    private array $arquivosTemporarios = [];

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'assinante', 'guard_name' => 'web']);
        $this->service = app(AssinaturaConsolidacaoService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->arquivosTemporarios as $arq) {
            @unlink($arq);
            @unlink(preg_replace('/\.pdf$/i', '_assinado.pdf', $arq));
        }
        parent::tearDown();
    }

    public function test_estudo_tecnico_gera_uma_pagina_de_assinatura_por_subdocumento(): void
    {
        $processoId = $this->criarProcessoMinimo();
        $rascunho   = $this->criarPdfFake(); // 1 página
        $this->arquivosTemporarios[] = $rascunho;

        $docId = \DB::table('documentos')->insertGetId(
            $this->placeholdersParaNotNull('documentos', [
                'processo_id'    => $processoId,
                'tipo_documento' => 'estudo_tecnico',
                'caminho'        => 'uploads/x.pdf',
            ])
        );

        $versao = DocumentoVersao::factory()->create([
            'documentavel_type' => Documento::class,
            'documentavel_id'   => $docId,
            'caminho_pdf'       => $rascunho,
            'hash_sha256'       => hash_file('sha256', $rascunho),
        ]);

        $assinante0 = User::factory()->assinante()->create(['name' => 'Ailson Teste Vasconcelos']);
        $assinante1 = User::factory()->assinante()->create(['name' => 'Fabio Teste Brito']);

        AssinaturaDigital::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $assinante0->id,
            'metadados'           => ['nome' => $assinante0->name, 'cargo' => 'Resp. ETP'],
        ]);
        AssinaturaDigital::factory()->create([
            'documento_versao_id' => $versao->id,
            'assinante_user_id'   => $assinante1->id,
            'metadados'           => ['nome' => $assinante1->name, 'cargo' => 'Resp. Riscos'],
        ]);

        // Seleção salva (ordem = mapeamento posicional dos subdocumentos)
        DocumentoSelecaoAssinantes::create([
            'processo_id'            => $processoId,
            'tipo_documento'         => 'estudo_tecnico',
            'homologacao_id'         => null,
            'vencedor_id'            => null,
            'modo'                   => 'paralelo',
            'prazo_dias'             => 7,
            'assinantes'             => [
                ['responsavel' => $assinante0->name, 'unidade_nome' => 'Secretaria A'],
                ['responsavel' => $assinante1->name, 'unidade_nome' => 'Secretaria B'],
            ],
            'atualizado_por_user_id' => $assinante0->id,
        ]);

        $caminho = $this->service->consolidar($versao);
        $this->arquivosTemporarios[] = $caminho;

        // 1 página do rascunho + 2 páginas de assinatura (uma por subdocumento)
        $this->assertSame(
            $this->contarPaginas($rascunho) + 2,
            $this->contarPaginas($caminho),
            'Deveria haver uma página de assinatura por subdocumento (ETP e Mapa de Riscos).'
        );

        // Conteúdo (quando pdftotext disponível): títulos e assinantes corretos
        $texto = $this->extrairTexto($caminho);
        if ($texto !== null) {
            $this->assertStringContainsString('INSTRUMENTOS DE PLANEJAMENTO', $texto);
            $this->assertStringContainsString('MAPA DE GERENCIAMENTO DE RISCOS', $texto);
            $this->assertStringContainsString(strtoupper($assinante0->name), $texto);
            $this->assertStringContainsString(strtoupper($assinante1->name), $texto);
        }
    }

    public function test_documento_comum_continua_com_pagina_unica(): void
    {
        $processoId = $this->criarProcessoMinimo();
        $rascunho   = $this->criarPdfFake();
        $this->arquivosTemporarios[] = $rascunho;

        // tipo_documento SEM config de subdocumentos
        $docId = \DB::table('documentos')->insertGetId(
            $this->placeholdersParaNotNull('documentos', [
                'processo_id'    => $processoId,
                'tipo_documento' => 'formalizacao',
                'caminho'        => 'uploads/x.pdf',
            ])
        );

        $versao = DocumentoVersao::factory()->create([
            'documentavel_type' => Documento::class,
            'documentavel_id'   => $docId,
            'caminho_pdf'       => $rascunho,
            'hash_sha256'       => hash_file('sha256', $rascunho),
        ]);
        AssinaturaDigital::factory()->count(2)->create(['documento_versao_id' => $versao->id]);

        $caminho = $this->service->consolidar($versao);
        $this->arquivosTemporarios[] = $caminho;

        // rascunho + 1 página única (mesmo com 2 assinaturas)
        $this->assertSame(
            $this->contarPaginas($rascunho) + 1,
            $this->contarPaginas($caminho)
        );
    }

    private function contarPaginas(string $caminho): int
    {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        return $pdf->setSourceFile($caminho);
    }

    private function extrairTexto(string $caminho): ?string
    {
        if (trim((string) @shell_exec('command -v pdftotext')) === '') {
            return null;
        }
        return (string) @shell_exec('pdftotext ' . escapeshellarg($caminho) . ' - 2>/dev/null');
    }
}
