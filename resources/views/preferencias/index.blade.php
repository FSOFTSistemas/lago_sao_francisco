@extends('adminlte::page')

@section('title', 'Configurações')

@section('content_header')
    <h1>Configurações Gerais</h1>
@endsection

@section('content')
    <div class="row">
        <!-- Formas de Pagamento -->
        <div class="col-md-3">
            <a href="{{ route('formaPagamento.index') }}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-credit-card fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Formas de Pagamento</h5>
                </div>
            </a>
        </div>
        <!-- Tarifas Hotel -->
        <div class="col-md-3">
            <a href="{{ route('tarifa.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-hotel fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Tarifas do Hotel</h5>
                </div>
            </a>
        </div>
        <!-- Cadastro Quarto -->
        <div class="col-md-3">
            <a href="{{ route('quarto.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-bed fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cadastro de Quartos</h5>
                </div>
            </a>
        </div>
        <!-- Cadastro Categorias Quarto -->
        <div class="col-md-3">
            <a href="{{ route('categoria.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-tag fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cadastro de Categorias Quarto</h5>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
         <!-- Cadastro de Espaços -->
        <div class="col-md-3">
            <a href="{{ route('espaco.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-campground fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cadastro de Espaços</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('buffet.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-utensils fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cadastro de Itens Buffet</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('cardapios.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-book-open fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cadastro de Cardapios Buffet</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('categoriasCardapio.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-bookmark fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cadastro de Categorias do Cardapio</h5>
                </div>
            </a>
        </div>
    </div>
@endsection

@section('css')
    <style>
        h1{
            color: #679A4C;
            font-weight: 600;
        }
        .card-opcao {
            border: 2px solid transparent;
            border-radius: 10px;
            background-color: transparent;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            text-decoration: none;
            color: inherit;
        }

        .card-opcao:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-color: #679A4C;
        }

        .card-opcao h5 {
            font-weight: bold;
            color: #679A4C;
        }

        .card-opcao i {
            transition: color 0.2s ease-in-out;
        }
    </style>
@endsection
