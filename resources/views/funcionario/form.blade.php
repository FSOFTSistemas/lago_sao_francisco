@extends('adminlte::page')

@section('title', isset($funcionario) ? 'Atualizar Funcionário' : 'Novo Funcionário')

@section('content_header')
    <h4>{{ isset($funcionario) ? 'Atualizar funcionário' : 'Cadastrar Novo funcionário' }}</h4>
@stop

@section('content')
    <div class="card">
        <div class="card-header green text-white">
            <h3 class="card-title">
                {{ isset($funcionario) ? 'Preencha os dados atualizados' : 'Preencha os dados do novo Funcionário' }}</h3>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($funcionario) ? route('funcionario.update', $funcionario->id) : route('funcionario.store') }}"
                method="POST">
                @csrf
                @if (isset($funcionario))
                    @method('PUT')
                @endif
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="nome">* Nome:</label>
                    <div class="col-md-7">
                        <input type="text" class="form-control" id="nome" name="nome" required
                            value="{{ $funcionario->nome ?? '' }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="cpf"> CPF:</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="cpf" name="cpf"
                            value="{{ $funcionario->cpf ?? '' }}">
                    </div>

                    <label class="col-md-1 label-control" for="salario"> Salário:</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="salario" name="salario"
                            value="{{ $funcionario->salario ?? '' }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="dataContratacao">* Data de Contratação:</label>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dataContratacao" name="data_contratacao" required
                            value="{{ $funcionario->data_contratacao ?? '' }}">
                    </div>

                    <label class="col-md-1 label-control" for="empresa">* Empresa:</label>
                    <div class="col-md-3">
                        <select class="form-control" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}"
                                    {{ old('empresa_id', $funcionario->empresa_id ?? '') == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 label-control" for="setor">* Setor:</label>
                    <div class="col-md-3">
                        <select class="form-control" id="setor" name="setor" required>
                            <option value="">Selecione</option>
                            @foreach ($setores as $setor)
                                <option value="{{ $setor }}"
                                    {{ old('setor', $funcionario->setor ?? '') == $setor ? 'selected' : '' }}>
                                    {{ $setor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label class="col-md-1 label-control" for="cargo">* Cargo:</label>
                    <div class="col-md-3">
                        <select class="form-control" id="cargo" name="cargo" required>
                            <option value="">Selecione</option>
                            @foreach ($cargos as $cargo)
                                <option value="{{ $cargo }}"
                                    {{ old('cargo', $funcionario->cargo ?? '') == $cargo ? 'selected' : '' }}>
                                    {{ $cargo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="" class="col-md-3 label-control">Função:</label>
                    <div class="col-md-4">
                        <label for="vendedor{{ $funcionario->id ?? '' }}"
                            class="form-label label-control col-md-2">Vendedor</label>

                        <label class="switch-slide mb-0">
                            <input type="hidden" name="vendedor" value="0">
                            <input type="checkbox" id="vendedor{{ $funcionario->id ?? '' }}" value="1" name="vendedor"
                                @checked(old('vendedor', isset($funcionario) ? $funcionario->vendedor : false))>
                            <span class="slider-slide"></span>
                        </label>

                        <label for="caixa{{ $funcionario->id ?? '' }}"
                            class="form-label label-control col-md-2">Caixa</label>

                        <label class="switch-slide mb-0">
                            <input type="hidden" name="caixa" value="0">
                            <input class="caixacheck" type="checkbox" id="caixa{{ $funcionario->id ?? '' }}" value="1"
                                name="caixa" @checked(old('caixa', isset($funcionario) ? $funcionario->caixa : false))>
                            <span class="slider-slide"></span>
                        </label>
                    </div>
                </div>



                <div class="form-group row" id="senha">
                    <label for="senha_supervisor" class="col-md-3 label-control">Senha de Supervisor</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="senha_supervisor" id="senha_supervisor"
                            placeholder="{{ isset($funcionario) && $funcionario->senha_supervisor ? 'Deixe em branco para manter a senha' : 'Criar Senha' }}">
                        <span id="erro-senha" class="text-danger d-none">As senhas não coincidem.</span>
                    </div>
                    <label for="senha_supervisor_confirm" class="col-md-1 label-control">Confirme a Senha</label>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="senha_supervisor_confirm"
                            id="senha_supervisor_confirm" placeholder="Confirme sua senha">
                        <span id="erro-senha" class="text-danger d-none">As senhas não coincidem.</span>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="form-group row">
                    <label class="col-md-3 label-control" for="endereco_id">Endereço:</label>
                    <div class="col-md-3">
                        <select class="form-control select2" id="endereco_id" name="endereco_id">
                            <option value="">Selecione</option>
                            @foreach ($enderecos as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('endereco_id', $funcionario->endereco_id ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->logradouro }}, {{ $item->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enderecoModal">
                        <i class="fas fa-plus"></i> Novo Endereço
                    </button>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 form-label d-block label-control"> Ativo?</label>
                    <div class="form-check form-switch">
                        <input type="hidden" name="status" value="inativo">
                        <input class="form-check-input" type="checkbox" id="ativoSwitch" name="status" value="ativo"
                            {{ old('status', $funcionario->status ?? 'ativo') === 'ativo' ? 'checked' : '' }}>
                        <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                            {{ old('status', $funcionario->status ?? 'ativo') === 'ativo' ? 'Ativo' : 'Inativo' }}
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 form-label d-block label-control">
                        Criar também um usuário para este funcionário
                    </label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="criar_usuario" name="criar_usuario"
                            value="1">
                    </div>
                </div>

                <div id="dados_usuario" style="display: none;">
                    <div class="form-group row">
                        <label class="label-control col-md-3">Email</label>
                        <div class="col-md-4">
                            <input type="email" name="email" id="email" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="label-control col-md-3">Senha Gerada</label>
                        <div class="col-md-4">
                            <input type="text" name="senha_visivel" id="senha_visivel" class="form-control" readonly>
                            <input type="hidden" name="password" id="password">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="label-control col-md-3">Tipo de Usuário</label>
                        <div class="col-md-3">
                            <select name="role" class="form-control">
                                <option value="">Selecione um tipo</option>
                                @foreach ($roles as $role)
                                    @php
                                        $label = match ($role->name) {
                                            'Master' => 'Financeiro Geral',
                                            'financeiro' => 'Financeiro Empresa',
                                            'funcionario' => 'Funcionario',
                                            default => ucfirst($role->name),
                                        };
                                    @endphp
                                    <option value="{{ $role->name }}"
                                        {{ isset($user) && ($user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="permissoes"
                            class="col-md-3 col-form-label text-md-right font-weight-bold">Permissões:</label>
                        <div class="col-md-7">
                            <div class="permissoes-select-container">
                                <!-- Select2 -->
                                <select class="form-control select2-permissoes" id="permissoes" name="permissoes[]"
                                    multiple="multiple" style="width: 100%;">
                                    @php
                                        $selectedpermissoes =
                                            isset($usuario) && $usuario->permissoes
                                                ? $usuario->permissoes->pluck('id')->toArray()
                                                : [];
                                    @endphp
                                    @foreach ($permissoes as $permissao)
                                        @if ($permissao->name !== 'gerenciar fluxo de caixa')
                                            <option value="{{ $permissao->id }}"
                                                {{ in_array($permissao->id, $selectedpermissoes) ? 'selected' : '' }}>
                                                {{ $permissao->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                                <!-- Botões de Controle -->
                                <div class="permissions-control-buttons mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary select-all-btn">
                                        <i class="fas fa-check-double mr-1"></i> Selecionar todas
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary deselect-all-btn ml-2">
                                        <i class="fas fa-times-circle mr-1"></i> Limpar seleção
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Botão de Salvar -->
                <div class="card-footer">
                    <a href="{{ route('funcionario.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit"
                        class="btn green">{{ isset($funcionario) ? 'Atualizar Funcionário' : 'Adicionar Funcionário ' }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Novo Endereço -->
    @include('components.endereco-modal')
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#cpf').mask('000.000.000-00');
        });
    </script>
    <script src="{{ asset('js/endereco.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switchInput = document.getElementById('ativoSwitch');
            const label = document.getElementById('ativoLabel');
            label.textContent = switchInput.checked ? 'Ativo' : 'Inativo';
            switchInput.value = switchInput.checked ? 'ativo' : 'inativo';
            switchInput.addEventListener('change', function() {
                label.textContent = this.checked ? 'Ativo' : 'Inativo';
                this.value = this.checked ? 'ativo' : 'inativo';
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "selecione...",
                allowClear: true,
                width: '100%'
            });

            $('form').on('submit', function(e) {
                const senha = $('#senha_supervisor').val();
                const confirmar = $('#senha_supervisor_confirm').val();

                if (senha !== confirmar) {
                    e.preventDefault(); // impede envio do form
                    $('#erro-senha').removeClass('d-none');
                } else {
                    $('#erro-senha').addClass('d-none');
                }
            });


        });

        function mostrarCampo() {
            let setor = $('#setor').val()
            if (setor == 'Gerência') {
                $('#senha').show();
            } else {
                $('#senha').hide();
                $('#senha').removeAttr('required');
            }
        }
        mostrarCampo()

        $('#setor').change(function() {
            mostrarCampo();
        });
    </script>
    <script>
        $(document).ready(function() {
            // Inicializa o Select2 com configurações aprimoradas
            $('.select2-permissoes').select2({
                placeholder: "Selecione as permissões...",
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            // Selecionar todas
            $('.select-all-btn').click(function() {
                $('.select2-permissoes option').prop('selected', true);
                $('.select2-permissoes').trigger('change');
            });

            // Desmarcar todas
            $('.deselect-all-btn').click(function() {
                $('.select2-permissoes option').prop('selected', false);
                $('.select2-permissoes').trigger('change');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkbox = document.getElementById('criar_usuario');
            const container = document.getElementById('dados_usuario');
            const nomeInput = document.querySelector('input[name="nome"]');
            const emailInput = document.getElementById('email');
            const senhaInput = document.getElementById('senha_visivel');
            const passwordHidden = document.getElementById('password');
            const caixa = document.querySelector('.caixacheck')

            function gerarEmail(nome) {
                if (!nome) return '';
                const partes = nome.trim().toLowerCase().split(' ');
                const primeiro = partes[0];
                const ultimo = partes[partes.length - 1];
                const random = Math.floor(100 + Math.random() * 900);
                return `${primeiro}${ultimo}${random}@lago.com`;
            }

            function gerarSenha() {
                const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                let senha = '';
                for (let i = 0; i < 8; i++) {
                    senha += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return senha;
            }

            caixa.addEventListener('change', () => {
                if (caixa.checked) {
                    checkbox.checked = true;
                    const evento = new Event('change', {
                        bubbles: true
                    });
                    checkbox.dispatchEvent(evento);

                }
            });

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    container.style.display = 'block';
                    const nome = nomeInput.value;
                    emailInput.value = gerarEmail(nome);
                    const senha = gerarSenha();
                    senhaInput.value = senha;
                    passwordHidden.value = senha;
                } else {
                    container.style.display = 'none';
                    emailInput.value = '';
                    senhaInput.value = '';
                    passwordHidden.value = '';
                }
            });
        });
    </script>


@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .switch-slide {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch-slide input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-slide {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider-slide:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .switch-slide input:checked+.slider-slide {
            background-color: var(--green-1);
        }

        .switch-slide input:checked+.slider-slide:before {
            transform: translateX(24px);
        }

        /* Área de seleção principal - Fundo branco com borda verde */
        .select2-selection--multiple {
            min-height: 38px !important;
            border: 1px solid #679A4C !important;
            border-radius: 4px !important;
            background-color: white !important;
            /* Fundo branco */
            color: #495057 !important;
            /* Texto preto padrão */
            padding: 0 5px !important;
        }

        /* Tags dos itens selecionados - Fundo verde com texto branco */
        .select2-selection--multiple .select2-selection__choice {
            background-color: #679A4C !important;
            border-color: #55853a !important;
            color: white !important;
            padding: 0 8px;
            border-radius: 12px;
            margin-top: 5px;
        }

        /* Texto do placeholder */
        .select2-selection--multiple .select2-search__field {
            color: #495057 !important;
        }

        .select2-selection--multiple .select2-search__field::placeholder {
            color: #6c757d !important;
        }

        /* Botão de remover item (mantém branco) */
        .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.7) !important;
            margin-right: 4px;
        }

        .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white !important;
        }
    </style>
@endsection
