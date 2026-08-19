@extends('adminlte::page')

@php
    $somenteLeitura = $visualizacao ?? false;
    $valorAtual = old('valor_pessoa', $excursao->valor_pessoa ?? null);
    $valorFormatado = $valorAtual !== null && $valorAtual !== ''
        ? number_format((float) $valorAtual, 2, ',', '.')
        : '';
@endphp

@section('title', $somenteLeitura ? 'Visualizar excursão' : (isset($excursao) ? 'Editar excursão' : 'Cadastrar excursão'))

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mb-1">{{ $somenteLeitura ? 'Visualizar excursão' : (isset($excursao) ? 'Editar excursão' : 'Cadastrar excursão') }}</h1>
            <p class="text-muted mb-0">{{ $somenteLeitura ? 'Consulte os dados da excursão realizada ou cancelada.' : (isset($excursao) ? 'Atualize os dados da excursão selecionada.' : 'Informe os dados da excursão para incluí-la nos eventos.') }}</p>
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
                        <i class="fas fa-bus mr-2"></i>{{ $somenteLeitura ? 'Dados da excursão' : (isset($excursao) ? 'Editar dados da excursão' : 'Dados da excursão') }}
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
                                    required @disabled($somenteLeitura)
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
                                        required @disabled($somenteLeitura)
                                    >
                                    @error('qtd_pessoas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="valor_display">Valor por pessoa <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input
                                        type="text"
                                        class="form-control @error('valor_pessoa') is-invalid @enderror"
                                        id="valor_display"
                                        value="{{ $valorFormatado }}"
                                        inputmode="numeric"
                                        placeholder="0,00"
                                        autocomplete="off"
                                        required @disabled($somenteLeitura)
                                    >
                                    <input type="hidden" id="valor_pessoa" name="valor_pessoa" value="{{ $valorAtual }}">
                                    @error('valor_pessoa')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Informe o valor cobrado por pessoa.</small>
                            </div>
                        </div>

                        @isset($excursao)
                            <div class="form-group">
                                <label>Status</label>
                                <input class="form-control" value="{{ ucfirst(strtolower(str_replace('_', ' ', $excursao->status))) }}" disabled>
                            </div>
                        @endisset

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
                                    required @disabled($somenteLeitura)
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
                                    inputmode="tel"
                                    autocomplete="tel"
                                    placeholder="(00) 00000-0000"
                                    required @disabled($somenteLeitura)
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
                                maxlength="200"
                                placeholder="Descreva a excursão"
                                required @disabled($somenteLeitura)
                            >{{ old('descricao', $excursao->descricao ?? '') }}</textarea>
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Máximo de 200 caracteres.</small>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('eventos.excursoes.index') }}" class="btn btn-light border">Cancelar</a>
                        @unless($somenteLeitura)
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-1"></i> {{ isset($excursao) ? 'Salvar alterações' : 'Cadastrar excursão' }}
                            </button>
                        @endunless
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const telefone = document.getElementById('telefone_responsavel');
            const valorDisplay = document.getElementById('valor_display');
            const valorInput = document.getElementById('valor_pessoa');

            function formatarTelefone(valor) {
                const digitos = valor.replace(/\D/g, '').slice(0, 11);

                if (digitos.length === 0) return '';
                if (digitos.length <= 2) return digitos.replace(/^(\d{0,2})/, '($1');
                if (digitos.length <= 6) return digitos.replace(/^(\d{2})(\d+)/, '($1) $2');
                if (digitos.length <= 10) return digitos.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');

                return digitos.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            }

            if (telefone && !telefone.disabled) {
                telefone.value = formatarTelefone(telefone.value);
                telefone.addEventListener('input', function() {
                    telefone.value = formatarTelefone(telefone.value);
                });
            }

            if (!valorDisplay || !valorInput || valorDisplay.disabled) return;

            let digitosValor = valorInput.value
                ? Math.round(Number(valorInput.value) * 100).toString()
                : '';

            function renderizarValor() {
                const centavos = Number(digitosValor || 0);
                valorDisplay.value = (centavos / 100).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                valorInput.value = (centavos / 100).toFixed(2);
            }

            valorDisplay.addEventListener('keydown', function(event) {
                if (/^\d$/.test(event.key)) {
                    event.preventDefault();
                    digitosValor = (digitosValor + event.key).replace(/^0+/, '').slice(0, 10);
                    renderizarValor();
                } else if (event.key === 'Backspace') {
                    event.preventDefault();
                    digitosValor = digitosValor.slice(0, -1);
                    renderizarValor();
                } else if (event.key === 'Delete') {
                    event.preventDefault();
                    digitosValor = '';
                    renderizarValor();
                }
            });

            valorDisplay.addEventListener('beforeinput', function(event) {
                if (event.inputType === 'deleteContentBackward') {
                    event.preventDefault();
                    digitosValor = digitosValor.slice(0, -1);
                    renderizarValor();
                } else if (event.inputType === 'deleteContentForward') {
                    event.preventDefault();
                    digitosValor = '';
                    renderizarValor();
                } else if (event.inputType === 'insertText' && /^\d$/.test(event.data || '')) {
                    event.preventDefault();
                    digitosValor = (digitosValor + event.data).replace(/^0+/, '').slice(0, 10);
                    renderizarValor();
                }
            });

            valorDisplay.addEventListener('paste', function(event) {
                event.preventDefault();
                digitosValor = (event.clipboardData.getData('text').match(/\d/g) || [])
                    .join('')
                    .replace(/^0+/, '')
                    .slice(0, 10);
                renderizarValor();
            });

            valorDisplay.addEventListener('input', function() {
                digitosValor = valorDisplay.value.replace(/\D/g, '').replace(/^0+/, '').slice(0, 10);
                renderizarValor();
            });

            if (digitosValor !== '') renderizarValor();
        });
    </script>
@stop
