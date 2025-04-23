@extends('adminlte::page')

@section('title', 'Empresas')

@section('content_header')
    <h5>Empresas</h5>
    <hr>
@stop

@section('content')
    <ul class="nav nav-tabs" id="empresaTabs" role="tablist">
        @foreach ($empresas as $index => $empresa)
            <li class="nav-item" role="presentation">
                <a class="nav-link editlink {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $empresa->id }}" data-toggle="tab" href="#empresa-{{ $empresa->id }}" role="tab">
                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="empresaTabsContent">
        @foreach ($empresas as $index => $empresa)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="empresa-{{ $empresa->id }}" role="tabpanel">
                <form action="{{ route('empresa.update', $empresa->id) }}" method="POST" class="empresa-form">
                    @csrf
                    @method('PUT')
                    <div class="card">
                      <div class="card-body mt-3">

                        <div class="form-group row">
                            <label class="col-md-3 label-control">Razão Social:</label>
                            <div class="col-md-3">
                              <input type="text" name="razao_social" value="{{ $empresa->razao_social }}" class="form-control" readonly>
                            </div>
                        </div>
    
                        <div class="form-group row">
                            <label class="col-md-3 label-control">Nome Fantasia:</label>
                            <div class="col-md-3">
                              <input type="text" name="nome_fantasia" value="{{ $empresa->nome_fantasia }}" class="form-control" readonly>
                            </div>
                        </div>
    
                        <div class="form-group row">
                            <label class="col-md-3 label-control">CNPJ:</label>
                            <div class="col-md-3">
                              <input type="text" name="cnpj" value="{{ $empresa->cnpj }}" class="form-control" readonly>
                            </div>
                        </div>
    
                        <div class="form-group row">
                            <label class="col-md-3 label-control">Inscrição Estadual:</label>
                            <div class="col-md-3">
                              <input type="text" name="inscricao_estadual" value="{{ $empresa->inscricao_estadual }}" class="form-control" readonly>
                            </div>
                        </div>
    
                        {{-- <div class="form-group row">
                            <label class="col-md-3 label-control">Endereço</label>
                            <div class="col-md-3">
                              <input type="text" name="endereco" value="{{ $empresa->endereco }}" class="form-control" readonly>
                            </div>
                        </div> --}}
                        
                        <div class="card-footer text-end">
                          <button type="button" class="btn btn-warning btn-edit mt-2">Editar</button>
                          <button type="submit" class="btn new btn-success mt-2 d-none btn-save">Salvar</button>
                        </div>
                      </div>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
@stop

@section('js')
<script>
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('form');
            form.querySelectorAll('input').forEach(input => input.removeAttribute('readonly'));
            form.querySelector('.btn-save').classList.remove('d-none');
            this.classList.add('d-none');
        });
    });
</script>
@stop
