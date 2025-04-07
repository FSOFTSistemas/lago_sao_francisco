@extends('adminlte::page')

@section('title', isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa')

@section('content_header')
    <h1>{{ isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa' }}</h1>
@endsection

@section('content')
<form action="{{ isset($tarifa) ? route('tarifa.update', $tarifa->id) : route('tarifa.store') }}" method="POST">
    @csrf
    @if(isset($tarifa))
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="tarifaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">Informações da Tarifa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dias-tab" data-toggle="tab" href="#dias" role="tab">Tarifa / Dia da semana</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="hospede-tab" data-toggle="tab" href="#hospede" role="tab">Tarifa / Hóspede</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="tarifaTabsContent">
                {{-- Aba 1: Informações da Tarifa --}}
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="mb-3">
                        <label for="nome" class="form-label">* Título</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $tarifa->nome ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoria" class="form-label">* Categoria</label>
                        <input type="text" class="form-control" id="categoria" name="categoria" value="{{ old('categoria', $tarifa->categoria ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações extras</label>
                        <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $tarifa->observacoes ?? '') }}</textarea>
                    </div>

                    <div class="mb-3">
                      <label class="form-label d-block">* Tarifa Ativa?</label>
                      <div class="form-check form-switch">
                          <input type="hidden" name="ativo" value="0">
                          <input 
                              class="form-check-input" 
                              type="checkbox" 
                              id="ativoSwitch" 
                              name="ativo" 
                              value="1"
                              {{ old('ativo', $tarifa->ativo ?? false) ? 'checked' : '' }}>
                          <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                              {{ old('ativo', $tarifa->ativo ?? false) ? 'Ativa' : 'Inativa' }}
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
                            'seg' => 'Segunda-feira',
                            'ter' => 'Terça-feira',
                            'qua' => 'Quarta-feira',
                            'qui' => 'Quinta-feira',
                            'sex' => 'Sexta-feira',
                            'sab' => 'Sábado',
                            'dom' => 'Domingo',
                        ];
                    @endphp

                    @foreach ($dias as $key => $label)
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">{{ $label }}</label>
                            <div class="col-sm-4">
                                <input
                                    type="text"
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
            <button type="submit" class="btn btn-{{ isset($tarifa) ? 'info' : 'success' }}">
                {{ isset($tarifa) ? 'Atualizar Tarifa' : 'Criar Tarifa' }}
            </button>
        </div>
    </div>
</form>
@endsection


@section('css')
<style>
.form-switch {
    padding-left: 3em;
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-switch .form-check-input {
    width: 3.5rem;
    height: 1.75rem;
    background-color: #dee2e6;
    border-radius: 1.75rem;
    position: relative;
    transition: background-color 0.3s ease-in-out;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
}

.form-switch .form-check-input:checked {
    background-color: #0d6efd;
}

.form-switch .form-check-input::before {
    content: "";
    position: absolute;
    width: 1.5rem;
    height: 1.5rem;
    top: 0.125rem;
    left: 0.125rem;
    border-radius: 50%;
    background-color: white;
    transition: transform 0.3s ease-in-out;
}

.form-switch .form-check-input:checked::before {
    transform: translateX(1.75rem);
}
</style>
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