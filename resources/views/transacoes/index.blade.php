@extends('adminlte::page')

@section('title', 'Transações')

@section('content_header')
    <h5>Transações - Hotel</h5>
    <hr>
@stop

@section('content')

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 1,
        'order' => [[4, 'dec']]
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>Reserva</th>
                    <th>Descrição</th>
                    <th>Forma de Pagamento</th>
                    <th>Valor</th>
                    <th>Data de Pagamento</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transacoes as $transacao)
                    <tr>
                        <td><a id="editlink" href="{{ route('reserva.edit', $transacao->reserva->id) }}">
                            00000{{ $transacao->reserva->id }}
                        </a></td>
                        <td>{{ $transacao->descricao }}</td>
                        <td>{{ $transacao->formaPagamento->descricao }}</td>
                        <td>R${{ $transacao->valor }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($transacao->data_pagamento)->format('d/m/Y') }}</td>
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
    .new:hover{
        background-color: #3e7222 !important;
    }
</style>
