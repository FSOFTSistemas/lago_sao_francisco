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
                    <i class="fas fa-credit-card fa-3x" style="color: #007bff;"></i>
                    <h5 class="mt-3">Formas de Pagamento</h5>
                </div>
            </a>
        </div>
@endsection

@section('css')
    <style>
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
            border-color: #007bff;
        }

        .card-opcao h5 {
            font-weight: bold;
            color: inherit;
        }

        .card-opcao i {
            transition: color 0.2s ease-in-out;
        }
    </style>
@endsection
