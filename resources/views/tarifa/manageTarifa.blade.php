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
                        <a class="nav-link active editlink" id="info-tab" data-toggle="tab" href="#info"
                            role="tab">Informações da Tarifa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link editlink" id="dias-tab" data-toggle="tab" href="#dias" role="tab">Tarifa /
                            Dia da semana</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link editlink" id="hospede-tab" data-toggle="tab" href="#hospede"
                            role="tab">Tarifa / Hóspede</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="tarifaTabsContent">
                    {{-- Aba 1: Informações da Tarifa --}}
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <div class="form-group row">
                            <label for="nome" class="col-md-3 label-control">* Título:</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="{{ old('nome', $tarifa->nome ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="categoria" class="col-md-3 label-control">Categoria:</label>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="categoria" name="categoria"
                                    value="{{ old('categoria', $tarifa->categoria->titulo ?? '') }}" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="observacoes" class="col-md-3 label-control">Observações extras:</label>
                            <div class="col-md-6">
                                <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $tarifa->observacoes ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-3 label-control form-lab d-block">Tarifa Ativa?</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="ativo" value="0">
                                <input class="form-check-input" type="checkbox" id="ativoSwitch" name="ativo"
                                    value="1" {{ old('ativo', $tarifa->ativo ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                                    {{ old('ativo', $tarifa->ativo ?? true) ? 'Ativa' : 'Inativa' }}
                                </label>
                            </div>
                        </div>


                    </div>


                    {{-- Aba 2: Tarifa por Dia da Semana --}}
                    <div class="tab-pane fade" id="dias" role="tabpanel">
                        <div class="alert alert-secondary">
                            <strong>DICA:</strong> O valor que você deve inserir em cada diária abaixo refere-se ao valor padrão do quarto. <br>
                            <em>Na aba Tarifa/Hóspede você poderá configurar os valores adicionais por adulto/criança.</em>
                        </div>

                        @php
                            $dias = [
                                'seg' => 'Segunda-feira:',
                                'ter' => 'Terça-feira:',
                                'qua' => 'Quarta-feira:',
                                'qui' => 'Quinta-feira:',
                                'sex' => 'Sexta-feira:',
                                'sab' => 'Sábado:',
                                'dom' => 'Domingo:',
                            ];
                        @endphp

                        @foreach ($dias as $key => $label)
                            <div class="form-group row">
                                <label class="col-md-3 label-control col-form-label">{{ $label }}</label>
                                <div class="col-sm-4">
                                    <input type="number" class="form-control" name="{{ $key }}"
                                        value="{{ old($key, $tarifa->$key ?? 0) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Aba 3: Tarifa / Hóspede --}}
                    <div class="tab-pane fade" id="hospede" role="tabpanel">
                        <div class="alert alert-secondary">
                            <strong>DICA:</strong> A quantidade padrão é referente ao valor inserido na aba anterior. Durante o cadastro da reserva, se houver um número maior que o padrão, será aplicado o adicional para cada excedente <br>
                            <em>EX: Se o padrão é 2 adultos e o valor adicional é 250, então se houver 3 adultos na reserva, a diária será composta por valor da diária + adicional de 250</em>
                        </div>
                        <div class="container py-4">

                            <div class="form-group row">
                                <label for="padrao_adultos" class="col-md-3 label-control">Quantidade padrão de adultos</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" id="padrao_adultos" name="padrao_adultos"
                                        value="{{ old('padrao_adultos', $tarifa->padrao_adultos) }}" min="0">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="padrao_criancas" class="col-md-3 label-control">Quantidade padrão de crianças</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" id="padrao_criancas" name="padrao_criancas"
                                        value="{{ old('padrao_criancas', $tarifa->padrao_criancas) }}" min="0">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="adicional_adulto" class="col-md-3 label-control">Valor adicional por adulto
                                    extra (R$)</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="adicional_adulto" name="adicional_adulto"
                                            value="{{ old('adicional_adulto', number_format($tarifa->adicional_adulto, 2, ',', '.')) }}">
                                    </div>
                            </div>
                            <div class="form-group row">
                                <label for="adicional_crianca" class="col-md-3 label-control">Valor adicional por criança 
                                    extra (R$)</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="adicional_crianca"
                                            name="adicional_crianca"
                                            value="{{ old('adicional_crianca', number_format($tarifa->adicional_crianca, 2, ',', '.')) }}">
                                    </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Infos finais --}}
                @if (isset($tarifa))
                    <p class="text-muted mt-3">
                        Criado em: {{ $tarifa->created_at->format('d/m/Y H:i:s') }}<br>
                        Alterado em: {{ $tarifa->updated_at->format('d/m/Y H:i:s') }}<br>
                        Alterado por: {{ Auth::user()->name }}
                    </p>
                @endif
            </div>


            <div class="card-footer text-end">
                <a href="{{ route('tarifa.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn new btn-{{ isset($tarifa) ? 'info' : 'success' }}">
                    {{ isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa' }}
                </button>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switchInput = document.getElementById('ativoSwitch');
            const label = document.getElementById('ativoLabel');

            switchInput.addEventListener('change', function() {
                label.textContent = this.checked ? 'Ativa' : 'Inativa';
            });
        });
    </script>
@endsection
