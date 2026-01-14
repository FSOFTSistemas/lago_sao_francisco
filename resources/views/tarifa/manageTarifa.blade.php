@extends('adminlte::page')

@section('title', isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa')

@section('content_header')
    <h5>{{ isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa' }}</h5>
    <hr>
@endsection

@section('content')
    <form action="{{ isset($tarifa) ? route('tarifa.update', $tarifa->id) : route('tarifa.store') }}" method="POST">
        @csrf
        @if (isset($tarifa))
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body mt-3">
                <ul class="nav nav-tabs" id="tarifaTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active editlink" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações Gerais</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link editlink" id="dias-tab" data-toggle="tab" href="#dias" role="tab">Valores da Diária</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link editlink" id="hospede-tab" data-toggle="tab" href="#hospede" role="tab">Adicionais por Pessoa</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="tarifaTabsContent">
                    {{-- Aba 1: Informações da Tarifa --}}
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="form-group row">
                            <label for="nome" class="col-md-3 label-control">* Nome da Tarifa:</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="{{ old('nome', $tarifa->nome ?? '') }}" placeholder="Ex: Padrão, Carnaval 2026..." required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="categoria_id" class="col-md-3 label-control">* Categoria:</label>
                            <div class="col-md-4">
                                <select name="categoria_id" id="categoria_id" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" 
                                            {{-- Lógica de Seleção: Se é edição (tem $tarifa) ou se veio da URL (nova com parametro) --}}
                                            {{ (old('categoria_id', $tarifa->categoria_id ?? ($categoriaIdPreSelecionada ?? ''))) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->titulo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- BLOCO DE TEMPORADA --}}
                        <div class="form-group row">
                            <label class="col-md-3 label-control">Tipo de Tarifa:</label>
                            <div class="col-md-9">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="altaTemporadaSwitch" name="alta_temporada" value="1"
                                        {{ old('alta_temporada', $tarifa->alta_temporada ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label font-weight-bold text-warning" for="altaTemporadaSwitch">
                                        <i class="fas fa-sun"></i> É Alta Temporada / Feriado?
                                    </label>
                                </div>
                                
                                <div id="dates-container" class="row" style="display: {{ old('alta_temporada', $tarifa->alta_temporada ?? false) ? 'flex' : 'none' }};">
                                    <div class="col-md-4">
                                        <label class="small text-muted">Data Início</label>
                                        <input type="date" name="data_inicio" class="form-control" value="{{ old('data_inicio', $tarifa->data_inicio ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small text-muted">Data Fim</label>
                                        <input type="date" name="data_fim" class="form-control" value="{{ old('data_fim', $tarifa->data_fim ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="observacoes" class="col-md-3 label-control">Observações:</label>
                            <div class="col-md-6">
                                <textarea class="form-control" name="observacoes" rows="2">{{ old('observacoes', $tarifa->observacoes ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control">Status:</label>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="ativo" value="0">
                                    <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo"
                                        value="1" {{ old('ativo', $tarifa->ativo ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ativoSwitch">Ativa</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Aba 2: Valores (Dias da semana) --}}
                    <div class="tab-pane fade" id="dias" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Defina o valor da diária para cada dia da semana.
                        </div>
                        @php
                            $dias = [
                                'dom' => 'Domingo', 'seg' => 'Segunda-feira', 'ter' => 'Terça-feira',
                                'qua' => 'Quarta-feira', 'qui' => 'Quinta-feira', 'sex' => 'Sexta-feira', 'sab' => 'Sábado'
                            ];
                        @endphp
                        @foreach ($dias as $key => $label)
                            <div class="form-group row">
                                <label class="col-md-3 label-control col-form-label">{{ $label }}</label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" class="form-control" name="{{ $key }}"
                                            value="{{ old($key, $tarifa->$key ?? 0) }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Aba 3: Hóspedes Extras --}}
                    <div class="tab-pane fade" id="hospede" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-users"></i> Configuração de capacidade e valores excedentes.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Capacidade Padrão (Inclusa na diária)</h5>
                                <div class="form-group row">
                                    <label class="col-md-6 label-control">Adultos</label>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="padrao_adultos" value="{{ old('padrao_adultos', $tarifa->padrao_adultos ?? 2) }}" min="1">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-6 label-control">Crianças</label>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="padrao_criancas" value="{{ old('padrao_criancas', $tarifa->padrao_criancas ?? 0) }}" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 border-left">
                                <h5>Valores Excedentes (Por pessoa extra)</h5>
                                <div class="form-group row">
                                    <label class="col-md-6 label-control">Por Adulto Extra</label>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                            <input type="text" class="form-control money" name="adicional_adulto"
                                                value="{{ old('adicional_adulto', number_format($tarifa->adicional_adulto ?? 0, 2, ',', '.')) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-6 label-control">Por Criança Extra</label>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                            <input type="text" class="form-control money" name="adicional_crianca"
                                                value="{{ old('adicional_crianca', number_format($tarifa->adicional_crianca ?? 0, 2, ',', '.')) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('tarifa.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> {{ isset($tarifa) ? 'Salvar Alterações' : 'Criar Tarifa' }}
                </button>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Controle de exibição das datas de temporada
            const switchAlta = document.getElementById('altaTemporadaSwitch');
            const containerDatas = document.getElementById('dates-container');

            function toggleDates() {
                if(switchAlta.checked) {
                    containerDatas.style.display = 'flex';
                } else {
                    containerDatas.style.display = 'none';
                    // Opcional: Limpar datas se desmarcar? Melhor não para UX, caso tenha clicado errado
                }
            }

            if(switchAlta) {
                switchAlta.addEventListener('change', toggleDates);
            }
        });
    </script>
@endsection