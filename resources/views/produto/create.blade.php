@extends('adminlte::page')

@section('title', isset($produto) ? 'Editar Produto' : 'Novo Produto')

@section('content_header')
    <h4>{{ isset($produto) ? 'Editar produto' : 'Cadastrar Novo produto' }}</h4>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                {{ isset($produto) ? 'Editar informações do Produto' : 'Preencha os dados do novo Produto' }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ isset($produto) ? route('produto.update', $produto->id) : route('produto.store') }}"
                method="POST">
                @csrf
                @if (isset($produto))
                    @method('PUT')
                @endif
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="nome" required value="{{ $produto->descricao ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tipo">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ean">EAN:</label>
                        <input type="text" class="form-control" id="ean" name="ean" required value="{{ $produto->ean ?? '' }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tipo">Situação:</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                        
                    <div class="col-md-6 mb-3">
                        <label for="precoCusto">Preço de Custo:</label>
                        <input type="text" class="form-control" id="precoCusto" name="preco_custo" required value="{{ $produto->preco_custo ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="precoVenda">Preço de Venda:</label>
                        <input type="text" class="form-control" id="precoVenda" name="preco_venda" required value="{{ $produto->preco_venda ?? '' }}">
                    </div>
                  </div>
                  

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ncm">NCM:</label>
                        <input type="text" class="form-control" id="ncm" name="ncm" required value="{{ $produto->ncm ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cst">CST:</label>
                        <input type="text" class="form-control" id="cst" name="cst" required value="{{ $produto->cst ?? '' }}">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cfopInterno">CFOP Interno:</label>
                        <input type="text" class="form-control" id="cfopInterno" name="cfop_interno" required value="{{ $produto->cfop_interno ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cfopExterno">CFOP Externo:</label>
                        <input type="text" class="form-control" id="cfopExterno" name="cfop_externo" required value="{{ $produto->cfop_externo ?? '' }}">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="aliquota">Alíquota:</label>
                        <input type="text" class="form-control" id="aliquota" name="aliquota" required value="{{ $produto->aliquota ?? '' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="csosn">CSOSN:</label>
                        <input type="text" class="form-control" id="csosn" name="csosn" required value="{{ $produto->csosn ?? '' }}">
                    </div>
                  </div>

                <!-- Botão de Salvar -->
                <div class="form-group col-md-12">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> {{ isset($produto) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
