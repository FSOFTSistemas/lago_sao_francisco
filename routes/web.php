<?php

use App\Http\Controllers\AdiantamentoController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\ContasAPagarController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\PlanoDeContaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/financeiro', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('financeiro');

Route::resource('planoDeConta', PlanoDeContaController::class);

Route::resource('empresa', EmpresaController::class);

// Route::middleware(['auth'])->group(function () {
// });
Route::resource('usuarios', UserController::class);

// Route::resource('usuarios', UserController::class)->middleware(['auth', 'role:Master']);

Route::resource('bancos', BancoController::class);

Route::resource('fornecedor', FornecedorController::class);

Route::resource('contasAPagar', ContasAPagarController::class);


Route::get('endereco/{cep}', [EnderecoController::class, 'buscarEnderecoPorCep'])->name('buscarCep');

Route::resource('endereco', EnderecoController::class);

Route::resource('funcionario', FuncionarioController::class);

Route::resource('adiantamento', AdiantamentoController::class);
