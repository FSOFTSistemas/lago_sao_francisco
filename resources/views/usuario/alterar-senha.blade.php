@extends('adminlte::page')

@section('title', 'Alterar Senha')

@section('content_header')
    <h5>Alterar Senha</h5>
    <hr>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('usuario.senha.atualizar') }}">
        @csrf

        <div class="form-group row">
            <label class="label-control col-md-4" for="email">E-mail:</label>
            <div class="col-md-3">
              <input type="email" class="form-control" value="{{ $usuario->email }}" readonly>
            </div>
        </div>

        <div class="form-group row">
            <label class="label-control col-md-4" for="senha">Nova Senha:</label>
            <div class="col-md-3">
              <input type="password" name="senha" class="form-control" required minlength="8">
              @error('senha')
                  <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
        </div>

        <div class="form-group row">
            <label class="label-control col-md-4" for="senha_confirmation">Confirme a Nova Senha:</label>
            <div class="col-md-3">
              <input type="password" name="senha_confirmation" class="form-control" required minlength="8">
            </div>
        </div>

        <div class="card-footer">
          <button type="submit" class="btn btn-primary w-100">Atualizar Senha</button>
        </div>
    </form>
@stop
