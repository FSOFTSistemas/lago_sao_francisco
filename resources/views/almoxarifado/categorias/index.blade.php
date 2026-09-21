@extends('adminlte::page')

@section('title', 'Categorias do Almoxarifado')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-1 text-dark"><i class="fas fa-warehouse text-success mr-2"></i>Almoxarifado - Categorias</h1>
            <p class="text-muted mb-0">Gerencie as categorias para organizar os produtos e materiais de consumo interno.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('preferencias') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </div>
    <hr class="mt-2">
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> <strong>Atenção:</strong> Verifique os erros no formulário.
            <ul class="mb-0 mt-1 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Barra de busca e ações fora da tabela --}}
    <div class="row mb-3 align-items-center">
        <div class="col-12 col-md-6 col-lg-5 mb-2 mb-md-0">
            <form action="{{ route('almoxarifado.categorias.index') }}" method="GET">
                <div class="input-group">
                    <input type="text"
                           name="busca"
                           class="form-control text-dark"
                           placeholder="Buscar por nome da categoria..."
                           value="{{ $busca ?? '' }}"
                           aria-label="Buscar categoria">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit" title="Buscar">
                            <i class="fas fa-search"></i>
                        </button>
                        @if (!empty($busca))
                            <a href="{{ route('almoxarifado.categorias.index') }}" class="btn btn-outline-danger" title="Limpar filtro de busca">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-12 col-md-6 col-lg-7 text-md-right">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCriarCategoria">
                <i class="fas fa-plus mr-1"></i> Nova Categoria
            </button>
        </div>
    </div>

    <div class="card card-outline card-success shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-bold mb-0">
                <i class="fas fa-tags mr-2 text-success"></i>Categorias Cadastradas ({{ $categorias->count() }})
            </h3>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px;" class="text-center">#</th>
                        <th>Nome da Categoria</th>
                        <th style="width: 160px;" class="text-center">Itens Vinculados</th>
                        <th style="width: 120px;" class="text-center">Status</th>
                        <th style="width: 140px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $categoria)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $categoria->id }}</td>
                            <td class="font-weight-bold text-dark">
                                {{ $categoria->nome }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info px-2 py-1">
                                    <i class="fas fa-boxes mr-1"></i>{{ $categoria->itens_count ?? 0 }} {{ ($categoria->itens_count ?? 0) == 1 ? 'item' : 'itens' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($categoria->ativo)
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fas fa-check mr-1"></i>Ativo
                                    </span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1">
                                        <i class="fas fa-ban mr-1"></i>Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm" title="Editar categoria"
                                    data-toggle="modal" data-target="#modalEditarCategoria{{ $categoria->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" title="Excluir categoria"
                                    data-toggle="modal" data-target="#modalExcluirCategoria{{ $categoria->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Modais específicos para cada linha --}}
                        @include('almoxarifado.categorias.modals._edit', ['categoria' => $categoria])
                        @include('almoxarifado.categorias.modals._delete', ['categoria' => $categoria])
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-dark">
                                    <i class="fas fa-tags fa-3x mb-3 text-secondary"></i>
                                    @if (!empty($busca))
                                        <h5 class="text-dark">Nenhuma categoria encontrada para "<strong>{{ $busca }}</strong>"</h5>
                                        <p class="text-secondary mb-3">Tente buscar por outro termo ou limpe a busca para ver todas as categorias.</p>
                                        <a href="{{ route('almoxarifado.categorias.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times mr-1"></i> Limpar busca
                                        </a>
                                    @else
                                        <h5 class="text-dark">Nenhuma categoria de almoxarifado cadastrada</h5>
                                        <p class="text-secondary mb-3">Cadastre categorias como Limpeza, Manutenção, Escritório ou Rouparia para organizar os produtos.</p>
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCriarCategoria">
                                            <i class="fas fa-plus mr-1"></i> Cadastrar Primeira Categoria
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de Criação --}}
    @include('almoxarifado.categorias.modals._create')
@stop

@push('js')
<script>
    $(document).ready(function () {
        // Desativa o botão de cadastro após o primeiro clique para evitar duplicidade
        $('#formCriarCategoria').on('submit', function () {
            if (!this.checkValidity()) {
                return;
            }

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        });

        // Restaura o botão caso o modal seja reaberto
        $('#modalCriarCategoria').on('show.bs.modal', function () {
            var $btn = $('#formCriarCategoria').find('button[type="submit"]');
            $btn.prop('disabled', false);
            $btn.html('<i class="fas fa-save mr-1"></i> Salvar Categoria');
        });

        // Aplica a mesma proteção para os formulários de edição
        $('form[id^="formEditarCategoria"]').on('submit', function () {
            if (!this.checkValidity()) {
                return;
            }

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        });

        // Desativa o botão de exclusão após o primeiro clique
        $(document).on('submit', '.form-excluir-categoria', function (e) {
            var $form = $(this);
            if ($form.data('submitting')) {
                e.preventDefault();
                return false;
            }
            $form.data('submitting', true);

            var $btn = $form.find('.btn-confirmar-exclusao');
            $btn.addClass('disabled').css('pointer-events', 'none').prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Excluindo...');
            $form.closest('.modal').find('button').addClass('disabled').css('pointer-events', 'none').prop('disabled', true);
        });

        $(document).on('click', '.btn-confirmar-exclusao', function (e) {
            var $form = $(this).closest('form');
            if ($form.data('submitting')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endpush
