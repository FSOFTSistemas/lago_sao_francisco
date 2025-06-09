@extends('adminlte::page')

@section('title', isset($cliente) ? 'Editar Cliente' : 'Cadastrar Cliente')

@section('content_header')
    <h1>{{ isset($cliente) ? 'Editar Cliente' : 'Cadastrar Cliente' }}</h1>
    <hr>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="createClienteForm" action="{{ isset($cliente) ? route('cliente.update', $cliente->id) : route('cliente.store') }}" method="POST">
                @csrf
                @if (isset($cliente))
                    @method('PUT')
                @endif

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="tipo">* Tipo:</label>
                    <div class="col-md-3">
                        <select class="form-control select2" id="tipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="PF" {{ (isset($cliente) && $cliente->tipo == 'PF') ? 'selected' : '' }}>Pessoa Física</option>
                            <option value="PJ" {{ (isset($cliente) && $cliente->tipo == 'PJ') ? 'selected' : '' }}>Pessoa Jurídica</option>
                        </select>
                    </div>
                </div>

                <!-- Todos os outros campos dentro desta div que será mostrada após selecionar o tipo -->
                <div id="outrosCampos" class="campos-gerais">
                    <!-- Campos para Pessoa Física -->
                    <div id="camposPF" class="conditional-field">
                        
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="cpf"> CPF:</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="cpf" name="cpf_cnpj"
                                    value="{{ $cliente->cpf_cnpj ?? '' }}">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="rg">RG:</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="rg" name="rg_ie"
                                    value="{{ $cliente->rg_ie ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="dataNascimento">* Data de Nascimento:</label>
                            <div class="col-md-3">
                                <input type="date" class="form-control" id="dataNascimento" name="data_nascimento"
                                    value="{{ $cliente->data_nascimento ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Campos para Pessoa Jurídica -->
                    <div id="camposPJ" class="conditional-field">
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="cnpj">* CNPJ:</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control cnpjteste" id="cnpj" name="cpf_cnpj"
                                    value="{{ $cliente->cpf_cnpj ?? '' }}">
                            </div>
                            <button class="btn btn-outline-primary" type="button" id="btnBuscarCnpj">
                            <i class="bi bi-search"></i> Buscar CNPJ
                        </button>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="inscricaoEstadual">Inscrição Estadual:</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="inscricaoEstadual" name="rg_ie"
                                    value="{{ $cliente->rg_ie ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" id="labelNomeRazao">* Nome/Razão Social:</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="nomeRazaoSocial" name="nome_razao_social"
                                value="{{ $cliente->nome_razao_social ?? '' }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" id="labelApelidoFantasia">* Apelido/Nome Fantasia:</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="apelidoNomeFantasia" name="apelido_nome_fantasia"
                                value="{{ $cliente->apelido_nome_fantasia ?? '' }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="telefone">Contato:</label>
                        <div class="row col-md-6">
                            <div class="col-md-3">
                                <label for="telefone">Telefone:</label>
                                <input type="text" class="form-control" id="telefone" name="telefone"
                                    value="{{ $cliente->telefone ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label for="whatsapp">Whatsapp:</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                    value="{{ $cliente->whatsapp ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 label-control" for="endereco_id">Endereço:</label>
                        <div class="col-md-3">
                            <select class="form-control select2" id="endereco_id" name="endereco_id">
                                <option value="">Selecione</option>
                                @foreach ($enderecos as $endereco)
                                    <option value="{{ $endereco->id }}" {{ (isset($cliente) && $cliente->endereco_id == $endereco->id) ? 'selected' : '' }}>
                                        {{ $endereco->logradouro }}, {{ $endereco->numero }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enderecoModal">
                            <i class="fas fa-plus"></i> Novo Endereço
                        </button>
                    </div>

                    <!-- Botão de Salvar -->
                    <div class="card-footer">
                        <a href="{{ route('cliente.index') }}" class="btn btn-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary">{{ isset($cliente) ? 'Atualizar Cliente' : 'Adicionar Cliente' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para Novo Endereço -->
    @include('components.endereco-modal')
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .conditional-field {
        display: none;
        transition: all 0.4s ease-in-out;
    }
    
    .conditional-field.show {
        display: block;
    }
    
    .campos-gerais {
        display: none;
    }
    
    .campos-gerais.show {
        display: block;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Select2
    $('.select2').select2({
        placeholder: "Selecione...",
        width: '100%'
    });
    
    // Atualiza os rótulos conforme o tipo selecionado
    function atualizarRotulos(tipo) {
        if (tipo === 'PF') {
            $('#labelNomeRazao').text('* Nome:');
            $('#labelApelidoFantasia').text('* Apelido:');
            $('#rg').parent().parent().find('label').text('RG:');
        } else if (tipo === 'PJ') {
            $('#labelNomeRazao').text('* Razão Social:');
            $('#labelApelidoFantasia').text('* Nome Fantasia:');
            $('#inscricaoEstadual').parent().parent().find('label').text('Inscrição Estadual:');
        }
    }

    
    // Controle dos campos condicionais
    function toggleCampos() {
        var tipo = $('#tipo').val();
        
        // Mostra todos os campos gerais se um tipo foi selecionado
        if (tipo) {
            $('#outrosCampos').addClass('show');
            
            // Oculta todos os campos condicionais primeiro
            $('#camposPF, #camposPJ').removeClass('show');
            $('#dataNascimento, #cpf, #cnpj, #inscricaoEstadual, #rg').removeAttr('required');
            
            // Mostra os campos específicos conforme o tipo selecionado
            if (tipo === 'PF') {
                $('#camposPF').addClass('show');
                $('#dataNascimento, #cpf').attr('required',);
            } else if (tipo === 'PJ') {
                $('#camposPJ').addClass('show');
                $('#cnpj').attr('required', 'required');
            }
            
            // Atualiza os rótulos
            atualizarRotulos(tipo);
        } else {
            $('#outrosCampos').removeClass('show');
        }
    }
    
    // Executa ao carregar a página
    toggleCampos();
    
    // Executa quando o tipo é alterado
    $('#tipo').change(function() {
        toggleCampos();
    });

    // Se estiver editando, mostra tudo imediatamente
    @if(isset($cliente))
    $(window).on('load', function() {
        $('#outrosCampos').addClass('show');
        $('#campos{{ $cliente->tipo }}').addClass('show');
        atualizarRotulos('{{ $cliente->tipo }}');
    });
    @endif
});
</script>
<script src="{{ asset('js/endereco.js') }}"></script>
<script src="{{ asset('js/buscarCnpj.js') }}"></script>
<script src="{{ asset('js/masksCnpj.js') }}"></script>
@stop