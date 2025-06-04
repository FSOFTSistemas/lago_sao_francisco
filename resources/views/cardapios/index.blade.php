@extends('adminlte::page')

@section('title', 'Cardápios')

@section('content_header')
    <h5>Lista de Cardápios</h5>
    <hr>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col d-flex justify-content-start">
            <a href="{{ route('preferencias') }}" class="btn btn-success new">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="col d-flex justify-content-end">
           <a href="{{ route('cardapios.create') }}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Cardápio
        </a>
        </div>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ano</th>
                <th>Preço Base</th>
                <th>Validade (dias)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cardapios as $cardapio)
                <tr>
                    <td>{{ $cardapio->id }}</td>
                    <td>{{ $cardapio->NomeCardapio }}</td>
                    <td>{{ $cardapio->AnoCardapio }}</td>
                    <td>R$ {{ number_format($cardapio->PrecoBasePorPessoa, 2, ',', '.') }}</td>
                    <td>{{ $cardapio->ValidadeOrcamentoDias }}</td>
                    <td>
                        {{-- <a href="{{ route('cardapios.show', $cardapio->CardapioID) }}" class="btn btn-info btn-sm">👁️</a> --}}
                        <a href="{{ route('cardapios.edit', $cardapio->id) }}" class="btn btn-warning btn-sm">✏️</a>
                        {{-- <form action="{{ route('cardapios.destroy', $cardapio->CardapioID) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este cardápio?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form> --}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endcomponent
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop

@section('css')
    <style>
        .new {
            background-color: #679A4C !important;
            border: none !important;
        }
        .new:hover {
            background-color: #3e7222 !important;
        }
    </style>
@stop