@extends('adminlte::page')

@section('title', 'Receitas Avulsas')

@section('content_header')
    <h1>Receitas Avulsas</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Novo lançamento</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('receitas-avulsas.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="empresa_id">Empresa</label>
                            <select name="empresa_id" id="empresa_id" class="form-control" required {{ ! $usuario->hasRole('Master') ? 'disabled' : '' }}>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ old('empresa_id', $empresaSelecionada) == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                @endforeach
                            </select>
                            @if (! $usuario->hasRole('Master'))
                                <input type="hidden" name="empresa_id" value="{{ $empresaSelecionada }}">
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="caixa_id">Caixa</label>
                            <select name="caixa_id" id="caixa_id" class="form-control" required>
                                <option value="">Selecione</option>
                                @foreach ($caixas as $caixa)
                                    <option value="{{ $caixa->id }}"
                                        data-empresa="{{ $caixa->empresa_id }}"
                                        {{ old('caixa_id') == $caixa->id ? 'selected' : '' }}>
                                        {{ $caixa->descricao ?? 'Caixa #' . $caixa->id }}
                                        - {{ \Carbon\Carbon::parse($caixa->data_abertura)->format('d/m/Y') }}
                                        - {{ ucfirst($caixa->status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data">Data</label>
                            <input type="date" name="data" id="data" class="form-control" value="{{ old('data', now()->toDateString()) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="plano_de_conta_id">Plano de receita</label>
                            <select name="plano_de_conta_id" id="plano_de_conta_id" class="form-control" required>
                                <option value="">Selecione</option>
                                @foreach ($planosDeConta as $plano)
                                    <option value="{{ $plano->id }}"
                                        data-empresa="{{ $plano->empresa_id }}"
                                        {{ old('plano_de_conta_id') == $plano->id ? 'selected' : '' }}>
                                        {{ $plano->descricao }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="movimento_id">Forma / movimento</label>
                            <select name="movimento_id" id="movimento_id" class="form-control" required>
                                <option value="">Selecione</option>
                                @foreach ($movimentos as $movimento)
                                    <option value="{{ $movimento->id }}" {{ old('movimento_id') == $movimento->id ? 'selected' : '' }}>
                                        {{ $movimento->descricao }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="number" name="valor" id="valor" class="form-control" step="0.01" min="0.01" value="{{ old('valor') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <input type="text" name="descricao" id="descricao" class="form-control" value="{{ old('descricao') }}" placeholder="Ex.: Receita manual Restaurante Dom Dina" required>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Lançar receita
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Receitas lançadas</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('receitas-avulsas.index') }}" class="mb-3">
                <div class="row">
                    @if ($usuario->hasRole('Master'))
                        <div class="col-md-3">
                            <label for="filtro_empresa_id">Empresa</label>
                            <select name="empresa_id" id="filtro_empresa_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ $empresaSelecionada == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome_fantasia }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-3">
                        <label for="data_inicio">Data início</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $dataInicio }}">
                    </div>

                    <div class="col-md-3">
                        <label for="data_fim">Data fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $dataFim }}">
                    </div>

                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Empresa</th>
                            <th>Descrição</th>
                            <th>Plano</th>
                            <th>Movimento</th>
                            <th class="text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receitas as $receita)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($receita->data)->format('d/m/Y') }}</td>
                                <td>{{ $receita->daEmpresa->nome_fantasia ?? '-' }}</td>
                                <td>{{ $receita->descricao }}</td>
                                <td>{{ $receita->planoDeConta->descricao ?? '-' }}</td>
                                <td>{{ $receita->movimento->descricao ?? '-' }}</td>
                                <td class="text-right">R$ {{ number_format($receita->valor, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nenhuma receita avulsa encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Total</th>
                            <th class="text-right">R$ {{ number_format($receitas->sum('valor'), 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        (function () {
            const empresa = document.getElementById('empresa_id');
            const caixa = document.getElementById('caixa_id');
            const plano = document.getElementById('plano_de_conta_id');

            function filtrarPorEmpresa(select) {
                if (!empresa || !select) return;

                const empresaId = empresa.value;
                Array.from(select.options).forEach(function (option) {
                    if (!option.value) return;

                    const optionEmpresa = option.getAttribute('data-empresa');
                    const visivel = !optionEmpresa || optionEmpresa === empresaId;
                    option.hidden = !visivel;

                    if (!visivel && option.selected) {
                        select.value = '';
                    }
                });
            }

            function atualizarOpcoes() {
                filtrarPorEmpresa(caixa);
                filtrarPorEmpresa(plano);
            }

            if (empresa) {
                empresa.addEventListener('change', atualizarOpcoes);
                atualizarOpcoes();
            }
        })();
    </script>
@endpush
