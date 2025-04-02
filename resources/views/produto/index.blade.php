@extends('adminlte::page')

@section('title', 'Produtos')

@section('content_header')
    <h5>Lista de Produtos</h5>
@stop

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a class="btn btn-success float-end" href="{{ route('produto.create') }}"><i class="fas fa-plus"></i> Adicionar Produto</a>
</div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 4],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>EAN</th>
                    <th>Descrição</th>
                    <th>Preço de Venda</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produtos as $produto)
                    <tr>
                        <td>{{ $produto->id }}</td>
                        <td>{{ $produto->ean }}</td>
                        <td>{{ $produto->descricao }}</td>
                        <td>R${{ $produto->preco_venda }}</td>
                        <td>{{ $produto->situacao }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showProduto{{ $produto->id }}">
                                👁️
                            </button>

                            <a class="btn btn-warning btn-sm" href="{{route('produto.edit', $produto->id)}}"
                                >
                                ✏️
                            </a>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteProdutoModal{{ $produto->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('produto.modals._show', ['produto' => $produto])
                    @include('produto.modals._delete', ['produto' => $produto])
                @endforeach
            </tbody>
    @endcomponent

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

@stop
