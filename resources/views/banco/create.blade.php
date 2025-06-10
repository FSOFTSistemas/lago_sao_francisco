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

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="descricao">* Descrição:</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="descricao" name="descricao"
                            value="{{ $banco->descricao ?? '' }}" required>
                    </div>
                </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="agencia">* Agência:</label>
                        <div class="col-md-3">
                        <input type="number" class="form-control" id="agencia" name="agencia"
                            value="{{ $banco->agencia ?? '' }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="digitoAgencia"> Dígito da Agência:</label>
                        <div class="col-md-3">
                            <input type="number" class="form-control" id="digitoAgencia" name="digito_agencia"
                                value="{{ $banco->digito_agencia ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="numeroBanco">* Banco:</label>
                        <div class="row col-md-6">
                            <div class="col-md-3">
                                <label for="numeroBanco">Número:</label>
                                <input type="number" class="form-control" id="numeroBanco" name="numero_banco"
                                    value="{{ $banco->numero_banco ?? '' }}" required>
                            </div>
    
                            <div class="col-md-3">
                                <label for="digitoBanco">Dígito:</label>
                                    <input type="number" class="form-control" id="digitoBanco" name="digito_numero"
                                        value="{{ $banco->digito_numero ?? '' }}">
                            </div>
                        </div>
                    </div>
                
                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="numeroConta">* Conta:</label>
                        <div class="row col-md-6">
                            <div class="col-md-3">
                                <label for="digitoConta">Conta:</label>
                                <input type="number" class="form-control" id="numeroConta" name="numero_conta"
                                    value="{{ $banco->numero_conta ?? '' }}" required>
                            </div>
                
                            <div class="col-md-3">
                                <label for="digitoConta">Dígito:</label>
                                <input type="number" class="form-control" id="digitoConta" name="digito_conta"
                                        value="{{ $banco->digito_conta ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="agenciaUf">UF da Agência:</label>
                        <div class="col-md-3">
                            <select class="form-control select2" id="agenciaUf" name="agencia_uf" style="width: 100%;">
                                <option value="">Selecione o estado</option>
                                @php
                                    $estados = [
                                        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
                                        'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
                                        'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
                                    ];
                                @endphp
                                @foreach($estados as $uf)
                                    <option value="{{ $uf }}" {{ ($banco->agencia_uf ?? '') == $uf ? 'selected' : '' }}>
                                        {{ $uf }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="agenciaCidade">Cidade da Agência:</label>
                    <div class="col-md-3">
                    <input type="text" class="form-control" id="agenciaCidade" name="agencia_cidade"
                        value="{{ $banco->agencia_cidade ?? '' }}">
                        </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('bancos.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn green">{{ isset($banco) ? 'Atualizar Banco' : 'Adicionar Banco ' }}</button>
                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione o estado",
            allowClear: true,
            width: '100%'
        });
    });
</script>

@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
