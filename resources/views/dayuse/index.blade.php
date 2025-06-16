@extends('adminlte::page')

@section('title', 'Day use')

@section('content_header')
    <h5>Vendas Day Use</h5>
    <hr>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col d-flex justify-content-end">
           <a href="{{ route('dayuse.create') }}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Day Use
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
                <th>Data</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dayuses as $dayuse)
                <tr>
                    <td>{{ $dayuse->id }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($dayuse->data)->format('d/m/Y') }}</td>
                    <td>{{ $dayuse->cliente->tipo == 'PJ' ? $dayuse->cliente->apelido_nome_fantasia : $dayuse->cliente->nome_razao_social }}</td>
                    <td>{{ $dayuse->vendedor->nome }}</td>
                    <td>
                        <form action="{{ route('dayuse.destroy', $dayuse->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este Day use?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
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