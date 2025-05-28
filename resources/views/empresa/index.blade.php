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
                <a class="nav-link editlink {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $empresa->id }}" data-toggle="tab"
                    href="#empresa-{{ $empresa->id }}" role="tab">
                    {{ $empresa->nome_fantasia ?? $empresa->razao_social }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="empresaTabsContent">
        @foreach ($empresas as $index => $empresa)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="empresa-{{ $empresa->id }}"
                role="tabpanel">
                <!-- Abas internas -->
                <ul class="nav nav-tabs mt-3" id="empresaInnerTabs-{{ $empresa->id }}" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="info-tab-{{ $empresa->id }}" data-toggle="tab"
                            href="#info-{{ $empresa->id }}" role="tab">Informações</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="preferencias-tab-{{ $empresa->id }}" data-toggle="tab"
                            href="#preferencias-{{ $empresa->id }}" role="tab">Preferências</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="resp-tecnico-tab-{{ $empresa->id }}" data-toggle="tab"
                            href="#resp-tecnico-{{ $empresa->id }}" role="tab">Responsável Técnico</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="contador-tab-{{ $empresa->id }}" data-toggle="tab"
                            href="#contador-{{ $empresa->id }}" role="tab">Contador</a>
                    </li>
                </ul>
                <div class="tab-content mt-2" id="empresaInnerTabsContent-{{ $empresa->id }}">
                    <div class="tab-pane fade show active" id="info-{{ $empresa->id }}" role="tabpanel">
                        {{-- {{ route('empresa.update', $empresa->id) }} --}}
                        <form action="#" method="POST" class="empresa-form">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body mt-3">
                                    <div class="form-group row">
                                        <label class="col-md-3 label-control">Razão Social:</label>
                                        <div class="col-md-3">
                                            <input type="text" name="razao_social" value="{{ $empresa->razao_social }}"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 label-control">Nome Fantasia:</label>
                                        <div class="col-md-3">
                                            <input type="text" name="nome_fantasia"
                                                value="{{ $empresa->nome_fantasia }}" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 label-control">CNPJ:</label>
                                        <div class="col-md-3">
                                            <input type="text" name="cnpj" value="{{ $empresa->cnpj }}"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-3 label-control">Inscrição Estadual:</label>
                                        <div class="col-md-3">
                                            <input type="text" name="inscricao_estadual"
                                                value="{{ $empresa->inscricao_estadual }}" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button type="button" class="btn btn-warning btn-edit mt-2">Editar</button>
                                        <button type="submit"
                                            class="btn new btn-success mt-2 d-none btn-save">Salvar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="preferencias-{{ $empresa->id }}" role="tabpanel">
                        {{-- {{ route('empresa.updatePreferencias', $empresa->id) }} --}}
                        <form action="#" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body mt-3">
                                    <div class="form-group">
                                        <label>Certificado Digital:</label>
                                        <input type="file" name="certificado_digital" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Número da Última Nota:</label>
                                        <input type="number" name="ultima_nota" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Série:</label>
                                        <input type="text" name="serie" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>CFOP Padrão:</label>
                                        <input type="text" name="cfop_padrao" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Regime Tributário:</label>
                                        <select name="regime_tributario" class="form-control">
                                            <option value="1">Simples Nacional</option>
                                            <option value="2">Lucro Presumido</option>
                                            <option value="3">Lucro Real</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="button" class="btn btn-warning btn-edit mt-2">Editar</button>
                                    <button type="submit"
                                        class="btn new btn-success mt-2 d-none btn-save">Salvar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="resp-tecnico-{{ $empresa->id }}" role="tabpanel">
                        {{-- {{ route('empresa.updateResponsavelTecnico', $empresa->id) }} --}}
                        <form action="#" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body mt-3">
                                    <div class="form-group">
                                        <label>Nome:</label>
                                        <input type="text" name="resp_tecnico_nome" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>CNPJ:</label>
                                        <input type="text" name="resp_tecnico_cnpj" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>CPF:</label>
                                        <input type="text" name="resp_tecnico_cpf" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="text" name="resp_tecnico_telefone" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" name="resp_tecnico_email" class="form-control">
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="button" class="btn btn-warning btn-edit mt-2">Editar</button>
                                    <button type="submit"
                                        class="btn new btn-success mt-2 d-none btn-save">Salvar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="contador-{{ $empresa->id }}" role="tabpanel">
                        {{-- {{  route('empresa.updateContador', $empresa->id)  }} --}}
                        <form action="#" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body mt-3">
                                    <div class="form-group">
                                        <label>Nome:</label>
                                        <input type="text" name="contador_nome" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>CPF:</label>
                                        <input type="text" name="contador_cpf" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>CNPJ:</label>
                                        <input type="text" name="contador_cnpj" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>CRC:</label>
                                        <input type="text" name="contador_crc" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" name="contador_email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="text" name="contador_telefone" class="form-control">
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="button" class="btn btn-warning btn-edit mt-2">Editar</button>
                                    <button type="submit"
                                        class="btn new btn-success mt-2 d-none btn-save">Salvar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

@section('js')
    <script>
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                form.querySelectorAll('input').forEach(input => input.removeAttribute('readonly'));
                form.querySelector('.btn-save').classList.remove('d-none');
                this.classList.add('d-none');
            });
        });
    </script>
@stop
