<?php

namespace App\Http\Controllers;

use App\Models\DfeDocumento;
use App\Models\DfeEvento;
use App\Models\Empresa;
use App\Services\DanfeService;
use App\Services\DfeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DfeController extends Controller
{
    protected DfeService $dfeService;
    protected DanfeService $danfeService;

    public function __construct(DfeService $dfeService, DanfeService $danfeService)
    {
        $this->dfeService = $dfeService;
        $this->danfeService = $danfeService;
    }

    /**
     * Retorna o ID da empresa ativa para o usuário autenticado.
     */
    protected function getEmpresaId(): int
    {
        $usuario = Auth::user();
        $empresaSelecionada = session('empresa_id');
        return (int) ($usuario?->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : ($usuario?->empresa_id ?: 1));
    }

    /**
     * Retorna o model Empresa correspondente à empresa ativa.
     */
    protected function getEmpresaAtiva(): Empresa
    {
        return Empresa::with('preferencia')->findOrFail($this->getEmpresaId());
    }

    /**
     * Lista a Caixa de Entrada de DF-e recebidos da SEFAZ.
     */
    public function index(Request $request): View|JsonResponse
    {
        $empresa = $this->getEmpresaAtiva();
        $empresaId = $empresa->id;

        $busca = trim((string) $request->input('busca', ''));
        $manifestacao = $request->input('manifestacao');
        $comXml = $request->input('com_xml', '1'); // Padrão: 1 (Apenas notas com XML completo)
        $importado = $request->input('importado');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        $query = DfeDocumento::query()
            ->where('empresa_id', $empresaId)
            ->when($busca !== '', function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->where('chave', 'like', "%{$busca}%")
                        ->orWhere('numero_nota', 'like', "%{$busca}%")
                        ->orWhere('serie', 'like', "%{$busca}%")
                        ->orWhere('nome_emitente', 'like', "%{$busca}%")
                        ->orWhere('cnpj_emitente', 'like', "%{$busca}%")
                        ->orWhere('nsu', 'like', "%{$busca}%");
                });
            })
            ->when(!empty($manifestacao), function ($q) use ($manifestacao) {
                $q->where('situacao_manifestacao', $manifestacao);
            })
            ->when($comXml === '1', function ($q) {
                $q->whereNotNull('xml')
                  ->where('xml', '!=', '')
                  ->whereIn('schema', ['procNFe', 'nfeProc']);
            })
            ->when($comXml === '0', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('xml')
                        ->orWhere('xml', '')
                        ->orWhere('schema', 'resNFe');
                });
            })
            ->when($importado !== null && $importado !== '', function ($q) use ($importado) {
                $q->where('importado_entrada', (bool) $importado);
            })
            ->when(!empty($dataInicio), function ($q) use ($dataInicio) {
                $q->whereDate('data_emissao', '>=', $dataInicio);
            })
            ->when(!empty($dataFim), function ($q) use ($dataFim) {
                $q->whereDate('data_emissao', '<=', $dataFim);
            })
            ->orderByDesc('data_emissao')
            ->orderByDesc('id');

        if ($request->wantsJson()) {
            return response()->json($query->paginate(50));
        }

        $documentos = $query->paginate(20)->withQueryString();

        // Totais para cards informativos do topo
        $totaisBase = DfeDocumento::where('empresa_id', $empresaId);
        $totalGeral = (clone $totaisBase)->count();
        $totalSemManifestacao = (clone $totaisBase)->where('situacao_manifestacao', 'sem_manifestacao')->count();
        $totalComXml = (clone $totaisBase)->whereNotNull('xml')->where('xml', '!=', '')->whereIn('schema', ['procNFe', 'nfeProc'])->count();
        $totalPendentesImportacao = (clone $totaisBase)->whereNotNull('xml')->where('xml', '!=', '')->whereIn('schema', ['procNFe', 'nfeProc'])->where('importado_entrada', false)->count();

        $preferencia = $empresa->preferencia;

        return view('dfe.index', compact(
            'empresa',
            'preferencia',
            'documentos',
            'totalGeral',
            'totalSemManifestacao',
            'totalComXml',
            'totalPendentesImportacao',
            'comXml'
        ));
    }

    /**
     * Executa a sincronização manual de DF-e junto à SEFAZ.
     */
    public function sincronizar(Request $request): RedirectResponse|JsonResponse
    {
        $empresa = $this->getEmpresaAtiva();
        $force = $request->boolean('force', false);

        $resultado = $this->dfeService->sincronizarLote($empresa, $force);

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        if (!empty($resultado['bloqueado'])) {
            return redirect()->back()->with('warning', $resultado['mensagem'] ?? 'Aguarde o intervalo da SEFAZ para nova consulta.');
        }

        if (!$resultado['sucesso']) {
            return redirect()->back()->with('error', $resultado['erro'] ?? 'Erro ao consultar SEFAZ.');
        }

        $cStat = $resultado['cStat'] ?? '';
        if ($cStat === '137') {
            return redirect()->back()->with('info', 'SEFAZ consultada: Nenhum novo documento localizado para o NSU atual.');
        }

        $qtd = $resultado['qtdProcessados'] ?? 0;
        return redirect()->back()->with('success', "SEFAZ consultada com sucesso! {$qtd} documento(s) sincronizado(s).");
    }

    /**
     * Registra evento de Manifestação do Destinatário para uma nota.
     */
    public function manifestar(Request $request, string $chave): RedirectResponse|JsonResponse
    {
        $request->validate([
            'evento'        => 'required|in:210210,210200,210220,210240',
            'justificativa' => 'nullable|string|min:15|max:255',
        ], [
            'evento.required'       => 'O tipo de evento é obrigatório.',
            'justificativa.min'     => 'A justificativa para Não Realização deve ter no mínimo 15 caracteres.',
        ]);

        $empresa = $this->getEmpresaAtiva();
        $evento = (string) $request->input('evento');
        $justificativa = (string) $request->input('justificativa', '');

        $resultado = $this->dfeService->manifestar($empresa, $chave, $evento, $justificativa, Auth::id());

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        if (!$resultado['sucesso']) {
            return redirect()->back()->with('error', $resultado['erro'] ?? 'Erro ao manifestar na SEFAZ.');
        }

        return redirect()->back()->with('success', 'Manifestação registrada na SEFAZ com sucesso! Status: ' . ($resultado['mensagem'] ?? 'OK'));
    }

    /**
     * Força a consulta direta de uma chave específica na SEFAZ para buscar o XML.
     * Registra automaticamente a Ciência da Emissão se ainda não manifestada.
     */
    public function consultarChave(string $chave): RedirectResponse|JsonResponse
    {
        $empresa = $this->getEmpresaAtiva();

        $doc = DfeDocumento::where('empresa_id', $empresa->id)->where('chave', $chave)->first();

        // Automação: Se ainda não deu ciência ou confirmação, manifesta Ciência da Emissão automaticamente
        if ($doc && !in_array($doc->situacao_manifestacao, ['ciencia', 'confirmada'])) {
            $this->dfeService->manifestar($empresa, $chave, DfeService::EVENTO_CIENCIA, '', Auth::id());
            sleep(1); // Breve pausa para propagação na SEFAZ
        }

        $resultado = $this->dfeService->consultarPorChave($empresa, $chave);

        if (request()->wantsJson()) {
            return response()->json($resultado);
        }

        if (!$resultado['sucesso']) {
            return redirect()->back()->with('info', 'Ciência da Emissão registrada na SEFAZ. O XML completo estará disponível para download/entrada em instantes.');
        }

        return redirect()->back()->with('success', 'Ciência registrada e XML completo obtido com sucesso da SEFAZ!');
    }

    /**
     * Download do arquivo .xml da nota fiscal.
     * Se o XML ainda não foi baixado, tenta manifestar ciência e obter o XML da SEFAZ automaticamente.
     * Ao baixar, registra Ciência da Emissão automaticamente se ainda não manifestada.
     */
    public function downloadXml(string $chave): StreamedResponse|RedirectResponse
    {
        $empresa = $this->getEmpresaAtiva();
        $empresaId = $empresa->id;

        $documento = DfeDocumento::where('empresa_id', $empresaId)
            ->where('chave', $chave)
            ->first();

        // Automação: Ao baixar o arquivo, registra Ciência da Emissão se ainda não manifestada
        if ($documento && !in_array($documento->situacao_manifestacao, ['ciencia', 'confirmada'])) {
            try {
                $this->dfeService->manifestar($empresa, $chave, DfeService::EVENTO_CIENCIA, '', Auth::id());
                $documento->refresh();
            } catch (\Throwable $e) {
                // Log e segue com o fluxo
            }
        }

        if (!$documento || empty($documento->xml)) {
            // Tenta obter o XML da SEFAZ
            $this->dfeService->consultarPorChave($empresa, $chave);

            $documento = DfeDocumento::where('empresa_id', $empresaId)
                ->where('chave', $chave)
                ->whereNotNull('xml')
                ->first();

            if (!$documento || empty($documento->xml)) {
                return redirect()->back()->with('info', 'Ciência da Emissão transmitida à SEFAZ. O XML completo estará liberado para download em instantes.');
            }
        }

        $filename = "NFe_{$chave}.xml";

        return response()->streamDownload(function () use ($documento) {
            echo $documento->xml;
        }, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Gera e exibe o DANFE (PDF) de uma nota fiscal do DF-e no navegador.
     */
    public function danfe(int $id): Response|RedirectResponse
    {
        $empresa = $this->getEmpresaAtiva();

        $documento = DfeDocumento::where('empresa_id', $empresa->id)->findOrFail($id);

        // Se o XML não estiver disponível, tenta manifestar ciência e consultar na SEFAZ
        if (empty($documento->xml)) {
            try {
                if (!in_array($documento->situacao_manifestacao, ['ciencia', 'confirmada'])) {
                    $this->dfeService->manifestar($empresa, $documento->chave, DfeService::EVENTO_CIENCIA, '', Auth::id());
                    sleep(1);
                }
                $this->dfeService->consultarPorChave($empresa, $documento->chave);
                $documento->refresh();
            } catch (\Throwable $e) {
                // segue para a checagem abaixo
            }
        }

        if (empty($documento->xml)) {
            return redirect()->back()->with('warning', 'O XML desta nota ainda não está disponível na SEFAZ para emissão do DANFE. Foi registrada a Ciência da Emissão; tente novamente em instantes.');
        }

        try {
            $isCancelada = ($documento->situacao_nfe == 2);
            $pdfBytes = $this->danfeService->gerarPdf($documento->xml, null, $isCancelada);

            $filename = "DANFE_{$documento->chave}.pdf";

            return response($pdfBytes, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
                'Cache-Control'       => 'private, max-age=0, must-revalidate',
                'Pragma'              => 'public',
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao gerar o DANFE em PDF: ' . $e->getMessage());
        }
    }

    /**
     * Retorna o histórico de eventos da nota fiscal em JSON para exibição em modal/timeline.
     */
    public function eventos(string $chave): JsonResponse
    {
        $empresa = $this->getEmpresaAtiva();

        $documento = DfeDocumento::where('empresa_id', $empresa->id)
            ->where('chave', $chave)
            ->first();

        $eventos = DfeEvento::with('usuario:id,name')
            ->where('empresa_id', $empresa->id)
            ->where('chave', $chave)
            ->orderByDesc('data_evento')
            ->orderByDesc('id')
            ->get()
            ->map(function ($ev) {
                return [
                    'id'            => $ev->id,
                    'tipo_evento'   => $ev->tipo_evento,
                    'nome_evento'   => $ev->nome_evento_formatado,
                    'badge_classe'  => $ev->badge_classe,
                    'sequencia'     => $ev->sequencia_evento,
                    'protocolo'     => $ev->protocolo ?: '-',
                    'data_evento'   => $ev->data_evento ? $ev->data_evento->format('d/m/Y H:i:s') : ($ev->created_at ? $ev->created_at->format('d/m/Y H:i:s') : '-'),
                    'motivo'        => $ev->motivo ?: '-',
                    'justificativa' => $ev->justificativa ?: null,
                    'detalhes'      => $ev->detalhes,
                    'usuario'       => $ev->usuario?->name ?? 'SEFAZ / Sistema',
                ];
            });

        return response()->json([
            'sucesso'   => true,
            'chave'     => $chave,
            'numero'    => $documento?->numero_nota,
            'serie'     => $documento?->serie,
            'emitente'  => $documento?->nome_emitente,
            'total'     => $documento?->valor_total_formatado,
            'eventos'   => $eventos,
        ]);
    }
}
