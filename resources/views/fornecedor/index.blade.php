@extends('adminlte::page')

@section('title', 'Fornecedor')

@section('content_header')
    <h5>Lista de Fornecedores</h5>
    <hr>
@stop

@section('content')
<div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success new" data-toggle="modal" data-target="#createFornecedorModal">
            <i class="fas fa-plus"></i> Novo Fornecedor
        </button>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Razão social</th>
                    <th>Nome fantasia</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fornecedores as $fornecedor)
                    <tr>
                        <td>{{ $fornecedor->id }}</td>
                        <td>{{ $fornecedor->razao_social }}</td>
                        <td>{{ $fornecedor->nome_fantasia }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                data-target="#showFornecedor{{ $fornecedor->id }}">
                                👁️
                            </button>

                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                data-target="#editFornecedorModal{{ $fornecedor->id }}">
                                ✏️
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteFornecedorModal{{ $fornecedor->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>

                    @include('fornecedor.modals._show', ['fornecedor' => $fornecedor])
                    @include('fornecedor.modals._edit', ['fornecedor' => $fornecedor])
                    @include('fornecedor.modals._delete', ['fornecedor' => $fornecedor])
                @endforeach
            </tbody>
    @endcomponent
    @include('fornecedor.modals._create')
@stop

@push('js')
    {{-- O AdminLTE já carrega o jQuery. Carregamos apenas o plugin da máscara. --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script src="{{ asset('js/buscarCnpj.js') }}"></script>
    
<script>
        $(document).ready(function() {
            // LOG 1: Garante que o script está rodando.
            console.log('Script da máscara iniciado e pronto.');

            function inicializarMascara(campo) {
                if (!campo || campo.length === 0) {
                    console.warn('Alvo para máscara não encontrado.');
                    return;
                }
                if (typeof $.fn.mask !== 'function') {
                    console.error('O plugin jQuery Mask não foi carregado.');
                    return;
                }

                var mascaraDinamica = function (val) {
                    return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
                };
                var opcoesMascara = {
                    onKeyPress: function (val, e, field, options) {
                        field.mask(mascaraDinamica.apply({}, arguments), options);
                    }
                };

                campo.unmask().mask(mascaraDinamica, opcoesMascara);

                var toggleSearchButton = function () {
                    var input = $(this);
                    var valorSemMascara = input.val().replace(/\D/g, '');
                    var botaoBuscar = input.closest('.input-group').find('.btn-buscar-doc');
                    valorSemMascara.length > 11 ? botaoBuscar.show() : botaoBuscar.hide();
                };

                campo.off('input.dynamicMask').on('input.dynamicMask', toggleSearchButton).trigger('input.dynamicMask');
                console.log('Máscara INICIALIZADA no campo:', campo.get(0));
            }

            // ABORDAGEM ALTERNATIVA: Reage ao clique no botão que abre o modal.
            // É mais direto e menos suscetível a conflitos de eventos.
            $('button[data-toggle="modal"]').on('click', function() {
                // Pega o ID do modal que será aberto a partir do atributo 'data-target' do botão.
                var modalId = $(this).data('target');
                console.log('Botão clicado para abrir o modal:', modalId);

                // Como o modal leva um tempo para aparecer, usamos um pequeno delay.
                // Isso garante que o campo já exista no DOM quando tentarmos selecioná-lo.
                setTimeout(function() {
                    // Seleciona o campo DENTRO do modal que foi aberto.
                    var campo = $(modalId).find('.cnpj-cpf-field');
                    
                    if (campo.length > 0) {
                        console.log('Campo encontrado via clique! Aplicando máscara...');
                        inicializarMascara(campo);
                    } else {
                        console.warn('Campo não encontrado no modal ' + modalId + ' após o clique.');
                    }
                }, 500); // 500ms é um tempo seguro para a animação do modal.
            });
        });
    </script>
@endpush

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
@endsection

