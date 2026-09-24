<?php

namespace App\Http\Controllers;

use App\Models\AlmoxarifadoCategoria;
use App\Models\AlmoxarifadoItem;
use App\Models\CategoriaProduto;
use App\Models\DfeDocumento;
use App\Models\Empresa;
use App\Models\Entrada;
use App\Models\PlanoDeConta;
use App\Models\Produto;
use App\Services\DanfeService;
use App\Services\EntradaXmlService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntradaController extends Controller
{
    protected EntradaXmlService $entradaXmlService;
    protected DanfeService $danfeService;

    public function __construct(EntradaXmlService $entradaXmlService, DanfeService $danfeService)
    {
        $this->entradaXmlService = $entradaXmlService;
        $this->danfeService = $danfeService;
    }

    /**
     * Retorna o ID da empresa ativa.
     */
    protected function getEmpresaId(): int
    {
        $usuario = Auth::user();
        $empresaSelecionada = session('empresa_id');
        return (int) ($usuario?->hasRole('Master') && $empresaSelecionada ? $empresaSelecionada : ($usuario?->empresa_id ?: 1));
    }

    /**
     * Lista as Notas Fiscais de Entrada processadas.
     */
    public function index(Request $request): View|JsonResponse
    {
        $empresaId = $this->getEmpresaId();

        $busca = trim((string) $request->input('busca', ''));
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $fornecedorId = $request->input('fornecedor_id');

        $query = Entrada::query()
            ->where('empresa_id', $empresaId)
            ->with(['fornecedor', 'usuario', 'itens'])
            ->when($busca !== '', function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->where('numero_nota', 'like', "%{$busca}%")
                        ->orWhere('chave', 'like', "%{$busca}%")
                        ->orWhereHas('fornecedor', function ($fQuery) use ($busca) {
                            $fQuery->where('razao_social', 'like', "%{$busca}%")
                                   ->orWhere('nome_fantasia', 'like', "%{$busca}%")
                                   ->orWhere('cnpj', 'like', "%{$busca}%");
                        });
                });
            })
            ->when(!empty($fornecedorId), function ($q) use ($fornecedorId) {
                $q->where('fornecedor_id', $fornecedorId);
            })
            ->when(!empty($dataInicio), function ($q) use ($dataInicio) {
                $q->whereDate('data_entrada', '>=', $dataInicio);
            })
            ->when(!empty($dataFim), function ($q) use ($dataFim) {
                $q->whereDate('data_entrada', '<=', $dataFim);
            })
            ->orderByDesc('data_entrada')
            ->orderByDesc('id');

        if ($request->wantsJson()) {
            return response()->json($query->paginate(25));
        }

        $entradas = $query->paginate(20)->withQueryString();

        // Cards informativos
        $totaisBase = Entrada::where('empresa_id', $empresaId);
        $totalNotas = (clone $totaisBase)->count();
        $valorTotalNotas = (clone $totaisBase)->sum('valor_total');
        $notasMesAtual = (clone $totaisBase)->whereMonth('data_entrada', now()->month)->whereYear('data_entrada', now()->year)->count();
        $valorMesAtual = (clone $totaisBase)->whereMonth('data_entrada', now()->month)->whereYear('data_entrada', now()->year)->sum('valor_total');

        return view('entradas.index', compact(
            'entradas',
            'totalNotas',
            'valorTotalNotas',
            'notasMesAtual',
            'valorMesAtual'
        ));
    }

    /**
     * Tela inicial para upload manual de arquivo XML.
     */
    public function create(): View
    {
        return view('entradas.create');
    }

    /**
     * Tela de conferência e De/Para de itens da nota (origem DF-e ou Upload manual).
     */
    public function conferir(Request $request, ?int $dfe_id = null): View|RedirectResponse
    {
        $empresaId = $this->getEmpresaId();
        $xmlContent = null;
        $dfeDocumento = null;

        if ($dfe_id) {
            $dfeDocumento = DfeDocumento::where('empresa_id', $empresaId)->findOrFail($dfe_id);

            if (empty($dfeDocumento->xml)) {
                return redirect()->route('dfe.index')->with('warning', 'O XML desta nota ainda não está disponível. Registre a Ciência da Emissão primeiro.');
            }

            $xmlContent = $dfeDocumento->xml;
        } elseif ($request->hasFile('xml_file')) {
            $file = $request->file('xml_file');
            if (!$file->isValid()) {
                return redirect()->back()->with('error', 'Arquivo XML inválido.');
            }
            $xmlContent = file_get_contents($file->getRealPath());
        } elseif ($request->filled('xml_raw')) {
            $xmlContent = $request->input('xml_raw');
        } else {
            return redirect()->route('entradas.create')->with('error', 'Nenhum arquivo ou documento XML foi informado.');
        }

        try {
            $dadosNota = $this->entradaXmlService->parseXml($xmlContent);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao interpretar XML: ' . $e->getMessage());
        }

        // Verifica se a nota já foi importada
        $notaExistente = Entrada::where('empresa_id', $empresaId)
            ->where('chave', $dadosNota['chave'])
            ->first();

        if ($notaExistente) {
            return redirect()->route('entradas.show', $notaExistente->id)
                ->with('warning', "Esta nota fiscal (Nº {$notaExistente->numero_nota}) já foi importada anteriormente em {$notaExistente->data_entrada_formatada}.");
        }

        // Dados auxiliares para o De/Para nos selects
        $produtos = Produto::where('empresa_id', $empresaId)->ativos()->orderBy('descricao')->get();
        $categoriasProduto = CategoriaProduto::orderBy('descricao')->get();
        $itensAlmoxarifado = AlmoxarifadoItem::where('empresa_id', $empresaId)->ativos()->orderBy('nome')->get();
        $categoriasAlmoxarifado = AlmoxarifadoCategoria::ativos()->orderBy('nome')->get();
        $planosDeContas = PlanoDeConta::query()
            ->when($empresaId, function ($query) use ($empresaId) {
                $query->where(function ($q) use ($empresaId) {
                    $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
                });
            })
            ->orderBy('descricao')
            ->get();

        return view('entradas.conferir', compact(
            'dadosNota',
            'dfeDocumento',
            'produtos',
            'categoriasProduto',
            'itensAlmoxarifado',
            'categoriasAlmoxarifado',
            'planosDeContas'
        ));
    }

    /**
     * Salva a entrada, itens, estoque/almoxarifado e contas a pagar.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'xml' => 'required|string',
        ], [
            'xml.required' => 'O XML da nota é obrigatório para processamento da entrada.',
        ]);

        $empresaId = $this->getEmpresaId();
        $usuarioId = Auth::id() ?? 1;

        try {
            $entrada = $this->entradaXmlService->processarEntrada($request->all(), $empresaId, $usuarioId);

            return redirect()->route('entradas.show', $entrada->id)
                ->with('success', "Nota fiscal Nº {$entrada->numero_nota} importada e processada com sucesso!");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Falha ao processar entrada: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma entrada consolidada.
     */
    public function show(int $id): View
    {
        $empresaId = $this->getEmpresaId();

        $entrada = Entrada::where('empresa_id', $empresaId)
            ->with(['fornecedor', 'usuario', 'itens.produto', 'itens.almoxarifadoItem', 'dfeDocumento'])
            ->findOrFail($id);

        return view('entradas.show', compact('entrada'));
    }

    /**
     * Download do arquivo XML da entrada.
     */
    public function downloadXml(int $id): StreamedResponse|RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $entrada = Entrada::where('empresa_id', $empresaId)->findOrFail($id);

        if (empty($entrada->xml)) {
            return redirect()->back()->with('error', 'XML não disponível para esta entrada.');
        }

        $filename = "NFe_{$entrada->chave}.xml";

        return response()->streamDownload(function () use ($entrada) {
            echo $entrada->xml;
        }, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Gera e exibe o DANFE (PDF) da nota fiscal de entrada no navegador.
     */
    public function danfe(int $id): Response|RedirectResponse
    {
        $empresaId = $this->getEmpresaId();

        $entrada = Entrada::where('empresa_id', $empresaId)->findOrFail($id);

        if (empty($entrada->xml)) {
            return redirect()->back()->with('error', 'O arquivo XML desta entrada não está disponível para gerar o DANFE.');
        }

        try {
            $isCancelada = ($entrada->status === 'cancelada');
            $pdfBytes = $this->danfeService->gerarPdf($entrada->xml, null, $isCancelada);

            $filename = "DANFE_{$entrada->chave}.pdf";

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
}
