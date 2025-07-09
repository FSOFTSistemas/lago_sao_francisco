@extends('adminlte::page')

@section('title', isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa')

@section('content_header')
    <h5>{{ isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa' }}</h5>
    <hr>
@endsection

@section('content')
<form action="{{ isset($tarifa) ? route('tarifa.update', $tarifa->id) : route('tarifa.store') }}" method="POST">
    @csrf
    @if(isset($tarifa))
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-body mt-3">
            <ul class="nav nav-tabs" id="tarifaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active editlink" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações da Tarifa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link editlink" id="dias-tab" data-toggle="tab" href="#dias" role="tab">Tarifa / Dia da semana</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link editlink" id="hospede-tab" data-toggle="tab" href="#hospede" role="tab">Tarifa / Hóspede</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="tarifaTabsContent">
                {{-- Aba 1: Informações da Tarifa --}}
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="form-group row">
                        <label for="nome" class="col-md-3 label-control">* Título:</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $tarifa->nome ?? '') }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="categoria" class="col-md-3 label-control">Categoria:</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="categoria" name="categoria" value="{{ old('categoria', $tarifa->categoria->titulo ?? '') }}" readonly>
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
                          <input 
                              class="form-check-input" 
                              type="checkbox" 
                              id="ativoSwitch" 
                              name="ativo" 
                              value="1"
                              {{ old('ativo', $tarifa->ativo ?? true) ? 'checked' : '' }}>
                          <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                              {{ old('ativo', $tarifa->ativo ?? true) ? 'Ativa' : 'Inativa' }}
                          </label>
                      </div>
                  </div>
                  
                  
                </div>


                {{-- Aba 2: Tarifa por Dia da Semana --}}
                <div class="tab-pane fade" id="dias" role="tabpanel">
                    <div class="alert alert-secondary">
                        <strong>DICA:</strong> O valor que você deve inserir em cada diária abaixo refere-se à ocupação máxima do quarto. <br>
                        <em>EX: Se você possui um quarto que a ocupação máxima é de 3 pessoas, você deve inserir o valor cobrado por estas 3 pessoas na diária.</em>
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
                                <input
                                    type="number"
                                    class="form-control"
                                    name="{{ $key }}"
                                    value="{{ old($key, $tarifa->$key ?? '') }}"
                                >
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Aba 3: Tarifa / Hóspede --}}
                <div class="tab-pane fade" id="hospede" role="tabpanel">
                    <p>Em breve...</p>
                </div>
            </div>

            {{-- Infos finais --}}
            @if(isset($tarifa))
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
    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('ativoSwitch');
        const label = document.getElementById('ativoLabel');

        switchInput.addEventListener('change', function () {
            label.textContent = this.checked ? 'Ativa' : 'Inativa';
        });
    });
</script>
@endsection