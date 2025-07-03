@extends('adminlte::page')

@section('title', 'Caixa')

@section('content_header')
    <h5>Lista de Caixas</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createCaixaModal">
            <i class="fas fa-plus"></i> Novo Caixa
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 2, 'targets' => 3],
            ['responsivePriority' => 3, 'targets' => 4],
            ['responsivePriority' => 3, 'targets' => 5],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 5,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Situação</th>
                <th>Data de Abertura</th>
                <th>Data de Fechamento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($caixas as $caixa)
                <tr>
                    <td>{{ $caixa->id }}</td>
                    <td>{{ $caixa->descricao }}</td>
                    <td>{{ $caixa->status }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($caixa->data_abertura)->format('d/m/Y') }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($caixa->data_fechamento)->format('d/m/Y') }}</td>
                    <td>
                        @if ($caixa->status === 'fechado')
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#abrirCaixaModal{{ $caixa->id }}">
                                🟢 Abrir Caixa
                            </button>
                        @else
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#fecharCaixaModal{{ $caixa->id }}">
                                🔴 Fechar Caixa
                            </button>
                        @endif
                        {{-- <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showCaixa{{ $caixa->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editCaixaModal{{ $caixa->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteCaixaModal{{ $caixa->id }}">
                            🗑️
                        </button> --}}
                    </td>
                </tr>

                @include('caixa.modals._abrircaixa', ['caixa' => $caixa])
                @include('caixa.modals._fecharcaixa', ['caixa' => $caixa])
                @include('caixa.modals._show', ['caixa' => $caixa])
                @include('caixa.modals._edit', ['caixa' => $caixa])
                @include('caixa.modals._delete', ['caixa' => $caixa])
                <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
                <script>
                    $(document).ready(function() {
                        $('[id^="fecharCaixaModal"]').on('show.bs.modal', function() {
                            const modalId = $(this).attr('id');
                            const caixaId = modalId.replace('fecharCaixaModal', '');
                            const url = `/caixas/${caixaId}/resumo`;

                            $.get(url, function(resumo) {
                                const saldo = resumo.saldo ?? 0;
                                const formas = resumo.formas ?? {};

                                $(`#valor_final${caixaId}`).val(saldo.toFixed(2).replace('.', ','));

                                const lista = $(`#resumoFormas${caixaId}`);
                                lista.empty();

                                if (Object.keys(formas).length === 0) {
                                    lista.append(
                                        '<li class="list-group-item">Nenhuma movimentação encontrada.</li>');
                                } else {
                                    for (const [forma, valor] of Object.entries(formas)) {
                                        const nome = forma.charAt(0).toUpperCase() + forma.slice(1);
                                        lista.append(
                                            `<li class="list-group-item">${nome}: R$ ${valor.toFixed(2).replace('.', ',')}</li>`
                                            );
                                    }
                                }
                            }).fail(function() {
                                $(`#resumoFormas${caixaId}`).html(
                                    '<li class="list-group-item text-danger">Erro ao carregar resumo.</li>');
                            });
                        });
                    });
                </script>
            @endforeach
        </tbody>
    @endcomponent

    @include('caixa.modals._create')
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
