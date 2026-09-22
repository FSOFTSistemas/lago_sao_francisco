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
                        <form action="{{ route('empresa.update', $empresa->id) }}" method="POST" class="empresa-form">
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
                        {{-- {{ route('empresaPreferencia.update', $empresa->id) }} --}}
                        <form action="{{ route('empresaPreferencia.update', $empresa->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @php
                                $preferencia = $preferencias->firstWhere('empresa_id', $empresa->id);
                                $certInfo = $preferencia?->getCertificadoInfo();
                            @endphp
                            <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                            <div class="card">
                                <div class="card-body mt-3">
                                    {{-- Status do Certificado Atual --}}
                                    @if ($certInfo)
                                        @if (!empty($certInfo['erro']))
                                            <div class="alert alert-warning py-2 text-sm mb-3">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <strong>Aviso de Certificado:</strong> {{ $certInfo['erro'] }} (Verifique se a senha foi informada corretamente).
                                            </div>
                                        @elseif ($certInfo['expirado'])
                                            <div class="alert alert-danger py-2 text-sm mb-3">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                <strong>Certificado Digital VENCIDO:</strong> Expirou em {{ $certInfo['valido_ate']->format('d/m/Y H:i') }}. Por favor, anexe um novo arquivo .pfx.
                                            </div>
                                        @elseif ($certInfo['dias_restantes'] <= 30)
                                            <div class="alert alert-warning py-2 text-sm mb-3">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                <strong>Certificado a Vencer:</strong> Válido até {{ $certInfo['valido_ate']->format('d/m/Y H:i') }} (Restam apenas {{ $certInfo['dias_restantes'] }} dias). Titular: {{ $certInfo['titular'] }} ({{ $certInfo['cnpj'] }}).
                                            </div>
                                        @else
                                            <div class="alert alert-success py-2 text-sm mb-3">
                                                <i class="fas fa-certificate mr-1"></i>
                                                <strong>Certificado Digital Ativo:</strong> Válido até {{ $certInfo['valido_ate']->format('d/m/Y H:i') }} ({{ $certInfo['dias_restantes'] }} dias restantes). Titular: {{ $certInfo['titular'] }} ({{ $certInfo['cnpj'] }}).
                                            </div>
                                        @endif
                                    @else
                                        <div class="alert alert-light border py-2 text-sm mb-3">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Nenhum Certificado Digital A1 (.pfx) configurado para esta empresa. Anexe o arquivo abaixo para habilitar o DF-e e a emissão de notas.
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Arquivo do Certificado Digital A1 (.pfx):</label>
                                            <input type="file" name="certificado_digital" class="form-control" accept=".pfx,.p12">
                                            <small class="text-muted">Selecione o arquivo .pfx fornecido pela autoridade certificadora.</small>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Senha do Certificado Digital:</label>
                                            <input type="password" name="senha_certificado" class="form-control" placeholder="{{ !empty($preferencia?->senha_certificado) ? '•••••••• (Preencha apenas para alterar)' : 'Digite a senha do certificado' }}">
                                            <small class="text-muted">A senha é criptografada e usada apenas para assinar notas e consultar a SEFAZ.</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label>Ambiente de Emissão / DF-e:</label>
                                            <select name="ambiente_dfe" class="form-control">
                                                <option value="1" {{ ($preferencia?->ambiente_dfe ?? 1) == 1 ? 'selected' : '' }}>1 - Produção (Ambiente Oficial da SEFAZ)</option>
                                                <option value="2" {{ ($preferencia?->ambiente_dfe ?? 1) == 2 ? 'selected' : '' }}>2 - Homologação (Ambiente de Testes)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label>Regime Tributário:</label>
                                            <select name="regime_tributario" class="form-control">
                                                <option value="Simples Nacional" {{ old('regime_tributario', $preferencia?->regime_tributario) == 'Simples Nacional' ? 'selected' : '' }}>Simples Nacional</option>
                                                <option value="Lucro Presumido" {{ old('regime_tributario', $preferencia?->regime_tributario) == 'Lucro Presumido' ? 'selected' : '' }}>Lucro Presumido</option>
                                                <option value="Lucro Real" {{ old('regime_tributario', $preferencia?->regime_tributario) == 'Lucro Real' ? 'selected' : '' }}>Lucro Real</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label>Número da Última Nota Emitida:</label>
                                            <input type="number" name="numero_ultima_nota" class="form-control" value="{{ old('numero_ultima_nota', $preferencia?->numero_ultima_nota) }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Série:</label>
                                            <input type="text" name="serie" class="form-control" value="{{ old('serie', $preferencia?->serie) }}">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>CFOP Padrão:</label>
                                            <input type="text" name="cfop_padrao" class="form-control" value="{{ old('cfop_padrao', $preferencia?->cfop_padrao) }}">
                                        </div>
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
                        {{-- {{ route('empresaRT.update', $empresa->id) }} --}}
                        <form action="{{ route('empresaRT.update', $empresa->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body mt-3">
                                    <div class="form-group">
                                        <label>Nome:</label>
                                        <input type="text" name="resp_tecnico_nome" class="form-control" value="{{ old('resp_tecnico_nome', $empresa->responsavelTecnico->nome ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>CNPJ:</label>
                                        <input type="text" name="resp_tecnico_cnpj" class="form-control" value="{{ old('resp_tecnico_cnpj', $empresa->responsavelTecnico->cnpj) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="text" name="resp_tecnico_telefone" class="form-control" value="{{ old('resp_tecnico_telefone', $empresa->responsavelTecnico->telefone) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" name="resp_tecnico_email" class="form-control" value="{{ old('resp_tecnico_email', $empresa->responsavelTecnico->email) }}">
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
                        {{-- {{  route('empresaContador.update', $empresa->id)  }} --}}
                        <form action="{{  route('empresaContador.update', $empresa->id)  }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body mt-3">
                                    <div class="form-group">
                                        <label>Nome:</label>
                                        <input type="text" name="contador_nome" class="form-control" value="{{ old('contador_nome', $empresa->contador->nome ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>CNPJ:</label>
                                        <input type="text" name="contador_cnpj" class="form-control" value="{{old('contador_cnpj', $empresa->contador->cnpj ?? '')  }}">
                                    </div>
                                    <div class="form-group">
                                        <label>CRC:</label>
                                        <input type="text" name="contador_crc" class="form-control" value="{{ old('contador_crc' , $empresa->contador->crc )}}">
                                    </div>
                                    <div class="form-group">
                                        <label>Email:</label>
                                        <input type="email" name="contador_email" class="form-control" value="{{ old('contador_email', $empresa->contador->email ?? '') }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Telefone:</label>
                                        <input type="text" name="contador_telefone" class="form-control" value="{{ old('contador_telefone', $empresa->contador->telefone ?? '') }}">
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
