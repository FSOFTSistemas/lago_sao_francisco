@extends('adminlte::page')

@section('title', 'Cardápios de Excursão')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mb-1">Cardápios de Excursão</h1>
            <p class="text-muted mb-0">Cadastre as opções de almoço disponíveis para excursões.</p>
        </div>
        <a href="{{ route('preferencias') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title"><i class="fas fa-utensils mr-2"></i>Opções cadastradas</h3>
            <button type="button" class="btn btn-success btn-sm ml-auto" data-toggle="modal" data-target="#modalCriarCardapio">
                <i class="fas fa-plus mr-1"></i> Novo cardápio
            </button>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição do cardápio</th>
                        <th class="text-right">Valor por pessoa</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cardapios as $cardapio)
                        <tr>
                            <td class="font-weight-bold">{{ $cardapio->nome }}</td>
                            <td style="max-width: 440px; white-space: pre-line;">{{ $cardapio->descricao_cardapio }}</td>
                            <td class="text-right text-nowrap">R$ {{ number_format((float) $cardapio->valor_por_pessoa, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $cardapio->ativo ? 'success' : 'secondary' }}">
                                    {{ $cardapio->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm" title="Editar"
                                    data-toggle="modal" data-target="#modalEditarCardapio{{ $cardapio->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" title="Excluir"
                                    data-toggle="modal" data-target="#modalExcluirCardapio{{ $cardapio->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        @include('preferencias.partials.cardapioExcursaoEdit', ['cardapio' => $cardapio])
                        @include('preferencias.partials.cardapioExcursaoDelete', ['cardapio' => $cardapio])
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum cardápio de excursão cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalCriarCardapio" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('cardapios-excursao.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_origem" value="modalCriarCardapio">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title"><i class="fas fa-plus mr-1"></i> Novo cardápio de excursão</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('preferencias.partials.cardapioExcursaoForm', ['prefixo' => 'novo'])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($errors->any() && old('_origem'))
                $('#{{ old('_origem') }}').modal('show');
            @endif
        });
    </script>
@stop
