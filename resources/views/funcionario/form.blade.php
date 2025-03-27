@extends('adminlte::page')

@section('title', isset($funcionario) ? 'Editar Funcionário' : 'Nova Funcionário')

@section('content_header')
    <h4>{{ isset($funcionario) ? 'Editar funcionario' : 'Cadastrar Nova funcionario' }}</h4>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                {{ isset($funcionario) ? 'Editar informações da Funcionário' : 'Preencha os dados da novo Funcionário' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($funcionario) ? route('funcionario.update', $funcionario->id) : route('funcionario.store') }}"
                method="POST">
                @csrf
                @if (isset($funcionario))
                    @method('PUT')
                @endif
                <div class="mb-3">
                    <label for="nome">Nome:</label>
                    <input type="text" class="form-control" id="nome" name="nome" required value="{{ $funcionario->nome ?? '' }}">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cpf">CPF:</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" required value="{{ $funcionario->cpf ?? '' }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="salario">Salário:</label>
                        <input type="text" class="form-control" id="salario" name="salario" required value="{{ $funcionario->salario ?? '' }}">
                    </div>
                </div>

                <div class="row">
                        
                    <div class="col-md-6 mb-3">
                        <label for="dataContratacao">Data de Contratação:</label>
                        <input type="date" class="form-control" id="dataContratacao" name="data_contratacao" required value="{{ $funcionario->data_contratacao ?? '' }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="empresa">Empresa:</label>
                        <select class="form-control" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                  </div>
                  

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="setor">Setor:</label>
                        <input type="text" class="form-control" id="setor" name="setor" required value="{{ $funcionario->setor ?? '' }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cargo">Cargo:</label>
                        <input type="text" class="form-control" id="cargo" name="cargo" required value="{{ $funcionario->cargo ?? '' }}">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo">Situação:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                </div>

                <!-- Endereço -->
                <div class="form-group col-md-6">
                    <label for="endereco_id">Endereço</label>
                    <div class="input-group">
                        <select class="form-control" id="endereco_id" name="endereco_id" required>
                            <option value="" disabled {{ !isset($funcionario->endereco_id) ? 'selected' : '' }}>Selecione
                                um endereço...</option>
                            @foreach ($enderecos as $endereco)
                                <option value="{{ $endereco->id }}"
                                    {{ isset($funcionario->endereco_id) && $funcionario->endereco_id == $endereco->id ? 'selected' : '' }}>
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
                        <i class="fas fa-save"></i> {{ isset($funcionario) ? 'Atualizar' : 'Salvar' }}
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
