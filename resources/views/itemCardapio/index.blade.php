@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createItemModal">
        <i class="fas fa-plus"></i> Novo Item
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
    'valueColumnIndex' => 1,

])
    <thead class="bg-primary text-white">
        <tr>
            <th>ID</th>
            <th>Nome do Item</th>
            <th>Tipo do Item</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($itens as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->nome_item }}</td>
                <td>{{ $item->tipo_item }}</td>
                <td>
                    <button type="button" class="btn btn-warning btn-sm edit-item" 
                            data-toggle="modal" data-target="#editItemModal{{ $item->id }}">
                        <i class="fas fa-edit"></i>
                    </button>

                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                        data-target="#deleteItemModal{{ $item->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>

        
            @include('itemCardapio.modals.edit', ['item' => $item])
            @include('itemCardapio.modals.delete', ['item' => $item])
        @endforeach
    </tbody>
@endcomponent

<!-- Modal de criação (apenas um, fora do loop) -->
 @include('itemCardapio.modals.create')
@stop

@section('css')
<style>
    .modal-content {
        border-radius: 0.5rem;
    }
    .form-control:focus {
        border-color: #679A4C;
        box-shadow: 0 0 0 0.2rem rgba(103, 154, 76, 0.25);
    }
    .required-field::after {
        content: " *";
        color: red;
    }
</style>
@stop

@section('js')
    <script> 
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
$(document).ready(function() {
    // Configuração do Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": 5000
    };

    // Submissão do formulário de edição via AJAX
    $('.edit-item-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.html();
        var modalId = form.closest('.modal').attr('id');
        
        // Feedback visual durante o carregamento
        submitButton.prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin"></i> Salvando...'
        );
        
        // Timeout para evitar travamento
        var ajaxTimeout = setTimeout(function() {
            toastr.warning('A operação está demorando mais que o normal...');
        }, 5000);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                clearTimeout(ajaxTimeout);
                
                // Fecha o modal específico
                $('#'+modalId).modal('hide');
                
                // Mostra notificação de sucesso
                toastr.success('Item atualizado com sucesso!');
                
                // Recarrega a página após 1 segundo
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                clearTimeout(ajaxTimeout);
                
                var errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error('Erro ao atualizar item: ' + 
                        (xhr.responseJSON?.message || 'Erro desconhecido'));
                }
                
                // Reativa o botão
                submitButton.prop('disabled', false).html(originalText);
            }
        });
    });
});
    </script>
@stop

