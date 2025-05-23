@extends('adminlte::page')

@section('title', isset($cardapio) ? 'Editar Cardápio' : 'Novo Cardápio')

@section('content_header')
    <h1>{{ isset($cardapio) ? 'Editar Cardápio' : 'Novo Cardápio de Buffet' }}</h1>
    <hr>
@stop

@section('content')
<div class="card">
        <div class="card-header green bg-primary text-white">
            <h3 class="card-title">
                {{ isset($cardapio) ? 'Editar informações do cardápio' : 'Preencha os dados do cardápio' }}</h3>
        </div>
        <div class="card-body">
          <form id="createCardapioForm" action="{{ isset($cardapio) ? route('cardapios.update', $cardapio->id) : route('cardapios.store') }}" method="POST">
                @csrf
                @if (isset($cardapios))
                    @method('PUT')
                @endif
              <div class="form-group row">
                  <label class="col-md-3 label-control"> *Nome:</label>
                  <div class="col-md-3">
                    <input type="text" name="nome" class="form-control" value="{{$cardapio->nome}}" required>
                  </div>
              </div>

              <div class="form-group row">
                  <label class="col-md-3 label-control">Observações:</label>
                  <div class="col-md-6">
                    <textarea name="observacoes" class="form-control" value="{{$cardapio->observacoes}}"></textarea>
                  </div>
              </div>

                <div>
                  <h4 class="form-section"><i class="fa fa-caret-down"></i> Defina a quantidade de itens por categoria:</h4>
                  <hr>
                </div>

                  @foreach ($categorias as $categoria)

                          <div class="form-group row">
                              <label class="col-md-3 label-control">{{ $categoria->nome }} (Limite de itens)</label>
                              <div class="col-md-3">
                                <input type="number" name="categorias[{{ $categoria->id }}]" min="0" value="0" class="form-control">
                              </div>
                          </div>

                  @endforeach

              <div class="card-footer">
                <button type="submit" class="btn new btn-primary">Salvar</button>

              </div>
          </form>
      </div>
</div>
@stop