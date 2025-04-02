<?php

use App\Http\Controllers\AdiantamentoController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContaCorrenteController;
use App\Http\Controllers\ContasAPagarController;
use App\Http\Controllers\ContasAReceberController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\FluxoCaixaController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\PlanoDeContaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\VendaItemController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/financeiro', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('financeiro');

Route::resource('planoDeConta', PlanoDeContaController::class);

Route::resource('empresa', EmpresaController::class);

Route::resource('usuarios', UserController::class)->middleware('permission:gerenciar usuarios');

Route::resource('bancos', BancoController::class);

Route::resource('fornecedor', FornecedorController::class);

Route::resource('contasAPagar', ContasAPagarController::class); 

Route::get('endereco/{cep}', [EnderecoController::class, 'buscarEnderecoPorCep'])->name('buscarCep');

Route::resource('endereco', EnderecoController::class);

Route::resource('funcionario', FuncionarioController::class);

Route::resource('adiantamento', AdiantamentoController::class);

Route::resource('caixa', CaixaController::class);

Route::resource('contaCorrente', ContaCorrenteController::class);

Route::resource('cliente', ClienteController::class);

Route::resource('fluxoCaixa', FluxoCaixaController::class);

Route::resource('contasAReceber', ContasAReceberController::class);

Route::resource('venda', VendaController::class);

Route::resource('produto', ProdutoController::class);

Route::resource('vendaItem', VendaItemController::class);

Route::resource('formaPagamento', FormaPagamentoController::class);

Route::get('/preferencias', [EmpresaController::class, 'preferencias'])->name('preferencias');