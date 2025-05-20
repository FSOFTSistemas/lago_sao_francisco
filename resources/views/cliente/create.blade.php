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
                    <label class="col-md-3 label-control" for="nomeRazaoSocial">* Nome/Razão Social:</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="nomeRazaoSocial" name="nome_razao_social"
                            value="{{ $cliente->nome_razao_social ?? '' }}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="apelidoNomeFantasia">* Apelido/Nome Fantasia:</label>
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
                                    @foreach ($endereco as $endereco)
                                        <option value="{{ $endereco->id }}">{{ $endereco->logradouro }}, {{$endereco->numero}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enderecoModal">
                                <i class="fas fa-plus"></i> Novo Endereço
                            </button>
                        </div>
                
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="dataNascimento">* Data de Nascimento:</label>
                            <div class="col-md-3">
                                <input type="date" class="form-control" id="dataNascimento" name="data_nascimento"
                                    value="{{ $cliente->data_nascimento ?? '' }}" required>
                            </div>
                        </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="cpfCnpj">* Documentos:</label>
                    <div class=" row col-md-6">
                        <div class="col-md-3">
                            <label for="cpfCnpj">CPF/CNPJ</label>
                            <input type="text" class="form-control" id="cpfCnpj" name="cpf_cnpj"
                                value="{{ $cliente->cpf_cnpj ?? '' }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="rgIe">RG/Inscrição Estadual:</label>
                            <input type="text" class="form-control" id="rgIe" name="rg_ie"
                                value="{{ $cliente->rg_ie ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="tipo">* Tipo:</label>
                    <div class="col-md-3">
                        <select class="form-control select2" id="tipo" name="tipo" required>
                            <option value="PF">Pessoa Física</option>
                            <option value="PJ">Pessoa Jurídica</option>
                        </select>
                    </div>
                </div>

                
                <!-- Botão de Salvar -->
                <div class="card-footer">
                    <a href="{{ route('cliente.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn green">{{ isset($cliente) ? 'Atualizar Cliente' : 'Adicionar Cliente ' }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal para Novo Endereço -->
    @include('components.endereco-modal')
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#whatsapp').mask('(00)00000-0000');
        $('#telefone').mask('(00)0000-0000');
    })
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecione...",
            width: '100%'
        });
    });
</script>
<script src="{{ asset('js/endereco.js') }}"></script>
@endsection