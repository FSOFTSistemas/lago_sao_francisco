@extends('adminlte::page')

@section('title', 'Cadastrar Funcionário')

@section('content_header')
    <h1>Cadastrar Funcionário</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('funcionarios.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Setor</label>
                <input type="text" name="setor" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Salvar
            </button>
        </form>
    </div>
</div>
@stop
