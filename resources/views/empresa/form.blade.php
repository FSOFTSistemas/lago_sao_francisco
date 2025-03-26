@extends('adminlte::page')

@section('title', isset($empresa) ? 'Editar Empresa' : 'Nova Empresa')

@section('content_header')
    <h4>{{ isset($empresa) ? 'Editar empresa' : 'Cadastrar Nova empresa' }}</h4>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                {{ isset($empresa) ? 'Editar informações da Empresa' : 'Preencha os dados da novo Empresa' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($empresa) ? route('empresa.update', $empresa->id) : route('empresa.store') }}"
                method="POST">
                @csrf
                @if (isset($empresa))
                    @method('PUT')
                @endif
                <!-- CNPJ -->
                <div class="form-group col-md-6">
                    <label for="cnpj">CNPJ</label>
                    <div class="input-group">
                        <input type="text" id="cnpj" name="cnpj" class="form-control" placeholder="Digite o CNPJ"
                            value="{{ $empresa->cnpj ?? '' }}">
                        <button type="button" class="btn btn-info" id="search-cnpj">
                            <i class="fas fa-search"></i> Consultar CNPJ
                        </button>
                    </div>
                </div>

                <!-- Razão Social -->
                <x-adminlte-input id="razaoSocial" name="razao_social" label="Razão Social"
                    placeholder="Digite a razão social" fgroup-class="col-md-6" value="{{ $empresa->razao_social ?? '' }}"
                    required />

                <!-- Nome Fantasia -->
                <x-adminlte-input id="nomeFantasia" name="nomeFantasia" label="Nome Fantasia"
                    placeholder="Digite o nome fantasia" fgroup-class="col-md-6"
                    value="{{ $empresa->nome_fantasia ?? '' }}" />


                <!-- Inscrição Estadual -->
                <x-adminlte-input id="ie" name="inscricao_estadual" label="Inscrição Estadual"
                    placeholder="Digite a inscrição estadual" fgroup-class="col-md-6"
                    value="{{ $empresa->inscricao_estadual ?? '' }}" />

                <!-- Endereço -->
                <div class="form-group col-md-6">
                    <label for="endereco_id">Endereço</label>
                    <div class="input-group">
                        <select class="form-control" id="endereco_id" name="endereco_id" required>
                            <option value="" disabled {{ !isset($empresa->endereco_id) ? 'selected' : '' }}>Selecione
                                um endereço...</option>
                            @foreach ($enderecos as $endereco)
                                <option value="{{ $endereco->id }}"
                                    {{ isset($empresa->endereco_id) && $empresa->endereco_id == $endereco->id ? 'selected' : '' }}>
                                    {{ $endereco->logradouro }}, {{ $endereco->numero }} - {{ $endereco->cidade }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#enderecoModal">
                            <i class="fas fa-plus"></i> Novo Endereço
                        </button>
                    </div>
                </div>

                <!-- Botão de Salvar -->
                <div class="form-group col-md-12">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> {{ isset($empresa) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Novo Endereço -->
    @include('components.endereco-modal')
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#cnpj').mask('99.999.999/9999-99');

            $('#search-cnpj').on('click', function() {
                const cnpj = $('#cnpj').val().replace(/[^\d]/g, '');
                if (cnpj.length !== 14) {
                    alert('Por favor, insira um CNPJ válido com 14 dígitos.');
                    return;
                }
                $.ajax({
                    url: `https://open.cnpja.com/office/${cnpj}`,
                    type: 'GET',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('Authorization',
                            'Bearer YOUR_API_TOKEN');
                    },
                    success: function(response) {
                        if (response) {
                            console.log(response);
                            $('#razaoSocial').val(response.company.name || '');
                            $('#nomeFantasia').val(response.alias || '');
                            $('#ie').val(response.registrations[0].number || '');
                            $('#logradouro').val(response.address.street);
                            $('#cidade').val(response.address.city);
                            $('#uf').val(response.address.state);
                            $('#bairro').val(response.address.district);
                            $('#cep').val(response.address.zip);
                            $('#numero').val(response.address.number);
                        } else {
                            alert('Nenhum dado encontrado para o CNPJ fornecido.');
                        }
                    },
                    error: function() {
                        alert('Ocorreu um erro ao buscar o CNPJ. Por favor, tente novamente.');
                    }
                });
            });
        });
    </script>
    <script src="{{ asset('js/endereco.js') }}"></script>
@endsection
