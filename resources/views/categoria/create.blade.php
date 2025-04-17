@extends('adminlte::page')

@section('title', isset($categoria) ? 'Editar Categoria' : 'Cadastrar Categoria')

@section('content_header')
    <h1>{{ isset($categoria) ? 'Editar Categoria' : 'Cadastrar Categoria' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                {{ isset($categoria) ? 'Editar informações da Categoria' : 'Preencha os dados da nova Categoria' }}</h3>
        </div>
        <div class="card-body">
            <form id="createCategoriaForm" action="{{ isset($categoria) ? route('categoria.update', $categoria->id) : route('categoria.store') }}" method="POST">
                @csrf
                @if (isset($categoria))
                    @method('PUT')
                @endif

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="titulo">* Título:</label>
                  <div class="col-md-9">
                    <div><input class="form-control"  type="text" name="titulo" id="titulo" value="{{ old('titulo', $categoria->titulo ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="ocupantes">* Ocupantes:</label>
                  <div class="col-md-3">
                    <div><input class="form-control"  type="text" name="ocupantes" id="ocupantes" value="{{ old('ocupantes', $quarto->ocupantes ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="posicao">Posição:</label>
                  <div class="col-md-3">
                    <div><input class="form-control"  type="text" name="posicao" id="posicao" value="{{ old('posicao', $quarto->posicao ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="descricao" class="col-md-3 label-control" >Descrição</label>
                  <div class="col-md-9">
                    <textarea class="form-control" name="descricao" rows="3">{{ old('descricao', $categoria->descricao ?? '') }}</textarea>
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
                          {{ old('status', $categoria->status ?? true) ? 'checked' : '' }}>
                      <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                          {{ old('status', $categoria->status ?? true) ? 'Ativo' : 'Inativo' }}
                      </label>
                  </div>
                </div>

                <div>
                  <hr>
                </div>

                <div class="card-footer">
                    <a href="{{ route('categoria.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($categoria) ? 'Atualizar Categoria' : 'Adicionar Categoria' }}</button>
                </div>
            </form>
        </div>
    </div>

@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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