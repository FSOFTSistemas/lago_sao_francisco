@extends('adminlte::page')

@section('title', isset($quarto) ? 'Editar Quarto' : 'Cadastrar Quarto')

@section('content_header')
    <h1>{{ isset($quarto) ? 'Editar Quarto' : 'Cadastrar Quarto' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                {{ isset($quarto) ? 'Editar informações do Quarto' : 'Preencha os dados do novo Quarto' }}</h3>
        </div>
        <div class="card-body">
            <form id="createQuartoForm" action="{{ isset($quarto) ? route('quarto.update', $quarto->id) : route('quarto.store') }}" method="POST">
                @csrf
                @if (isset($quarto))
                    @method('PUT')
                @endif

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="nome">* Título:</label>
                  <div class="col-md-9">
                    <div><input class="form-control"  type="text" name="nome" id="nome" value="{{ old('nome', $quarto->nome ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="categoria_id">Categoria</label>
                  <div class="col-md-3">
                    <select class="form-control select2" id="categoria_id" name="categoria_id" required>
                        <option value="">Selecione uma categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->titulo }}
                            </option>
                        @endforeach
                    </select>
                  </div>
                </div>
      
              <div class="form-group row">
                <label class="col-md-3 label-control"  for="posicao">Posição:</label>
                <div class="col-md-3">
                  <div><input class="form-control"  type="number" name="posicao" id="posicao" value="{{ old('posicao', $quarto->posicao ?? '') }}"></div>
                </div>
              </div>

                <div class="form-group row">
                  <label for="descricao" class="col-md-3 label-control" >Descrição</label>
                  <div class="col-md-9">
                    <textarea class="form-control" name="descricao" rows="2">{{ old('descricao', $quarto->descricao ?? '') }}</textarea>
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
                          {{ old('status', $quarto->status ?? true) ? 'checked' : '' }}>
                      <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                          {{ old('status', $quarto->status ?? true) ? 'Ativo' : 'Inativo' }}
                      </label>
                  </div>
                </div>

                <div>
                  <hr>
                </div>

                <div class="card-footer">
                    <a href="{{ route('quarto.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($quarto) ? 'Atualizar Quarto' : 'Adicionar Quarto' }}</button>
                </div>
            </form>
        </div>
    </div>

@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "selecione...",
            allowClear: true,
            width: '100%'
        });
    });

</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
      const switchInput = document.getElementById('ativoSwitch');
      const label = document.getElementById('ativoLabel');
      label.textContent = switchInput.checked ? 'Ativo' : 'Inativo';
      switchInput.addEventListener('change', function () {
          label.textContent = this.checked ? 'Ativo' : 'Inativo';
      });
  });
</script>
  
@stop