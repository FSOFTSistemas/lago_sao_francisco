<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EmpresaPreferencia;
use App\Models\NotaFiscal;
use App\Models\NotaFiscalItem;
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class NotaFiscalController extends Controller
{
    /**
     * Exibe a listagem de notas fiscais da empresa.
     */
    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id ?? 1;

        $query = NotaFiscal::with(['cliente', 'itens.produto'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('termo')) {
            $termo = trim($request->input('termo'));
            $query->where(function ($q) use ($termo) {
                $q->where('numero', 'like', "%{$termo}%")
                    ->orWhere('chave', 'like', "%{$termo}%")
                    ->orWhereHas('cliente', function ($cq) use ($termo) {
                        $cq->where('nome_razao_social', 'like', "%{$termo}%")
                            ->orWhere('cpf_cnpj', 'like', "%{$termo}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data', '<=', $request->input('data_fim'));
        }

        $notas = $query->orderBy('id', 'desc')->paginate(15)->appends($request->query());

        // Contadores para os cards da dashboard
        $totalEmitidas = NotaFiscal::where('empresa_id', $empresaId)->count();
        $totalAutorizadas = NotaFiscal::where('empresa_id', $empresaId)->where('status', NotaFiscal::STATUS_AUTORIZADA)->count();
        $totalPendentes = NotaFiscal::where('empresa_id', $empresaId)->whereIn('status', [NotaFiscal::STATUS_PENDENTE, NotaFiscal::STATUS_GERADA, NotaFiscal::STATUS_ASSINADA])->count();
        $totalRejeitadas = NotaFiscal::where('empresa_id', $empresaId)->where('status', NotaFiscal::STATUS_REJEITADA)->count();

        return view('notasFiscais', compact('notas', 'totalEmitidas', 'totalAutorizadas', 'totalPendentes', 'totalRejeitadas'));
    }

    /**
     * Exibe o formulário de emissão de nova nota fiscal.
     */
    public function create()
    {
        return view('NFe.create');
    }

    /**
     * Grava a nota fiscal e seus itens no banco de dados e gera o XML correspondente.
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();
        $empresaId = (int) ($request->input('empresa_id') ?: ($usuario?->empresa_id ?: 1));
        $usuarioId = (int) ($usuario?->id ?: 1);

        // Validação dos dados essenciais recebidos
        $request->validate([
            'numero' => 'required|integer|min:1',
            'serie' => 'required|integer|min:1',
            'itens' => 'required|array|min:1',
            'cliente' => 'required',
        ]);

        $clienteId = is_array($request->input('cliente'))
            ? ($request->input('cliente')['id'] ?? null)
            : $request->input('cliente_id', $request->input('cliente'));

        if (! $clienteId) {
            return redirect()->back()->with('error', 'Selecione um cliente para emitir a nota fiscal.');
        }

        try {
            $notaFiscal = DB::transaction(function () use ($request, $empresaId, $usuarioId, $clienteId) {
                $itens = $request->input('itens', []);

                // Determina CFOP e NCM para os campos obrigatórios da tabela nota_fiscals
                $cfopCode = (string) ($request->input('cfop') ?: ($itens[0]['cfop'] ?? '5102'));
                $cfopId = DB::table('cfops')->where('cfop', $cfopCode)->value('id') ?: 1;

                $firstNcm = (string) ($itens[0]['ncm'] ?? '21069090');
                $ncmId = DB::table('ncms')->where('ncm', $firstNcm)->value('id')
                    ?: (DB::table('ncms')->where('ncm', 'like', substr($firstNcm, 0, 4).'%')->value('id') ?: 1);

                $totalProdutos = (float) ($request->input('subtotal') ?: collect($itens)->sum('subtotal'));
                $totalDesconto = (float) ($request->input('desconto') ?: collect($itens)->sum('desconto'));
                $totalNota = (float) ($request->input('total') ?: max(0, $totalProdutos - $totalDesconto));

                $baseIcms = (float) collect($itens)->sum('base_calculo');
                $vIcms = (float) collect($itens)->sum('valor_icms');

                $dataEmissao = $request->input('data_emissao') ?: now()->toDateString();
                $numero = (int) $request->input('numero');
                $serie = (int) $request->input('serie');

                $tipoNota = in_array((string) $request->input('tipo_nota'), ['0', 'entrada']) ? 0 : 1;

                // 1. Criação do cabeçalho da Nota Fiscal
                $notaFiscal = NotaFiscal::create([
                    'cliente_id' => $clienteId,
                    'ncm_id' => $ncmId,
                    'cfop_id' => $cfopId,
                    'usuario_id' => $usuarioId,
                    'empresa_id' => $empresaId,
                    'data' => $dataEmissao,
                    'chave' => $request->input('chave'),
                    'serie' => $serie,
                    'numero' => $numero,
                    'observacoes' => (string) ($request->input('observacoes') ?: ''),
                    'info_complementares' => (string) ($request->input('informacoes_complementares') ?: ''),
                    'peso_liquido' => (float) ($request->input('peso_liquido') ?: 0),
                    'peso_bruto' => (float) ($request->input('peso_bruto') ?: 0),
                    'tp_frete' => (int) ($request->input('tp_frete') ?: 9),
                    'tp_transporte' => (int) ($request->input('tp_transporte') ?: 0),
                    'tp_nota' => $tipoNota,
                    'nfe_referenciavel' => $request->input('chave_nfe_referenciada') ?: $request->input('nfe_referenciada'),
                    'total_produtos' => $totalProdutos,
                    'total_nota' => $totalNota,
                    'total_notas' => $totalNota,
                    'total_desconto' => $totalDesconto,
                    'outras_despesas' => (float) ($request->input('outras_despesas') ?: 0),
                    'base_ICMS' => $baseIcms,
                    'vICMS' => $vIcms,
                    'base_ST' => 0,
                    'v_ST' => 0,
                ]);

                // 2. Criação dos Itens da Nota Fiscal
                foreach ($itens as $item) {
                    $itemCfopCode = (string) ($item['cfop'] ?? $cfopCode);
                    $itemCfopId = DB::table('cfops')->where('cfop', $itemCfopCode)->value('id') ?: $cfopId;

                    $qtd = (int) max(1, round((float) ($item['quantidade'] ?? 1)));
                    $vUnit = (float) ($item['valor_unitario'] ?? $item['v_unitario'] ?? 0);
                    $sub = (float) ($item['subtotal'] ?? ($qtd * $vUnit));
                    $desc = (float) ($item['desconto'] ?? 0);
                    $tot = (float) ($item['total'] ?? max(0, $sub - $desc));

                    $baseItem = (float) ($item['base_calculo'] ?? $tot);
                    $vIcmsItem = (float) ($item['valor_icms'] ?? 0);

                    NotaFiscalItem::create([
                        'nota_fiscal_id' => $notaFiscal->id,
                        'produto_id' => $item['produto_id'] ?? $item['id'],
                        'quantidade' => $qtd,
                        'v_unitario' => $vUnit,
                        'desconto' => $desc,
                        'subtotal' => $sub,
                        'cst' => (string) ($item['cst'] ?? '00'),
                        'cfop_id' => $itemCfopId,
                        'csosm' => (string) ($item['csosn'] ?? $item['csosm'] ?? '102'),
                        'total' => $tot,
                        'base_ICMS' => $baseItem,
                        'vICMS' => $vIcmsItem,
                        'base_ST' => 0,
                        'v_ST' => 0,
                    ]);
                }

                // 3. Atualizar número da última nota nas preferências da empresa se for maior
                $preferencia = EmpresaPreferencia::where('empresa_id', $empresaId)->first();
                if ($preferencia && $numero > ($preferencia->numero_ultima_nota ?? 0)) {
                    $preferencia->update(['numero_ultima_nota' => $numero]);
                }

                return $notaFiscal;
            });

            // 4. Invocar o construtor de XML (NFeService)
            $nfeService = app(NFeService::class);
            $resultadoXml = $nfeService->gerarXml($notaFiscal);

            if ($resultadoXml['sucesso'] ?? false) {
                $chave = $resultadoXml['chave'];
                $xml = $resultadoXml['xml'];

                // Atualiza a chave de 44 dígitos e status no registro do banco
                $notaFiscal->update([
                    'chave' => $chave,
                    'status' => NotaFiscal::STATUS_GERADA,
                ]);

                // Salva o XML gerado no storage (storage/app/nfe/geradas/{chave}.xml)
                $dir = storage_path('app/nfe/geradas');
                if (! File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }
                file_put_contents($dir.DIRECTORY_SEPARATOR.$chave.'.xml', $xml);

                Log::info("XML da NF-e nº {$notaFiscal->numero} gerado com sucesso. Chave: {$chave}");
                $mensagem = "Nota Fiscal nº {$notaFiscal->numero} gravada e XML gerado com sucesso! Chave: {$chave}";

                if ($request->wantsJson()) {
                    return response()->json([
                        'sucesso' => true,
                        'mensagem' => $mensagem,
                        'chave' => $chave,
                        'nota_fiscal' => $notaFiscal->load('itens'),
                    ]);
                }

                return redirect()->route('nota_fiscal.index')->with('success', $mensagem);
            } else {
                $erros = implode(', ', $resultadoXml['erros'] ?? ['Erro desconhecido ao gerar XML']);
                Log::warning("Nota Fiscal nº {$notaFiscal->numero} gravada, mas o XML necessita de ajustes: {$erros}");
                $mensagemAlerta = "Nota Fiscal nº {$notaFiscal->numero} gravada, mas houve inconsistência no XML: {$erros}";

                if ($request->wantsJson()) {
                    return response()->json([
                        'sucesso' => false,
                        'mensagem' => $mensagemAlerta,
                        'erros' => $resultadoXml['erros'] ?? [],
                        'nota_fiscal' => $notaFiscal->load('itens'),
                    ], 422);
                }

                return redirect()->route('nota_fiscal.index')->with('warning', $mensagemAlerta);
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao gravar nota fiscal: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', 'Erro ao gravar nota fiscal: '.$e->getMessage());
        }
    }

    /**
     * Faz o download do XML da nota fiscal.
     */
    public function baixarXml(string $id)
    {
        $nota = NotaFiscal::findOrFail($id);

        if (! $nota->chave) {
            return redirect()->back()->with('error', 'Esta nota fiscal ainda não possui chave ou XML gerado.');
        }

        $caminhos = [
            storage_path('app/nfe/autorizadas/'.$nota->chave.'.xml'),
            storage_path('app/nfe/assinadas/'.$nota->chave.'.xml'),
            storage_path('app/nfe/geradas/'.$nota->chave.'.xml'),
            public_path('xml/'.$nota->chave.'.xml'),
        ];

        foreach ($caminhos as $caminho) {
            if (File::exists($caminho)) {
                return response()->download($caminho, "NFe-{$nota->numero}-{$nota->chave}.xml", [
                    'Content-Type' => 'application/xml',
                ]);
            }
        }

        // Se o arquivo físico não estiver no disco, gera dinamicamente a partir dos dados do banco
        $nfeService = app(NFeService::class);
        $resultado = $nfeService->gerarXml($nota);
        if ($resultado['sucesso'] ?? false) {
            return response($resultado['xml'], 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => "attachment; filename=\"NFe-{$nota->numero}-{$nota->chave}.xml\"",
            ]);
        }

        return redirect()->back()->with('error', 'Arquivo XML não encontrado para esta nota fiscal.');
    }

    /**
     * Assina digitalmente o XML da nota fiscal com o certificado A1 da empresa.
     */
    public function assinar(string $id)
    {
        $nota = NotaFiscal::with(['cliente', 'empresa', 'itens.produto'])->findOrFail($id);
        $nfeService = app(NFeService::class);
        $resultado = $nfeService->assinarNotaFiscal($nota);

        if ($resultado['sucesso'] ?? false) {
            $chave = $resultado['chave'];

            return redirect()->back()->with('success', "Nota Fiscal nº {$nota->numero} assinada digitalmente com sucesso! (Chave: {$chave})");
        }

        $erro = $resultado['erro'] ?? 'Erro desconhecido ao assinar NF-e.';

        return redirect()->back()->with('error', "Falha ao assinar NF-e: {$erro}");
    }

    /**
     * Transmite a nota fiscal para autorização na SEFAZ.
     */
    public function transmitir(string $id)
    {
        $nota = NotaFiscal::with(['cliente', 'empresa', 'itens.produto'])->findOrFail($id);
        $nfeService = app(NFeService::class);
        $resultado = $nfeService->transmitirNotaFiscal($nota);

        if ($resultado['sucesso'] ?? false) {
            $protocolo = $resultado['protocolo'] ?? '';
            $mensagem = "Nota Fiscal nº {$nota->numero} AUTORIZADA pela SEFAZ com sucesso! Protocolo: {$protocolo}";

            return redirect()->back()->with('success', $mensagem);
        }

        $erro = $resultado['erro'] ?? 'Erro desconhecido ao transmitir para a SEFAZ.';

        return redirect()->back()->with('error', $erro);
    }

    /**
     * Consulta a situação da nota fiscal na SEFAZ e sincroniza o status no banco.
     */
    public function consultarStatus(string $id)
    {
        $nota = NotaFiscal::with(['cliente', 'empresa'])->findOrFail($id);
        $nfeService = app(NFeService::class);
        $resultado = $nfeService->consultarNotaFiscal($nota);

        if ($resultado['sucesso'] ?? false) {
            $cStat = $resultado['cStat'] ?? '';
            $motivo = $resultado['xMotivo'] ?? '';
            $mensagem = "Situação na SEFAZ consultada com sucesso! [{$cStat}] {$motivo}";

            return redirect()->back()->with('success', $mensagem);
        }

        $erro = $resultado['erro'] ?? 'Erro ao consultar situação na SEFAZ.';

        return redirect()->back()->with('error', $erro);
    }

    /**
     * Verifica e retorna informações do certificado digital da empresa logada.
     */
    public function verificarCertificado()
    {
        $empresa = Empresa::find(Auth::user()->empresa_id ?? 1);
        if (! $empresa) {
            return redirect()->back()->with('error', 'Empresa não encontrada.');
        }

        $nfeService = app(NFeService::class);
        $resultado = $nfeService->verificarCertificado($empresa);

        if ($resultado['valido'] ?? false) {
            $dados = $resultado['dados'] ?? [];
            $titular = $dados['titular'] ?? 'N/A';
            $validoAte = isset($dados['valido_ate']) ? $dados['valido_ate']->format('d/m/Y H:i') : 'N/A';
            $dias = $dados['dias_restantes'] ?? 0;

            return redirect()->back()->with('success', "Certificado Digital Válido! Titular: {$titular} | Vencimento: {$validoAte} ({$dias} dias restantes).");
        }

        $erro = $resultado['erro'] ?? 'Certificado digital inválido ou não configurado.';

        return redirect()->back()->with('error', $erro);
    }

    /**
     * Gera ou regenera manualmente o XML para uma nota gravada sem chave.
     */
    public function gerarXmlManual(string $id)
    {
        $nota = NotaFiscal::with(['cliente', 'empresa', 'itens.produto'])->findOrFail($id);
        $nfeService = app(NFeService::class);
        $resultado = $nfeService->gerarXml($nota);

        if ($resultado['sucesso'] ?? false) {
            $chave = $resultado['chave'];
            $xml = $resultado['xml'];

            $nota->update([
                'chave' => $chave,
                'status' => NotaFiscal::STATUS_GERADA,
            ]);

            $dir = storage_path('app/nfe/geradas');
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }
            file_put_contents($dir.DIRECTORY_SEPARATOR.$chave.'.xml', $xml);

            return redirect()->back()->with('success', "XML gerado com sucesso! Chave: {$chave}");
        }

        $erros = implode(', ', $resultado['erros'] ?? []);

        return redirect()->back()->with('error', "Erro ao gerar XML: {$erros}");
    }

    /**
     * Exibe os detalhes de uma nota fiscal.
     */
    public function show(string $id)
    {
        $nota = NotaFiscal::with(['cliente', 'empresa', 'itens.produto', 'usuario'])->findOrFail($id);

        return view('notasFiscaisDetalhes', compact('nota'));
    }

    /**
     * Remove uma nota fiscal do banco de dados.
     */
    public function destroy(string $id)
    {
        try {
            $notaFiscal = NotaFiscal::findOrFail($id);
            $numero = $notaFiscal->numero;
            $notaFiscal->delete();

            return redirect()->route('nota_fiscal.index')->with('success', "Nota Fiscal nº {$numero} excluída com sucesso!");
        } catch (\Throwable $e) {
            Log::error('Erro ao excluir nota fiscal: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erro ao excluir nota fiscal: '.$e->getMessage());
        }
    }

    public function getEmpresaCurrent()
    {
        return Empresa::where('id', Auth::user()->empresa_id)->get();
    }
}
