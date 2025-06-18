@extends('adminlte::page')

@section('title', isset($vendedor) ? 'Editar Vendedor' : 'Cadastrar Vendedor')

@section('content_header')
    <h1>{{ isset($vendedor) ? 'Editar Vendedor' : 'Cadastrar Vendedor' }}</h1>
    <hr>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form id="createVendedorForm" action="{{ isset($vendedor) ? route('vendedor.update', $vendedor->id) : route('vendedor.store') }}" method="POST">
            @csrf
            @if (isset($vendedor))
                @method('PUT')
            @endif

            <!-- Nome -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label label-control" for="nome">* Nome:</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="nome" name="nome" value="{{ $vendedor->nome ?? '' }}" required>
                </div>
            </div>

            <!-- Email -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label label-control" for="email">Email:</label>
                <div class="col-md-6">
                    <input type="email" class="form-control" id="email" name="email" value="{{ $vendedor->email ?? '' }}">
                </div>
            </div>

            <!-- Telefone -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label label-control" for="telefone">Telefone:</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="telefone" name="telefone" value="{{ $vendedor->telefone ?? '' }}">
                </div>
            </div>

            <!-- CPF -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label label-control" for="cpf">CPF:</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="cpf" name="cpf" value="{{ $vendedor->cpf ?? '' }}">
                </div>
            </div>

            <!-- Endereço -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label label-control" for="endereco">Endereço:</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="endereco" name="endereco" value="{{ $vendedor->endereco ?? '' }}">
                </div>
            </div>

            <!-- Botão de Salvar -->
            <div class="card-footer">
                <a href="{{ route('vendedor.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary">{{ isset($vendedor) ? 'Atualizar Vendedor' : 'Adicionar Vendedor' }}</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .form-group.row {
        margin-bottom: 1.5rem;
        align-items: center;
    }

    .label-control {
        padding-top: 0.375rem;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .card-body {
        padding: 2rem;
    }

    .card-footer {
        padding: 1.5rem 0 0 0;
        background: transparent;
        border-top: none;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    @media (max-width: 768px) {
        .form-group.row {
            flex-direction: column;
            align-items: flex-start;
        }

        .col-md-3, .col-md-6 {
            width: 100%;
            max-width: 100%;
        }

        .btn {
            margin-top: 0.5rem;
            width: 100%;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    $('#cpf').mask('000.000.000-00', {reverse: true});
    $('#telefone').mask('(00) 00000-0000');
});
</script>
@stop