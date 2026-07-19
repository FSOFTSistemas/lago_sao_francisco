@extends('adminlte::page')

@section('title', isset($motorhome) ? 'Editar Motorhome' : 'Cadastrar Motorhome')

@section('content_header')
    <h1>{{ isset($motorhome) ? 'Editar Motorhome' : 'Cadastrar Motorhome' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header green bg-primary text-white">
            <h3 class="card-title">
                {{ isset($motorhome) ? 'Editar informações do Motorhome' : 'Preencha os dados do novo Motorhome' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($motorhome) ? route('motorhome.update', $motorhome->id) : route('motorhome.store') }}" method="POST">
                @csrf
                @if (isset($motorhome))
                    @method('PUT')
                @endif

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="placa">* Placa:</label>
                  <div class="col-md-3">
                    <input class="form-control" type="text" name="placa" id="placa" maxlength="10" value="{{ old('placa', $motorhome->placa ?? '') }}">
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="modelo">Modelo:</label>
                  <div class="col-md-6">
                    <input class="form-control" type="text" name="modelo" id="modelo" value="{{ old('modelo', $motorhome->modelo ?? '') }}">
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="comprimento">Comprimento (m):</label>
                  <div class="col-md-3">
                    <input class="form-control" type="number" step="0.01" min="0" name="comprimento" id="comprimento" value="{{ old('comprimento', $motorhome->comprimento ?? '') }}">
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="cor">Cor:</label>
                  <div class="col-md-3">
                    <input class="form-control" type="text" name="cor" id="cor" value="{{ old('cor', $motorhome->cor ?? '') }}">
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="hospede_id">Proprietário/Responsável:</label>
                  <div class="col-md-5">
                    <select class="form-control select2" id="hospede_id" name="hospede_id">
                        <option value="">Selecione um hóspede</option>
                        @foreach ($hospedes as $hospede)
                            <option value="{{ $hospede->id }}" {{ old('hospede_id', $motorhome->hospede_id ?? '') == $hospede->id ? 'selected' : '' }}>
                                {{ $hospede->nome }}
                            </option>
                        @endforeach
                    </select>
                  </div>
                  <div class="col-md-1">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCadastrarHospede" title="Cadastrar Novo Hóspede">
                        <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="observacoes" class="col-md-3 label-control">Observações</label>
                  <div class="col-md-9">
                    <textarea class="form-control" name="observacoes" rows="2">{{ old('observacoes', $motorhome->observacoes ?? '') }}</textarea>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 form-label d-block label-control">Ativo?</label>
                  <div class="form-check form-switch">
                      <input type="hidden" name="status" value="0">
                      <input
                          class="form-check-input"
                          type="checkbox"
                          id="ativoSwitch"
                          name="status"
                          value="1"
                          {{ old('status', $motorhome->status ?? true) ? 'checked' : '' }}>
                      <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                          {{ old('status', $motorhome->status ?? true) ? 'Ativo' : 'Inativo' }}
                      </label>
                  </div>
                </div>

                <div>
                  <hr>
                </div>

                <div class="card-footer">
                    <a href="{{ route('motorhome.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($motorhome) ? 'Atualizar Motorhome' : 'Adicionar Motorhome' }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalCadastrarHospede" tabindex="-1" role="dialog"
        aria-labelledby="modalHospedeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('hospede.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHospedeLabel">Cadastrar Hóspede</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-md-3 label-control" for="nome_hospede_rapido">* Nome completo:</label>
                            <div class="col-md-9">
                                <input class="form-control" required="required" type="text" name="nome"
                                    id="nome_hospede_rapido" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('hospede.create') }}" class="btn btn-secondary">Cadastro Completo</a>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

  .card-footer{
    text-align: right
  }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "selecione...",
            allowClear: true,
            width: '100%'
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('ativoSwitch');
        const label = document.getElementById('ativoLabel');
        const updateLabel = () => {
            label.textContent = switchInput.checked ? 'Ativo' : 'Inativo';
        };
        if (switchInput) {
            updateLabel();
            switchInput.addEventListener('change', updateLabel);
        }
    });
</script>
@stop
