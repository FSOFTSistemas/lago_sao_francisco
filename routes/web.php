<?php

use App\Http\Controllers\AdiantamentoController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContaCorrenteController;
use App\Http\Controllers\ContasAPagarController;
use App\Http\Controllers\ContasAReceberController;
use App\Http\Controllers\DiariaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\EspacoController;
use App\Http\Controllers\FluxoCaixaController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\PlanoDeContaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\VendaItemController;
use App\Models\Diaria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::resource('contasAPagar', ContasAPagarController::class)->middleware('permission:gerenciar contas a pagar');

Route::get('endereco/{cep}', [EnderecoController::class, 'buscarEnderecoPorCep'])->name('buscarCep');

Route::resource('endereco', EnderecoController::class);

Route::resource('funcionario', FuncionarioController::class)->middleware('permission:gerenciar funcionario');

Route::resource('adiantamento', AdiantamentoController::class)->middleware('permission:gerenciar adiantamento');

Route::resource('caixa', CaixaController::class)->middleware('permission:gerenciar caixa');

Route::resource('contaCorrente', ContaCorrenteController::class)->middleware('permission:gerenciar conta corrente');

Route::resource('cliente', ClienteController::class)->middleware('permission:gerenciar cliente');

Route::resource('fluxoCaixa', FluxoCaixaController::class)->middleware('permission:gerenciar fluxo de caixa');

Route::resource('contasAReceber', ContasAReceberController::class)->middleware('permission:gerenciar contas a receber');

Route::resource('venda', VendaController::class);

Route::resource('produto', ProdutoController::class)->middleware('permission:gerenciar produto');

Route::resource('vendaItem', VendaItemController::class);

Route::resource('formaPagamento', FormaPagamentoController::class);

Route::get('/preferencias', [EmpresaController::class, 'preferencias'])->name('preferencias');

Route::resource('espaco', EspacoController::class); //->middleware('permission:gerenciar espaco')

Route::resource('diaria', DiariaController::class); //->middleware('permission:gerenciar diaria')

