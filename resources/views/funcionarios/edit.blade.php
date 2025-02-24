@extends('adminlte::page')

@section('title', 'Editar Funcionário')

@section('content_header')
    <h1>Editar Funcionário</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('funcionarios.update', $funcionario->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ $funcionario->nome }}" required>
            </div>
            <div class="form-group">
                <label>Setor</label>
                <input type="text" name="setor" class="form-control" value="{{ $funcionario->setor }}" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="Ativo" {{ $funcionario->status == 'Ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="Inativo" {{ $funcionario->status == 'Inativo' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Atualizar
            </button>
        </form>
    </div>
</div>
@stop
