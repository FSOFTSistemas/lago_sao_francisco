@extends('adminlte::page')

@section('title', isset($user) ? 'Editar Usuario' : 'Cadastrar Usuario')

@section('content_header')
    <h1>{{ isset($user) ? 'Editar Usuario' : 'Cadastrar Usuario' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header green bg-primary text-white">
            <h3 class="card-title">
                {{ isset($user) ? 'Editar informações do Usuário' : 'Preencha os dados do novo Usuário' }}</h3>
        </div>
        <div class="card-body">
            <form id="createUsuarioForm" action="{{ isset($user) ? route('usuarios.update', $user->id) : route('usuarios.store') }}" method="POST">
                @csrf
                @if (isset($user))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="name">Nome</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" id="name" name="name"
                            placeholder="Digite o nome do usuário" value="{{ $user->name ?? '' }}" required>
                    </div>
                </div>
        
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="Digite o email do usuário" value="{{ $user->email ?? '' }}" required>
                    </div>
                </div>
        
                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="{{ isset($user) ? 'A senha está configurada' : 'Digite a senha' }}"
                            {{ isset($user) ? 'disabled' : 'required' }}>
                    </div>
                </div>
                
        
                <div class="mb-3">
                    <label for="role">Tipo</label>
                    <select class="form-control select2" id="role" name="role" required>
                        <option value="">Selecione um tipo</option>
                        @foreach ($roles as $role)
                            @if ($role->name !== 'Master') <!-- Exclui a role "Master" -->
                                <option value="{{ $role->name }}" 
                                    {{ isset($user) && ($user->roles->first()->name ?? '') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="empresa">Empresa</label>
                    <select class="form-control select2" id="empresa" name="empresa_id" required>
                        <option value="">Selecione</option>
                        @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->id }}" {{ ($user->empresa_id ?? '') == $empresa->id ? 'selected' : '' }}>
                                {{ $empresa->nome_fantasia }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="permissions">Permissões</label>
                    <select class="form-control select2" id="permissions" name="permissions[]" multiple="multiple">
                        @php
                            $selectedPermissions = isset($user) && $user->permissions ? $user->permissions->pluck('id')->toArray() : [];
                        @endphp
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->id }}" 
                                {{ in_array($permission->id, $selectedPermissions) ? 'selected' : '' }}>
                                {{ $permission->name }}
                            </option>
                        @endforeach
                    </select>
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
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $('.select2').select2({
    });
</script>
@stop
