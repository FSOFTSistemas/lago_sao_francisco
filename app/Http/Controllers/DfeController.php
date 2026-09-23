<?php

namespace App\Http\Controllers;

use App\Models\DfeDocumento;
use App\Models\Empresa;
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

    public function __construct(DfeService $dfeService)
    {
        $this->dfeService = $dfeService;
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
        $comXml = $request->input('com_xml');
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
                $q->whereNotNull('xml')->where('xml', '!=', '');
            })
            ->when($comXml === '0', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('xml')->orWhere('xml', '');
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
        $totalComXml = (clone $totaisBase)->whereNotNull('xml')->where('xml', '!=', '')->count();
        $totalPendentesImportacao = (clone $totaisBase)->whereNotNull('xml')->where('xml', '!=', '')->where('importado_entrada', false)->count();

        $preferencia = $empresa->preferencia;

        return view('dfe.index', compact(
            'empresa',
            'preferencia',
            'documentos',
            'totalGeral',
            'totalSemManifestacao',
            'totalComXml',
            'totalPendentesImportacao'
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

        $resultado = $this->dfeService->manifestar($empresa, $chave, $evento, $justificativa);

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
     */
    public function consultarChave(string $chave): RedirectResponse|JsonResponse
    {
        $empresa = $this->getEmpresaAtiva();
        $resultado = $this->dfeService->consultarPorChave($empresa, $chave);

        if (request()->wantsJson()) {
            return response()->json($resultado);
        }

        if (!$resultado['sucesso']) {
            return redirect()->back()->with('info', $resultado['mensagem'] ?? $resultado['erro'] ?? 'XML ainda não disponibilizado.');
        }

        return redirect()->back()->with('success', 'XML obtido com sucesso da SEFAZ!');
    }

    /**
     * Download do arquivo .xml da nota fiscal.
     */
    public function downloadXml(string $chave): StreamedResponse|RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $documento = DfeDocumento::where('empresa_id', $empresaId)
            ->where('chave', $chave)
            ->whereNotNull('xml')
            ->first();

        if (!$documento || empty($documento->xml)) {
            return redirect()->back()->with('error', 'O XML completo desta nota ainda não está disponível no sistema.');
        }

        $filename = "NFe_{$chave}.xml";

        return response()->streamDownload(function () use ($documento) {
            echo $documento->xml;
        }, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
