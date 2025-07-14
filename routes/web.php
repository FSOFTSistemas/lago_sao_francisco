<?php

use App\Http\Controllers\AdiantamentoController;
use App\Http\Controllers\AdicionalController;
use App\Http\Controllers\AluguelController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\CardapioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CategoriaParceiroController;
use App\Http\Controllers\CategoriaProdutoController;
use App\Http\Controllers\CategoriasDeItensCardapioController;
use App\Http\Controllers\CfopController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContaCorrenteController;
use App\Http\Controllers\ContasAPagarController;
use App\Http\Controllers\ContasAReceberController;
use App\Http\Controllers\DayUseController;
use App\Http\Controllers\EmpresaContadorController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EmpresaPreferenciaController;
use App\Http\Controllers\EmpresaRTController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\EspacoController;
use App\Http\Controllers\EspacoDisponibilidadeController;
use App\Http\Controllers\FluxoCaixaController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\HospedeController;
use App\Http\Controllers\ItensDoCardapioController;
use App\Http\Controllers\MapaQuartoController;
use App\Http\Controllers\MapaReservaController;
use App\Http\Controllers\NotaFiscalController;
use App\Http\Controllers\PlanoDeContaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\QuartoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\VendaItemController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\ItensDayUseController;
use App\Http\Controllers\NotaFiscalItensController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LogDayuseController;
use App\Http\Controllers\ParceiroController;
use App\Http\Controllers\PreferenciasHotelController;
use App\Http\Controllers\ReservaItemController;
use App\Http\Controllers\SouvenirController;
use App\Http\Controllers\TransacaoController;
use App\Http\Controllers\UsuarioSenhaController;
use App\Http\Controllers\VendedorController;
use App\Livewire\Dayuse\ShowDayuse;

Route::get('/', function () {
    return redirect('/login');
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/financeiro', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('financeiro');

Route::resource('planoDeConta', PlanoDeContaController::class)->middleware('permission:gerenciar plano de conta');

Route::resource('empresa', EmpresaController::class)->middleware('permission:gerenciar empresa');

Route::resource('usuarios', UserController::class)->middleware('permission:gerenciar usuarios');

Route::resource('bancos', BancoController::class)->middleware('permission:gerenciar banco');

Route::resource('fornecedor', FornecedorController::class)->middleware('permission:gerenciar fornecedor');

Route::resource('contasAPagar', ContasAPagarController::class)->middleware(['permission:gerenciar contas a pagar', 'caixa.aberto' ]);

Route::get('endereco/{cep}', [EnderecoController::class, 'buscarEnderecoPorCep'])->name('buscarCep');

Route::resource('endereco', EnderecoController::class);

Route::resource('funcionario', FuncionarioController::class)->middleware('permission:gerenciar funcionario');

Route::resource('adiantamento', AdiantamentoController::class)->middleware('permission:gerenciar adiantamento');

Route::resource('caixa', CaixaController::class)->middleware('permission:gerenciar caixa');

Route::resource('contaCorrente', ContaCorrenteController::class)->middleware('permission:gerenciar conta corrente');

Route::resource('cliente', ClienteController::class)->middleware('permission:gerenciar cliente');

Route::resource('fluxoCaixa', FluxoCaixaController::class)->middleware('permission:gerenciar caixa');

Route::resource('contasAReceber', ContasAReceberController::class)->middleware('permission:gerenciar contas a receber');

Route::resource('venda', VendaController::class);

Route::resource('produto', ProdutoController::class)->middleware('permission:gerenciar produto');

Route::resource('vendaItem', VendaItemController::class);

Route::resource('formaPagamento', FormaPagamentoController::class);

Route::get('/preferencias', [EmpresaController::class, 'preferencias'])->name('preferencias');

Route::resource('espaco', EspacoController::class); //->middleware('permission:gerenciar espaco')

Route::resource('tarifa', TarifaController::class); //->middleware('permission:gerenciar tarifa')

Route::resource('hospede', HospedeController::class);

Route::resource('reserva', ReservaController::class);

Route::resource('quarto', QuartoController::class);

Route::resource('categoria', CategoriaController::class);

Route::resource('mapaQuarto', MapaQuartoController::class);

Route::post('/filtro-empresa', function (Illuminate\Http\Request $request) {
    session(['empresa_id' => $request->empresa_id]);
    return back();
})->name('filtro.empresa');

Route::get('/quartos/disponiveis', [ReservaController::class, 'quartosDisponiveis'])->name('quartos.disponiveis');

Route::get('/mapa-reservas', [MapaReservaController::class, 'index'])->name('mapa.reservas');

Route::get('aluguel/create', [AluguelController::class, 'create'])->middleware('caixa.aberto')->name('aluguel.create');

Route::resource('aluguel', AluguelController::class)->except(['create']);

Route::resource('cardapios', CardapioController::class);

Route::resource('estoques', EstoqueController::class);

Route::resource('nota_fiscal_itens', NotaFiscalItensController::class);

Route::resource('logs', LogController::class);

Route::resource('nota_fiscal', NotaFiscalController::class);

Route::get('/cardapios/{id}/dados', [CardapioController::class, 'dados'])->name('cardapios.dados');

Route::get('/espacos/disponibilidade', [EspacoDisponibilidadeController::class, 'getDisponibilidade']);

// Responsável Técnico (RT)
Route::resource('empresaRT', EmpresaRTController::class)->middleware('permission:gerenciar empresa');

// Contador
Route::resource('empresaContador', EmpresaContadorController::class)->middleware('permission:gerenciar empresa');

// Preferências da Empresa
Route::resource('empresaPreferencia', EmpresaPreferenciaController::class)->middleware('permission:gerenciar empresa');

Route::resource('itemCardapio', ItensDoCardapioController::class);

Route::resource('categoriaItensCardapio', CategoriasDeItensCardapioController::class);

//Rota para gerar PDF de cardapio
Route::get('/cardapios/{id}/pdf', [CardapioController::class, 'verPdf'])->name('cardapios.pdf');

Route::resource('cfop', CfopController::class);

Route::resource('categoriaProduto', CategoriaProdutoController::class);

Route::resource('adicionais', AdicionalController::class);

Route::resource('vendedor', VendedorController::class)->middleware('permission:gerenciar adiantamento');

Route::get('dayuse/create', [DayUseController::class, 'create'])
->middleware('caixa.aberto')
->name('dayuse.create');

Route::resource('dayuse', DayUseController::class)->except(['create']);

Route::resource('itemDayuse', ItensDayUseController::class);

Route::get('/clientes/search', [ClienteController::class, 'search'])->name('clientes.search');

Route::get('/vendedors/search', [FuncionarioController::class, 'search'])->name('vendedors.search');

Route::get('/fornecedores/search', [FornecedorController::class, 'search'])->name('fornecedores.search');

Route::resource('parceiros', ParceiroController::class);

Route::resource('categoriasParceiro', CategoriaParceiroController::class);

Route::post('/calcular-valor', [AluguelController::class, 'calcularValor']);

Route::post('/caixas/{id}/abrir', [CaixaController::class, 'abrir'])->name('caixas.abrir');

Route::post('/caixas/{id}/fechar', [CaixaController::class, 'fechar'])->name('caixas.fechar');

Route::get('/caixas/{caixa}/resumo', [CaixaController::class, 'getResumoFechamento']);

Route::post('/dayuse/verifica-supervisor', [DayUseController::class, 'verificaSupervisor'])->name('dayuse.verificaSupervisor');

Route::resource('logsdayuse', LogDayuseController::class);

Route::get('/fluxo-caixa/pdf', [FluxoCaixaController::class, 'exportResumoPDF'])->name('fluxoCaixa.pdf');

Route::post('/contas-a-receber/receber', [ContasAReceberController::class, 'receber'])->name('contasAReceber.receber');


Route::post('contas-a-pagar/{contasAPagar}/pagar', [ContasAPagarController::class, 'pagar'])->name('contasAPagar.pagar');

Route::middleware(['auth'])->group(function () {
    Route::get('/usuario/alterar-senha', [UsuarioSenhaController::class, 'form'])->name('usuario.senha.form');
    Route::post('/usuario/alterar-senha', [UsuarioSenhaController::class, 'atualizar'])->name('usuario.senha.atualizar');
});

Route::resource('souvenir', SouvenirController::class);

Route::get('/preferencias/hotel', [PreferenciasHotelController::class, 'show'])->name('preferencias.hotel');

Route::post('/preferencias/hotel', [PreferenciasHotelController::class, 'store'])->name('preferencias.store');

Route::get('/transacoes/resumo/{reservaId}', [TransacaoController::class, 'getResumoByReserva'])->name('transacoes.resumo');

Route::get('/transacoes/reserva/{reservaId}', [TransacaoController::class, 'getByReserva'])->name('transacoes.reserva');

Route::post('/transacoes', [TransacaoController::class, 'store'])->name('transacoes.store');

Route::delete('/transacoes/{id}', [TransacaoController::class, 'destroy'])->name('transacoes.destroy');

Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');

Route::prefix('reserva-itens')->group(function () {
    Route::get('/', [ReservaItemController::class, 'index'])->name('reserva-item.index');
    Route::post('/', [ReservaItemController::class, 'store'])->name('reserva-item.store');
    Route::get('/{reservaItem}', [ReservaItemController::class, 'show'])->name('reserva-item.show');
    Route::put('/{reservaItem}', [ReservaItemController::class, 'update'])->name('reserva-item.update');
    Route::delete('/{reservaItem}', [ReservaItemController::class, 'destroy'])->name('reserva-item.destroy');
    Route::get('/reserva/{reservaId}', [ReservaItemController::class, 'getByReserva'])->name('reserva-item.by-reserva');
    Route::get('/total/{reservaId}', [ReservaItemController::class, 'getTotalByReserva'])->name('reserva-item.total');
});

Route::put('/reserva/{id}/finalizar', [ReservaController::class, 'finalizar'])->name('reserva.finalizar');

Route::put('/reserva/{id}/cancelar', [ReservaController::class, 'cancelar'])->name('reserva.cancelar');

Route::put('/reserva/{id}/hospedar', [ReservaController::class, 'hospedar'])->name('reserva.hospedar');
