@extends('adminlte::page')

@section('title', 'Itens do Almoxarifado')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-1 text-dark"><i class="fas fa-boxes text-success mr-2"></i>Almoxarifado - Itens e Estoque</h1>
            <p class="text-muted mb-0">Controle físico de produtos e materiais de consumo interno.</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('almoxarifado.movimentacoes.index') }}" class="btn btn-outline-primary mr-1">
                <i class="fas fa-exchange-alt mr-1"></i> Movimentações
            </a>
            <a href="{{ route('almoxarifado.categorias.index') }}" class="btn btn-outline-success">
                <i class="fas fa-tags mr-1"></i> Categorias
            </a>
        </div>
    </div>
    <hr class="mt-2">
@stop

@section('content')
    {{-- Mensagens de Feedback --}}
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

    {{-- Barra de Filtros fora da tabela --}}
    <div class="row mb-3 align-items-center">
        <div class="col-12 col-xl-9 col-lg-8">
            <form action="{{ route('almoxarifado.itens.index') }}" method="GET" id="formFiltrosItens">
                @if ($estoqueBaixo)
                    <input type="hidden" name="estoque_baixo" value="1">
                @endif
                <div class="form-row align-items-center">
                    {{-- Busca por texto --}}
                    <div class="col-12 col-md-4 mb-2 mb-md-0">
                        <div class="input-group">
                            <input type="text"
                                   name="busca"
                                   class="form-control text-dark"
                                   placeholder="Buscar item por nome..."
                                   value="{{ $busca ?? '' }}"
                                   aria-label="Buscar item">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit" title="Buscar">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Filtro por Categoria --}}
                    <div class="col-12 col-sm-6 col-md-3 mb-2 mb-md-0">
                        <select name="categoria_id" class="form-control text-dark" onchange="this.form.submit()">
                            <option value="">Todas as categorias</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ (string)$categoriaId === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por Status --}}
                    <div class="col-12 col-sm-6 col-md-2 mb-2 mb-md-0">
                        <select name="status" class="form-control text-dark" onchange="this.form.submit()">
                            <option value="">Todos os status</option>
                            <option value="1" {{ $status === '1' ? 'selected' : '' }}>Ativos</option>
                            <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inativos</option>
                        </select>
                    </div>

                    {{-- Botão / Filtro de Estoque Mínimo (Reposição) --}}
                    <div class="col-12 col-md-3 mb-2 mb-md-0">
                        @php
                            $toggleParams = array_merge(
                                request()->except(['page', 'estoque_baixo']),
                                $estoqueBaixo ? [] : ['estoque_baixo' => 1]
                            );
                        @endphp
                        <a href="{{ route('almoxarifado.itens.index', $toggleParams) }}"
                           class="btn btn-block {{ $estoqueBaixo ? 'btn-danger text-white' : ($totalEstoqueBaixo > 0 ? 'btn-outline-danger' : 'btn-outline-secondary') }}"
                           title="{{ $estoqueBaixo ? 'Clique para ver todos os itens' : 'Filtrar itens no limite ou abaixo do estoque mínimo' }}">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Estoque Mínimo
                            <span class="badge {{ $estoqueBaixo ? 'badge-light text-danger' : ($totalEstoqueBaixo > 0 ? 'badge-danger' : 'badge-secondary') }} ml-1">
                                {{ $totalEstoqueBaixo }}
                            </span>
                        </a>
                    </div>
                </div>

                @if (!empty($busca) || !empty($categoriaId) || ($status !== null && $status !== '') || $estoqueBaixo)
                    <div class="mt-2 d-flex align-items-center flex-wrap">
                        <a href="{{ route('almoxarifado.itens.index') }}" class="btn btn-outline-danger btn-sm mr-2 mb-1">
                            <i class="fas fa-times mr-1"></i> Limpar filtros
                        </a>
                        @if ($estoqueBaixo)
                            <span class="badge badge-danger px-2 py-1 mb-1">
                                <i class="fas fa-filter mr-1"></i> Exibindo apenas itens no limite ou abaixo do mínimo ({{ $itens->count() }})
                            </span>
                        @endif
                    </div>
                @endif
            </form>
        </div>

        {{-- Botão de Cadastro --}}
        <div class="col-12 col-xl-3 col-lg-4 text-lg-right mt-2 mt-lg-0">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCriarItem">
                <i class="fas fa-plus mr-1"></i> Novo Item
            </button>
        </div>
    </div>

    {{-- Card com a Tabela de Itens --}}
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h3 class="card-title text-bold mb-0">
                <i class="fas fa-boxes mr-2 text-success"></i>Itens Cadastrados ({{ $itens->count() }})
            </h3>
            @if ($estoqueBaixo)
                <div>
                    <span class="badge badge-danger px-2 py-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Apenas Estoque Mínimo / Reposição
                    </span>
                </div>
            @endif
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 70px;" class="text-center">#</th>
                        <th>Nome do Item</th>
                        <th>Categoria</th>
                        <th style="width: 100px;" class="text-center">Unidade</th>
                        <th style="width: 140px;" class="text-center">Estoque Físico</th>
                        <th style="width: 130px;" class="text-center">Estoque Mín.</th>
                        <th style="width: 110px;" class="text-center">Status</th>
                        <th style="width: 130px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($itens as $item)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $item->id }}</td>
                            <td class="font-weight-bold text-dark">
                                {{ $item->nome }}
                            </td>
                            <td>
                                <span class="badge badge-light border text-dark font-weight-normal px-2 py-1">
                                    <i class="fas fa-tag text-secondary mr-1"></i>{{ $item->categoria->nome ?? 'Sem categoria' }}
                                </span>
                            </td>
                            <td class="text-center font-weight-bold text-secondary">
                                {{ $item->unidade_medida }}
                            </td>
                            <td class="text-center">
                                @if ($item->is_estoque_baixo)
                                    <span class="badge badge-danger px-2 py-1" title="Estoque no limite ou abaixo do mínimo!">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $item->estoque_formatado }} {{ $item->unidade_medida }}
                                    </span>
                                @else
                                    <span class="badge badge-success px-2 py-1 font-weight-bold">
                                        {{ $item->estoque_formatado }} {{ $item->unidade_medida }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center text-muted">
                                {{ (float)$item->estoque_minimo > 0 ? $item->estoque_minimo . ' ' . $item->unidade_medida : '-' }}
                            </td>
                            <td class="text-center">
                                @if ($item->ativo)
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
                                <button type="button" class="btn btn-warning btn-sm" title="Editar item"
                                    data-toggle="modal" data-target="#modalEditarItem{{ $item->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" title="Excluir item"
                                    data-toggle="modal" data-target="#modalExcluirItem{{ $item->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Modais de Edição e Exclusão da linha --}}
                        @include('almoxarifado.itens.modals._edit', ['item' => $item, 'categorias' => $categorias])
                        @include('almoxarifado.itens.modals._delete', ['item' => $item])
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-dark">
                                    <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i>
                                    @if (!empty($busca) || !empty($categoriaId) || ($status !== null && $status !== '') || $estoqueBaixo)
                                        <h5 class="text-dark">Nenhum item encontrado com os filtros selecionados</h5>
                                        @if ($estoqueBaixo)
                                            <p class="text-secondary mb-3">Nenhum item está no limite ou abaixo do estoque mínimo cadastrado.</p>
                                        @else
                                            <p class="text-secondary mb-3">Tente ajustar a busca ou limpe os filtros para ver todos os itens.</p>
                                        @endif
                                        <a href="{{ route('almoxarifado.itens.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times mr-1"></i> Limpar filtros
                                        </a>
                                    @else
                                        <h5 class="text-dark">Nenhum item cadastrado no almoxarifado</h5>
                                        <p class="text-secondary mb-3">Cadastre produtos e materiais de consumo para iniciar o controle físico de estoque.</p>
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCriarItem">
                                            <i class="fas fa-plus mr-1"></i> Cadastrar Primeiro Item
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

    {{-- Modal de Cadastro --}}
    @include('almoxarifado.itens.modals._create', ['categorias' => $categorias])
@stop

@push('js')
<script>
    /**
     * Alterna entre unidade selecionada no dropdown e campo de texto personalizado caso "Outro" seja selecionado.
     */
    function tratarTrocaUnidade(selectEl, wrapperId, inputId) {
        var wrapper = document.getElementById(wrapperId);
        var input = document.getElementById(inputId);
        if (!wrapper || !input) return;

        if (selectEl.value === '__OUTRO__') {
            wrapper.style.display = 'block';
            selectEl.removeAttribute('name');
            input.setAttribute('name', 'unidade_medida');
            input.setAttribute('required', 'required');
            input.focus();
        } else {
            wrapper.style.display = 'none';
            selectEl.setAttribute('name', 'unidade_medida');
            input.removeAttribute('name');
            input.removeAttribute('required');
            input.value = '';
        }
    }

    $(document).ready(function () {
        // Desativa o botão de cadastro após o primeiro clique (prevenção de double submit)
        $('#formCriarItem').on('submit', function () {
            if (!this.checkValidity()) {
                return;
            }

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        });

        // Restaura o botão caso o modal de cadastro seja reaberto
        $('#modalCriarItem').on('show.bs.modal', function () {
            var $btn = $('#formCriarItem').find('button[type="submit"]');
            $btn.prop('disabled', false);
            $btn.html('<i class="fas fa-save mr-1"></i> Salvar Item');
        });

        // Aplica a mesma proteção para todos os formulários de edição
        $('form[id^="formEditarItem"]').on('submit', function () {
            if (!this.checkValidity()) {
                return;
            }

            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');
        });

        // Desativa o botão de exclusão após o primeiro clique
        $(document).on('submit', '.form-excluir-item', function (e) {
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
