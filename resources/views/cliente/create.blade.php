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

            <!-- Campo Tipo -->
            <div class="form-group row">
                <label class="col-md-3 col-form-label label-control" for="tipo">* Tipo:</label>
                <div class="col-md-3">
                    <select class="form-control select2" id="tipo" name="tipo" required>
                        <option value="">Selecione...</option>
                        <option value="PF" {{ (isset($cliente) && $cliente->tipo == 'PF') ? 'selected' : '' }}>Pessoa Física</option>
                        <option value="PJ" {{ (isset($cliente) && $cliente->tipo == 'PJ') ? 'selected' : '' }}>Pessoa Jurídica</option>
                    </select>
                </div>
            </div>

            <!-- Todos os outros campos -->
            <div id="outrosCampos" class="campos-gerais">
                <!-- Campos para Pessoa Física -->
                <div id="camposPF" class="conditional-field">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label label-control" for="cpf"> CPF:</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="cpf" name="cpf_cnpj"
                                value="{{ $cliente->cpf_cnpj ?? '' }}">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label label-control" for="rg">RG:</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="rg" name="rg_ie"
                                value="{{ $cliente->rg_ie ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label label-control" for="dataNascimento">* Data de Nascimento:</label>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dataNascimento" name="data_nascimento"
                                value="{{ $cliente->data_nascimento ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Campos para Pessoa Jurídica -->
                <div id="camposPJ" class="conditional-field">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label label-control" for="cnpj">* CNPJ:</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control cnpjteste" id="cnpj" name="cpf_cnpj"
                                value="{{ $cliente->cpf_cnpj ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary" type="button" id="btnBuscarCnpj">
                                <i class="bi bi-search"></i> Buscar CNPJ
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label label-control" for="inscricaoEstadual">Inscrição Estadual:</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="inscricaoEstadual" name="rg_ie"
                                value="{{ $cliente->rg_ie ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Nome/Razão Social -->
                <div class="form-group row">
                    <label class="col-md-3 col-form-label label-control" id="labelNomeRazao">* Nome/Razão Social:</label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="nomeRazaoSocial" name="nome_razao_social"
                            value="{{ $cliente->nome_razao_social ?? '' }}" required>
                    </div>
                </div>

                <!-- Apelido/Nome Fantasia -->
                <div class="form-group row">
                    <label class="col-md-3 col-form-label label-control" id="labelApelidoFantasia">* Apelido/Nome Fantasia:</label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="apelidoNomeFantasia" name="apelido_nome_fantasia"
                            value="{{ $cliente->apelido_nome_fantasia ?? '' }}" required>
                    </div>
                </div>

                <!-- Contatos (agora lado a lado em telas maiores) -->
                <div class="form-group row">
                    <label class="col-md-3 col-form-label label-control">Contato:</label>
                    <div class="col-md-6 inline-fields">
                        <div>
                            <label for="telefone">Telefone:</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                value="{{ $cliente->telefone ?? '' }}">
                        </div>
                        <div>
                            <label for="whatsapp">Whatsapp:</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                value="{{ $cliente->whatsapp ?? '' }}">
                        </div>
                    </div>
                </div>
                
                <!-- Endereço -->
                <div class="form-group row">
                    <label class="col-md-3 col-form-label label-control" for="endereco_id">Endereço:</label>
                    <div class="col-md-4">
                        <select class="form-control select2" id="endereco_id" name="endereco_id">
                            <option value="">Selecione</option>
                            @foreach ($enderecos as $endereco)
                                <option value="{{ $endereco->id }}" {{ (isset($cliente) && $cliente->endereco_id == $endereco->id) ? 'selected' : '' }}>
                                    {{ $endereco->logradouro }}, {{ $endereco->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enderecoModal">
                            <i class="fas fa-plus"></i> Novo Endereço
                        </button>
                    </div>
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
    
    /* Novos estilos */
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
        
        .col-md-3, .col-md-4, .col-md-6 {
            width: 100%;
            max-width: 100%;
        }
        
        .row.col-md-6 > div {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .btn {
            margin-top: 0.5rem;
            width: 100%;
        }
    }
    
    /* Melhorias para campos lado a lado */
    .inline-fields {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .inline-fields > div {
        flex: 1;
        min-width: 200px;
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
            $('#camposPF input, #camposPJ input').prop('disabled', true);
            if (tipo === 'PF') {
                $('#camposPF').addClass('show');
                $('#dataNascimento, #cpf').attr('required',);
                $('#camposPF input').prop('disabled', false);
            } else if (tipo === 'PJ') {
                $('#camposPJ').addClass('show');
                $('#cnpj').attr('required', 'required');
                $('#camposPJ input').prop('disabled', false);

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