@extends('adminlte::page')

@section('title', 'Lista de Funcionários')

@section('content_header')
    <h1>Lista de Funcionários</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('funcionarios.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Cadastrar Novo
        </a>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Setor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($funcionarios as $funcionario)
                    <tr>
                        <td>{{ $funcionario->id }}</td>
                        <td>{{ $funcionario->nome }}</td>
                        <td>{{ $funcionario->setor }}</td>
                        <td>{{ $funcionario->status }}</td>
                        <td>
                            <a href="{{ route('funcionarios.edit', $funcionario->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('funcionarios.destroy', $funcionario->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop
