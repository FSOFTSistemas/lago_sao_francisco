@extends('adminlte::page')

@section('title', isset($item) ? 'Editar Item' : 'Novo Item')

@section('content_header')
    <h1>{{ isset($item) ? 'Editar Item' : 'Novo Item de Buffet' }}</h1>
    <hr>
@stop

@section('content')
<div class="card">
        <div class="card-header green bg-primary text-white">
            <h3 class="card-title">
                {{ isset($item) ? 'Editar informações do item' : 'Preencha os dados do item' }}</h3>
        </div>
        <div class="card-body">
    <form action="{{ isset($item) ? route('buffet.update', $item) : route('buffet.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div class="form-group row">
            <label class="col-md-3 label-control"> *Nome</label>
            <div class="col-md-3">
              <input type="text" name="nome" class="form-control" value="{{ old('nome', $item->nome ?? '') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 label-control">* Valor por Pessoa (R$)</label>
            <div class="col-md-3">
              <input type="number" step="0.01" name="valor_unitario" class="form-control"
                  value="{{ old('valor_unitario', $item->valor_unitario ?? '') }}" required>
            </div>
        </div>

        <div class="card-footer">
          <a href="{{ route('buffet.index') }}" class="btn btn-secondary">Cancelar</a>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
  </div>
</div>
@stop
