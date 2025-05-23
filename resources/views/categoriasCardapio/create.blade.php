@extends('adminlte::page')

@section('title', 'Nova Categoria')

@section('content_header')
    <h1>Nova Categoria de Cardápio</h1>
@stop

@section('content')
    <form action="{{ route('categoriasCardapio.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="nome">Nome da Categoria</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>


        <button class="btn btn-success">Salvar</button>
        <a href="{{ route('categoriasCardapio.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
