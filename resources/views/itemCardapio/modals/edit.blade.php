@extends('adminlte::page')

@section('title', 'Editar Item do Cardápio')

@section('content_header')
    <h5>Editar Item: {{ $item->nome_item }}</h5>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('itens-do-cardapio.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="nome_item">Nome do Item</label>
                            <input type="text" name="nome_item" id="nome_item" 
                                   class="form-control @error('nome_item') is-invalid @enderror" 
                                   value="{{ old('nome_item', $item->nome_item) }}" required>
                            @error('nome_item')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="tipo_item">Tipo do Item</label>
                            <select name="tipo_item" id="tipo_item" 
                                    class="form-control @error('tipo_item') is-invalid @enderror" required>
                                <option value="">Selecione o tipo</option>
                                <option value="Entrada" {{ old('tipo_item', $item->tipo_item) == 'Entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="Prato Principal" {{ old('tipo_item', $item->tipo_item) == 'Prato Principal' ? 'selected' : '' }}>Prato Principal</option>
                                <option value="Sobremesa" {{ old('tipo_item', $item->tipo_item) == 'Sobremesa' ? 'selected' : '' }}>Sobremesa</option>
                                <option value="Bebida" {{ old('tipo_item', $item->tipo_item) == 'Bebida' ? 'selected' : '' }}>Bebida</option>
                            </select>
                            @error('tipo_item')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <a href="{{ route('itens-do-cardapio.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-control:focus {
            border-color: #679A4C;
            box-shadow: 0 0 0 0.2rem rgba(103, 154, 76, 0.25);
        }
    </style>
@stop

@section('js')
    <script>
        // Você pode adicionar scripts JS específicos para esta página aqui
        $(document).ready(function() {
            // Exemplo: Máscaras ou validações adicionais
            console.log('Página de edição carregada');
        });
    </script>
@stop