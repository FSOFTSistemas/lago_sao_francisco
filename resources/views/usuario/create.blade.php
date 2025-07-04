@extends('adminlte::page')

@section('title', isset($user) ? 'Editar Usuário' : 'Cadastrar Usuário')

@section('content_header')
    <h1>{{ isset($user) ? 'Editar Usuário' : 'Cadastrar Usuário' }}</h1>
    <hr>
@stop

@section('content')
    <div class="alert alert-secondary">
        <strong>DICA:</strong> As permissões é o que define quais seções um usuário pode ou não ver/acessar<br>
        <em><strong>Financeiro Geral:</strong> tem acesso a todas as empresas e pode escolher, <strong>Finaceiro
                Empresa:</strong> está limitado a empresa de usuário dele, <strong>Funcionário:</strong> só pode ver dados
            do seu próprio usuário</em>
    </div>
    <div class="card">
        <div class="card-header green bg-primary text-white">
            <h3 class="card-title">
                {{ isset($user) ? 'Editar informações do Usuário' : 'Preencha os dados do novo Usuário' }}</h3>
        </div>
        <div class="card-body">
            <form id="createUsuarioForm"
                action="{{ isset($user) ? route('usuarios.update', $user->id) : route('usuarios.store') }}" method="POST">
                @csrf
                @if (isset($user))
                    @method('PUT')
                @endif

                <div class="form-group row">
                    <label for="name" class="col-md-3 label-control">* Nome:</label>
                    <div class="input-group col-md-6">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="name" name="name"
                            placeholder="Digite o nome do usuário" value="{{ $user->name ?? '' }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="email" class="col-md-3 label-control">* Email:</label>
                    <div class="input-group col-md-6">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="Digite o email do usuário" value="{{ $user->email ?? '' }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="password" class="col-md-3 label-control">* Senha:</label>
                    <div class="input-group  col-md-6">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="{{ isset($user) ? 'A senha está configurada' : 'Digite a senha' }}"
                            {{ isset($user) ? 'disabled' : 'required' }}>
                    </div>
                </div>


                <div class="form-group row">
                    <label for="role" class="col-md-3 label-control">* Tipo:</label>
                    <div class="col-md-3">
                        <select class="form-control select2" id="role" name="role" required>
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
                    <label for="empresa" class="col-md-3 label-control">* Empresa:</label>
                    <div class="col-md-3">
                        <select class="form-control select2" id="empresa" name="empresa_id" required>
                            <option value="">Selecione</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}"
                                    {{ ($user->empresa_id ?? '') == $empresa->id ? 'selected' : '' }}>
                                    {{ $empresa->nome_fantasia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row mb-4">
                    <label for="permissions"
                        class="col-md-3 col-form-label text-md-right font-weight-bold">Permissões:</label>
                    <div class="col-md-8">
                        <div class="permissions-select-container">
                            <!-- Select2 -->
                            <select class="form-control select2-permissions" id="permissions" name="permissions[]"
                                multiple="multiple" style="width: 100%;">
                                @php
                                    $selectedPermissions =
                                        isset($user) && $user->permissions
                                            ? $user->permissions->pluck('id')->toArray()
                                            : [];
                                @endphp
                                @foreach ($permissions as $permission)
                                    @if ($permission->name !== 'gerenciar fluxo de caixa')
                                        <option value="{{ $permission->id }}"
                                            {{ in_array($permission->id, $selectedPermissions) ? 'selected' : '' }}>
                                            {{ $permission->name }}
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
                                <button type="button" class="btn btn-sm btn-outline-info toggle-select-btn ml-2">
                                    <i class="fas fa-random mr-1"></i> Inverter seleção
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card-footer">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Atualizar' : 'Criar' }}</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
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
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $('.select2').select2({});
    </script>
    <script>
        $(document).ready(function() {
            // Inicializa o Select2 com configurações aprimoradas
            $('.select2-permissions').select2({
                placeholder: "Selecione as permissões...",
                allowClear: true,
                closeOnSelect: false,
                width: '100%'
            });

            // Selecionar todas
            $('.select-all-btn').click(function() {
                $('.select2-permissions option').prop('selected', true);
                $('.select2-permissions').trigger('change');
            });

            // Desmarcar todas
            $('.deselect-all-btn').click(function() {
                $('.select2-permissions option').prop('selected', false);
                $('.select2-permissions').trigger('change');
            });

            // Inverter seleção
            $('.toggle-select-btn').click(function() {
                $('.select2-permissions option').each(function() {
                    $(this).prop('selected', !$(this).prop('selected'));
                });
                $('.select2-permissions').trigger('change');
            });
        });
    </script>
@stop
