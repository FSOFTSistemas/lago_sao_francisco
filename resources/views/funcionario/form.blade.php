@extends('adminlte::page')

@section('title', isset($funcionario) ? 'Atualizar Funcionário' : 'Novo Funcionário')

@section('content_header')
    <h4>{{ isset($funcionario) ? 'Atualizar funcionário' : 'Cadastrar Novo funcionário' }}</h4>
@stop

@section('content')
    <div class="card">
        <div class="card-header green text-white">
            <h3 class="card-title">
                {{ isset($funcionario) ? 'Preencha os dados atualizados' : 'Preencha os dados do novo Funcionário' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($funcionario) ? route('funcionario.update', $funcionario->id) : route('funcionario.store') }}"
                method="POST">
                @csrf
                @if (isset($funcionario))
                    @method('PUT')
                @endif
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="nome">* Nome:</label>
                    <div class="col-md-7">
                        <input type="text" class="form-control" id="nome" name="nome" required value="{{ $funcionario->nome ?? '' }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="cpf"> CPF:</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="cpf" name="cpf" value="{{ $funcionario->cpf ?? '' }}">
                    </div>
                
                    <label class="col-md-1 label-control" for="salario"> Salário:</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="salario" name="salario" value="{{ $funcionario->salario ?? '' }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="dataContratacao">* Data de Contratação:</label>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dataContratacao" name="data_contratacao" required value="{{ $funcionario->data_contratacao ?? '' }}">
                    </div>
                
                    <label class="col-md-1 label-control" for="empresa">* Empresa:</label>
                    <div class="col-md-3">
                        <select class="form-control" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}" {{ old('empresa_id', $funcionario->empresa_id ?? '') == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                  
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="setor">* Setor:</label>
                    <div class="col-md-3">
                        <select class="form-control" id="setor" name="setor" required>
                            <option value="">Selecione</option>
                            @foreach($setores as $setor)
                                <option value="{{ $setor }}" {{ old('setor', $funcionario->setor ?? '') == $setor ? 'selected' : '' }}>
                                    {{ $setor }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                
                    <label class="col-md-1 label-control" for="cargo">* Cargo:</label>
                    <div class="col-md-3">
                        <select class="form-control" id="cargo" name="cargo" required>
                            <option value="">Selecione</option>
                            @foreach($cargos as $cargo)
                                <option value="{{ $cargo }}" {{ old('cargo', $funcionario->cargo ?? '') == $cargo ? 'selected' : '' }}>
                                    {{ $cargo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="endereco_id">Endereço:</label>
                    <div class="col-md-3">
                        <select class="form-control select2" id="endereco_id" name="endereco_id">
                            <option value="">Selecione</option>
                            @foreach ($enderecos as $item)
                                <option value="{{ $item->id }}" 
                                    {{ old('endereco_id', $funcionario->endereco_id ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->logradouro }}, {{ $item->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enderecoModal">
                        <i class="fas fa-plus"></i> Novo Endereço
                    </button>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 form-label d-block label-control"> Ativo?</label>
                    <div class="form-check form-switch">
                        <input type="hidden" name="status" value="inativo">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="ativoSwitch"
                            name="status"
                            value="ativo"
                            {{ old('status', $funcionario->status ?? 'ativo') === 'ativo' ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                            {{ old('status', $funcionario->status ?? 'ativo') === 'ativo' ? 'Ativo' : 'Inativo' }}
                        </label>
                    </div>
                </div>

                <!-- Botão de Salvar -->
                <div class="card-footer">
                    <a href="{{ route('funcionario.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn green">{{ isset($funcionario) ? 'Atualizar Funcionário' : 'Adicionar Funcionário ' }}</button>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#cpf').mask('000.000.000-00');
        });
    </script>
    <script src="{{ asset('js/endereco.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('ativoSwitch');
        const label = document.getElementById('ativoLabel');
        label.textContent = switchInput.checked ? 'Ativo' : 'Inativo';
        switchInput.value = switchInput.checked ? 'ativo' : 'inativo';
        switchInput.addEventListener('change', function () {
        label.textContent = this.checked ? 'Ativo' : 'Inativo';
        this.value = this.checked ? 'ativo' : 'inativo';
    });
});
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "selecione...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection