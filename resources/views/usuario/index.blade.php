@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <h5>Lista de Usuários</h5>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('usuarios.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Usuário
        </a>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 0,
    ])

            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showUsuario{{ $user->id }}">
                                👁️
                            </button>

                            <a href="{{route('usuarios.edit', $user->id)}}" class="btn btn-warning btn-sm">
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteUsuarioModal{{ $user->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('usuario.modals._show' , ['usuario' => $user])
                    @include('usuario.modals._delete', ['usuario' => $user])
                @endforeach
            </tbody>
    @endcomponent

    @include('components.endereco-modal')
@stop

@section('css')
<style>
    .new {
        background-color: #679A4C !important;
        border: none !important;
    }
    .new:hover{
        background-color: #3e7222 !important;
    }
    /* .main-sidebar {
        background-color: #679A4C !important;
    } */
</style>