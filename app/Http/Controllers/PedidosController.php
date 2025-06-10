<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Services\EmpresasService;
use App\Services\EstoquesService;
use App\Services\FaturaService;
use App\Services\ItemService;
use App\Services\NFeService;
use App\Services\PedidosService;
use App\Services\ProdutosService;
use App\Utils\FormatationUtil;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use NFePHP\Common\Exception\ValidatorException;
use NFePHP\DA\NFe\Daevento;
use NFePHP\DA\NFe\Danfe;

class PedidosController extends Controller
{
    private PedidosService $pedidoServices;
    private EmpresasService $empresaServices;
    private FaturaService $faturaServices;
    private ItemService $itemServices;
    private ProdutosService $produtoServices;
    private EstoquesService $estoqueService;

    public function __construct(PedidosService $pedidoServices, EmpresasService $empresaServices, ItemService $itemServices, FaturaService $faturaServices, ProdutosService $produtoServices, EstoquesService $estoqueService)
    {
        $this->pedidoServices = $pedidoServices;
        $this->empresaServices = $empresaServices;
        $this->itemServices = $itemServices;
        $this->faturaServices = $faturaServices;
        $this->produtoServices = $produtoServices;
        $this->estoqueService = $estoqueService;
    }

    public function imprimirCorrecao($id)
    {
        try {
            $venda = $this->pedidoServices->buscarPedido($id);
            $emitente = $this->empresaServices->buscarEmpresa($venda->empresa_id);
            $xml = file_get_contents($emitente->fantasia . '/' . date('Y') . '/' . date('m') . '/notas/CCe/' . $venda->chave . '.xml');
            $daevento = new Daevento($xml, $emitente);
            $daevento->debugMode(true);
            $pdf = $daevento->render();
            return response($pdf)->header('Content-Type', 'application/pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function inutil()
    {
        try {
            $user = Auth::user();
            return view('notas.inutilizar', ['empresa' => $user->empresa_id, 'mode' => 'nfe']);
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function inutilizar(Request $request)
    {
        try {
            $emitente = Empresa::find($request->empresa_id);
            if ($emitente == null) {
                return redirect('/inutilizar')->with('error', 'Configure o emitente');
            }
            $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int) $emitente->ambiente,
                "razaosocial" => $emitente->razao,
                "siglaUF" => $emitente->endereco->uf,
                "cnpj" => FormatationUtil::retiraPontuacoes($emitente->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $emitente->csc,
                "CSCid" => '00000' . $emitente->idCsc,
            ], $emitente);
            $result = $nfe_service->inutilizarNum($emitente->serie, $request->numI, $request->numI, $request->justificativa, $emitente->fantasia . '/' . date('Y') . '/' . date('m') . '/notas/Inutilizacoes');
            if (!isset($result['erro'])) {
                return redirect('/inutilizar')->with('success', 'Inutilização feita com sucesso');
            } else {
                return redirect('/inutilizar')->with('success', $result['data']);
            }
        } catch (ValidatorException $e) {
            return back()->with('warning', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function cartaCorrecao(Request $request)
    {
        try {
            $venda = Pedido::find($request->venda_id);
            $emitente = Empresa::find($venda->empresa_id);

            if ($emitente == null) {
                return response()->json('Configure o emitente', 404);
            }

            $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int) $emitente->ambiente,
                "razaosocial" => $emitente->razao,
                "siglaUF" => $emitente->endereco->uf,
                "cnpj" => FormatationUtil::retiraPontuacoes($emitente->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $emitente->csc,
                "CSCid" => '00000' . $emitente->idCsc,
            ], $emitente);

            $result = $nfe_service->cartaCorrecao($venda, $request->justificativa, $emitente->fantasia . '/' . date('Y') . '/' . date('m') . '/notas/CCe');
            if (!isset($result['erro'])) {
                return redirect('/venda')->with('success', 'Carta de Correção feita com sucesso');
            } else {
                return redirect('/venda')->with('warning', $result['data']['retEvento']['infEvento']['xMotivo']);
            }
        } catch (ValidatorException $e) {
            return back()->with('warning', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e->getMessage());
        }
    }

    public function cancelarNFe(Request $request)
    {
        try {
            $venda = Pedido::find($request->venda_id);
            $emitente = Empresa::find($venda->empresa_id);
            if ($emitente == null) {
                return response()->json('Configure o emitente', 404);
            }
            $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int) $emitente->ambiente,
                "razaosocial" => $emitente->razao,
                "siglaUF" => $emitente->endereco->uf,
                "cnpj" => FormatationUtil::retiraPontuacoes($emitente->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $emitente->csc,
                "CSCid" => '00000' . $emitente->idCsc,
            ], $emitente);
            $nfe = $nfe_service->cancelar($venda, $request->justificativa, $emitente->fantasia . '/' . date('Y') . '/' . date('m') . '/notas/Canceladas');
            if (!isset($nfe['erro'])) {
                $venda->status = 0;
                $venda->estado = 'Cancelado';
                $venda->total = 0;
                $venda->save();
                foreach ($venda->itens as $item) {
                    $this->estoqueService->reverseStock($item->produto_id, $item->qtde);
                }
                return redirect('/venda')->with('success', 'Nota cancelada com sucesso');
            } else {
                return redirect('/venda')->with('error', $nfe['data']['retEvento']['infEvento']['xMotivo']);
            }
        } catch (ValidatorException $e) {
            return back()->with('warning', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function imprimirCancelamento($id)
    {
        try {
            $venda = Pedido::find($id);
            $empresa = Empresa::find($venda->empresa_id);
            $xml = file_get_contents(public_path($empresa->fantasia . '/' . date_format($venda->created_at, 'Y') . '/' . date_format($venda->created_at, 'm') . '/notas/Canceladas/') . $venda->chave . '.xml');
            $dadosEmitente = Empresa::find($venda->empresa_id);
            $daevento = new Daevento($xml, $dadosEmitente->toArray());
            $daevento->debugMode(true);
            $pdf = $daevento->render();
            return response($pdf)
                ->header('Content-Type', 'application/pdf');
        } catch (Exception $e) {
            session()->flash("erro", $e->getMessage());
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function imprimir($id)
    {
        try {
            $venda = Pedido::find($id);
            $empresa = Empresa::find($venda->empresa_id);
            $xml = file_get_contents(public_path($empresa->fantasia . '/' . date_format($venda->created_at, 'Y') . '/' . date_format($venda->created_at, 'm') . '/notas/Autorizadas/') . $venda->chave . '.xml');
            $danfe = new Danfe($xml);
            $pdf = $danfe->render();
            return response($pdf)
                ->header('Content-Type', 'application/pdf');
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function enviarNFe($id)
    {
        try {
            DB::beginTransaction();
            $venda = $this->pedidoServices->buscarPedido($id);
            $empresa = $this->empresaServices->buscarEmpresa($venda->empresa_id);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int) $empresa->ambiente,
                "razaosocial" => $empresa->razao,
                "siglaUF" => $empresa->endereco->uf,
                "cnpj" => FormatationUtil::retiraPontuacoes($empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $empresa->csc,
                "CSCid" => "00000" . $empresa->idCsc,
            ], $empresa);
            if ($venda->estado->value == 'Rejeitado' || $venda->estado->value == 'Pendente') {
                $result = $nfe_service->gerarXml($venda, $empresa);
                // dd($result);
                if (!isset($result['erros_xml'])) {
                    $signed = $nfe_service->sign($result['xml']);
                    // dd($signed);
                    $resultado = $nfe_service->transmitir($signed, $result['chave'], $empresa->fantasia . '/' . date('Y') . '/' . date('m') . '/notas/Autorizadas');
                    // dd($resultado);
                    if (isset($resultado['sucesso'])) {
                        $venda->chave = $result['chave'];
                        $venda->status = 1;
                        $venda->estado = 'Autorizado';
                        $venda->numero_nfe = $result['nNf'];
                        $venda->save();
                        $empresa->update(['ultimaNFe' => $empresa->ultimaNFe + 1]);
                        if ($venda->tpNF) {
                            foreach ($venda->itens as $item) {
                                $this->estoqueService->out($item->produto_id, $item->qtde);
                            }
                        } else {
                            foreach ($venda->itens as $item) {
                                $this->estoqueService->reverseStock($item->produto_id, $item->qtde);
                            }
                        }
                        DB::commit();
                        return redirect('/vendas')->with('success', 'Nota enviada com sucesso');
                    } else {
                        $venda->status = 3;
                        $venda->estado = 'Rejeitado';
                        $venda->save();
                        DB::commit();
                        return redirect('/vendas')->with('warning', $resultado['erro']);
                    }
                } else {
                    DB::rollBack();
                    return redirect('/vendas')->with('error', $result['erros_xml']);
                }
            } else {
                DB::rollBack();
                return redirect('/vendas')->with("error", 404);
            }
        } catch (ValidatorException $e) {
            DB::rollBack();
            return back()->with('warning', $e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function edit($id)
    {
        try {
            $pedido = $this->pedidoServices->buscarPedido($id);
            return view('vendas.edit', ['pedido' => $pedido]);
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'cliente' => 'required|numeric',
                'cfop' => 'required|numeric',
                'vendaItens' => 'required',
                'info_complementares' => 'nullable'
            ]);
            $venda = $this->pedidoServices->buscarPedido($id);
            if (!$venda->chave) {
                $this->itemServices->deleteItems($venda->id);
                $subtotal = 0;
                $desconto = 0;
                foreach ($request->vendaItens as $item) {
                    $prod = $this->produtoServices->um($item['produto_id']);
                    $desconto = $desconto + $item['desconto'];
                    $subtotal = $subtotal + ($item['total']);
                }
                foreach ($request->vendaItens as $item) {
                    $prod = $this->produtoServices->um($item['produto_id']);
                    $desconto = 0;
                    $this->itemServices->create(
                        $id,
                        $prod,
                        $item['quantidade'],
                        $venda->empresa_id,
                        $item['desconto'],
                        $item['unitario']
                    );
                }
                $this->faturaServices->update(
                    $subtotal,
                    $venda->fatura[0]->id,
                );
                $this->pedidoServices->update(
                    $venda->id,
                    $request->cliente,
                    $subtotal,
                    $desconto,
                    $request->cfop,
                    $request->info_complementares
                );
                return redirect()->route('vendas.editar', [$venda->id])->with('success', 'Nota atualizada com sucesso.');
            } else {
                return redirect()->route('vendas.index')->with('warning', 'Já foi emitida a NFe desse venda, não é possível realizar alterações.');
            }
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'empresa' => 'required|numeric',
                'finalidade' => 'required|numeric',
                'tipo' => 'required|numeric',
                'ref_nfe' => $request->finalidade == 4 ? 'required' : 'nullable',
                'cliente' => 'required|numeric',
                'cfop' => 'required|numeric',
                'vendaItens' => 'required',
                'info_complementares' => 'nullable|max:255'
            ], [
                'required' => 'O campo :attribute é obrigatório!',
                'vendaItens.required' => 'Deve existir pelo menos um item no pedido!',
                'numeric' => 'O campo :attribute deve ser um valor numérico!',
                'max' => 'O campo :attribute deve conter no máximo :max caracteres'
            ]);
            DB::beginTransaction();
            $subtotal = 0;
            $desconto = 0;
            //Se ainda não atingiu o limite de Notas ou é janaina que está fazendo, permito a criação de uma nova nota, caso contrário faço o bloqueio da ação
            if ($this->pedidoServices->limiteDeNotas($request->empresa) < $this->empresaServices->buscarEmpresa($request->empresa)->limNFes || $request->empresa == 1) {
                foreach ($request->vendaItens as $item) {
                    $prod = $this->produtoServices->um($item['produto_id']);
                    $desconto = $desconto + $item['desconto'];
                    $subtotal = $subtotal + ($item['total']);
                }
                $pedido = $this->pedidoServices->create(
                    Auth::id(),
                    $request->cliente,
                    $subtotal,
                    $desconto,
                    $request->empresa,
                    $request->cfop,
                    $request->finalidade == 4 ? 4 : 1,
                    $request->ref_nfe,
                    $request->tipo,
                    $request->info_complementares
                );
                foreach ($request->vendaItens as $item) {
                    $prod = $this->produtoServices->um($item['produto_id']);
                    $this->itemServices->create(
                        $pedido->id,
                        $prod,
                        $item['quantidade'],
                        $request->empresa,
                        $item['desconto'],
                        $item['unitario']
                    );
                }
                $this->faturaServices->create(
                    $subtotal,
                    $pedido->id,
                    $pedido->finNF,
                    $request->empresa
                );
                DB::commit();
                return redirect()->route('vendas.index')->with('success', "Nota criada com sucesso");
            } else {
                DB::rollBack();
                return redirect()->route('vendas.index')->with('warning', 'Limite de notas Atingido');
            }
        } catch (ValidationException $e) {
            foreach ($e->errors() as $error) {
                $errors[] = implode(PHP_EOL, $error);
            }
            DB::rollBack();
            return back()->with('warning', implode(PHP_EOL, $errors))->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function destroyPedido(Request $request)
    {
        try {
            $this->pedidoServices->delete($request->pedido_id);
            return redirect()->route('vendas.index')->with('success', 'Nota deletada com sucesso!');
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function todos()
    {
        try {
            $pedidos = $this->pedidoServices->formatedVenda(Auth::user()->empresa_id);
            return view('vendas.todos', ['pedidos' => $pedidos, 'empresa' => Auth::user()->empresa_id]);
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function visualizar($id)
    {
        try {
            $pedido = $this->pedidoServices->buscarPedido($id);
            $empresa = $this->empresaServices->buscarEmpresa($pedido->empresa_id);
            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int) $empresa->ambiente,
                "razaosocial" => $empresa->razao,
                "siglaUF" => $empresa->endereco->uf,
                "cnpj" => FormatationUtil::retiraPontuacoes($empresa->cpf_cnpj),
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $empresa->csc,
                "CSCid" => "00000" . $empresa->idCsc,
            ], $empresa);
            $result = $nfe_service->gerarXml($pedido, $empresa);
            $danfe = new Danfe($result['xml']);
            $pdf = $danfe->render();
            return response($pdf)
                ->header('Content-Type', 'application/pdf');
        } catch (ValidatorException $e) {
            return back()->with('warning', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function new()
    {
        try {
            return view('vendas.create');
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e);
        }
    }

    public function totalMesNFe()
    {
        try {
            $results = $this->pedidoServices->getTotalNFePerMonth(Auth::user()->empresa_id);
            return response()->json($results);
        } catch (Exception $e) {
            return response()->json('error: Ocorreu um erro inesperado, tente novamente em alguns instantes!, Erro: ' . $e->getMessage(), $e->getCode());
        }
    }

}
