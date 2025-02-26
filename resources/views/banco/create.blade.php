@extends('adminlte::page')

@section('title', isset($banco) ? 'Editar Banco' : 'Cadastrar Banco')

@section('content_header')
    <h1>{{ isset($banco) ? 'Editar Banco' : 'Cadastrar Banco' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="createBancoForm" action="{{ isset($banco) ? route('bancos.update', $banco->id) : route('bancos.store') }}" method="POST">
                @csrf
                @if (isset($banco))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <input type="text" class="form-control" id="descricao" name="descricao"
                        value="{{ $banco->descricao ?? '' }}" required>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="agencia">Agência:</label>
                        <input type="text" class="form-control" id="agencia" name="agencia"
                            value="{{ $banco->agencia ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="digitoAgencia">Dígito da Agência:</label>
                        <input type="text" class="form-control" id="digitoAgencia" name="digito_agencia"
                            value="{{ $banco->digito_agencia ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="numeroBanco">Número do Banco:</label>
                        <input type="text" class="form-control" id="numeroBanco" name="numero_banco"
                            value="{{ $banco->numero_banco ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="digitoBanco">Dígito do Banco:</label>
                        <input type="text" class="form-control" id="digitoBanco" name="digito_numero"
                            value="{{ $banco->digito_numero ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <label for="numeroConta">Número da Conta:</label>
                        <input type="text" class="form-control" id="numeroConta" name="numero_conta"
                            value="{{ $banco->numero_conta ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="digitoConta">Dígito da Conta:</label>
                        <input type="text" class="form-control" id="digitoConta" name="digito_conta"
                            value="{{ $banco->digito_conta ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="agenciaUf">UF da Agência:</label>
                    <input type="text" class="form-control" id="agenciaUf" name="agencia_uf"
                        value="{{ $banco->agencia_uf ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="agenciaCidade">Cidade da Agência:</label>
                    <input type="text" class="form-control" id="agenciaCidade" name="agencia_cidade"
                        value="{{ $banco->agencia_cidade ?? '' }}">
                </div>

                <div class="text-right">
                    <a href="{{ route('bancos.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($banco) ? 'Atualizar' : 'Criar' }}</button>
                </div>
            </form>
        </div>
    </div>
@stop
