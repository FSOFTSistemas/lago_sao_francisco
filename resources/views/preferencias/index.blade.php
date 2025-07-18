@extends('adminlte::page')

@section('title', 'Configurações')

@section('content_header')
    <h1>Preferências</h1>
    <hr>
@endsection

@section('content')
<h5>Configurações de Hotel / Cadastro</h5>
    <div class="row">
       
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
                    <h5 class="mt-3">Quartos</h5>
                </div>
            </a>
        </div>
        <!-- Cadastro Categorias Quarto -->
        <div class="col-md-3">
            <a href="{{ route('categoria.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-tag fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Categorias Quarto</h5>
                </div>
            </a>
        </div>
        <!-- Preferências do Hotel -->
        <div class="col-md-3">
            <a href="{{ route('preferencias.hotel')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fa fa-key fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Preferências - Reserva</h5>
                </div>
            </a>
        </div>
    </div>
    <br>
    <h5>Configurações de Eventos / Cadastro</h5>
    <br>
    <div class="row">
         <!-- Cadastro de Espaços -->
        <div class="col-md-3">
            <a href="{{ route('espaco.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-campground fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Espaços</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('itemCardapio.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-utensils fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Itens Buffet</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('cardapios.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-book-open fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Cardapios Buffet</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('adicionais.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-chair fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Adicionais (Mobília)</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('parceiros.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-handshake fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Parceiros</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('categoriasParceiro.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-bookmark fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Categorias Parceiros</h5>
                </div>
            </a>
        </div>
    </div>
     <br>
    <h5>Configurações Gerais / Cadastro</h5>
    <br>
    <div class="row">
        <div class="col-md-3">
            <a href="{{ route('categoriaProduto.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-bookmark fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Categorias dos Produtos</h5>
                </div>
            </a>
        </div>
         <!-- Formas de Pagamento -->
        <div class="col-md-3">
            <a href="{{ route('formaPagamento.index') }}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-credit-card fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Formas de Pagamento</h5>
                </div>
            </a>
        </div>
    </div>
     <br>
    <h5>Configurações Day Use / Cadastro</h5>
    <br>
    <div class="row">
        <div class="col-md-3">
            <a href="{{ route('itemDayuse.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Entrada/Passeios</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('logsdayuse.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-info fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Logs</h5>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('souvenir.index')}}" class="card card-opcao text-center">
                <div class="card-body">
                    <i class="fas fa-gift fa-3x" style="color: #679A4C;"></i>
                    <h5 class="mt-3">Souvenir</h5>
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
