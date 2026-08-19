@extends('adminlte::page')

@section('title', isset($excursao) ? 'Editar excursão' : 'Cadastrar excursão')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mb-1">{{ isset($excursao) ? 'Editar excursão' : 'Cadastrar excursão' }}</h1>
            <p class="text-muted mb-0">{{ isset($excursao) ? 'Atualize os dados da excursão selecionada.' : 'Informe os dados da excursão para incluí-la nos eventos.' }}</p>
        </div>
        <a href="{{ route('eventos.excursoes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar para excursões
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bus mr-2"></i>{{ isset($excursao) ? 'Editar dados da excursão' : 'Dados da excursão' }}
                    </h3>
                </div>

                <form action="{{ isset($excursao) ? route('eventos.excursoes.update', $excursao) : route('eventos.excursoes.store') }}" method="POST">
                    @csrf
                    @isset($excursao)
                        @method('PUT')
                    @endisset

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <strong>Revise os dados informados:</strong>
                                <ul class="mb-0 mt-2 pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="data">Data da excursão <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input
                                    type="date"
                                    class="form-control @error('data') is-invalid @enderror"
                                    id="data"
                                    name="data"
                                    value="{{ old('data', isset($excursao) ? $excursao->data->format('Y-m-d') : '') }}"
                                    required
                                    autofocus
                                >
                                @error('data')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="qtd_pessoas">Quantidade de pessoas <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    </div>
                                    <input
                                        type="number"
                                        class="form-control @error('qtd_pessoas') is-invalid @enderror"
                                        id="qtd_pessoas"
                                        name="qtd_pessoas"
                                        value="{{ old('qtd_pessoas', $excursao->qtd_pessoas ?? '') }}"
                                        min="1"
                                        step="1"
                                        placeholder="Ex.: 40"
                                        required
                                    >
                                    @error('qtd_pessoas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="valor">Valor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input
                                        type="number"
                                        class="form-control @error('valor') is-invalid @enderror"
                                        id="valor"
                                        name="valor"
                                        value="{{ old('valor', $excursao->valor ?? '') }}"
                                        min="0"
                                        step="0.01"
                                        inputmode="decimal"
                                        placeholder="0,00"
                                        required
                                    >
                                    @error('valor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Informe o valor total da excursão.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select
                                class="form-control @error('status') is-invalid @enderror"
                                id="status"
                                name="status"
                                required
                            >
                                <option value="AGENDADO" @selected(old('status', $excursao->status ?? 'AGENDADO') === 'AGENDADO')>Agendado</option>
                                <option value="REALIZADO" @selected(old('status', $excursao->status ?? '') === 'REALIZADO')>Realizado</option>
                                <option value="CANCELADO" @selected(old('status', $excursao->status ?? '') === 'CANCELADO')>Cancelado</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-7">
                                <label for="responsavel">Responsável <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('responsavel') is-invalid @enderror"
                                    id="responsavel"
                                    name="responsavel"
                                    value="{{ old('responsavel', $excursao->responsavel ?? '') }}"
                                    maxlength="255"
                                    placeholder="Nome do responsável pela excursão"
                                    required
                                >
                                @error('responsavel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-md-5">
                                <label for="telefone_responsavel">Telefone do responsável <span class="text-danger">*</span></label>
                                <input
                                    type="tel"
                                    class="form-control @error('telefone_responsavel') is-invalid @enderror"
                                    id="telefone_responsavel"
                                    name="telefone_responsavel"
                                    value="{{ old('telefone_responsavel', $excursao->telefone_responsavel ?? '') }}"
                                    maxlength="20"
                                    placeholder="(00) 00000-0000"
                                    required
                                >
                                @error('telefone_responsavel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição <span class="text-danger">*</span></label>
                            <textarea
                                class="form-control @error('descricao') is-invalid @enderror"
                                id="descricao"
                                name="descricao"
                                rows="4"
                                maxlength="1000"
                                placeholder="Descreva a excursão"
                                required
                            >{{ old('descricao', $excursao->descricao ?? '') }}</textarea>
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('eventos.excursoes.index') }}" class="btn btn-light border">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> {{ isset($excursao) ? 'Salvar alterações' : 'Cadastrar excursão' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
