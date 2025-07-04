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
                <input type="password" name="senha" id="senha" class="form-control" required minlength="8">
                <small id="erro-minlength" class="text-danger" style="display: none;">
                    A senha deve ter pelo menos 8 caracteres.
                </small>
            </div>
        </div>

        <div class="form-group row">
            <label class="label-control col-md-4" for="senha_confirmation">Confirme a Nova Senha:</label>
            <div class="col-md-3">
                <input type="password" name="senha_confirmation" id="senha_confirmation" class="form-control" required
                    minlength="8">
                <small id="erro-diferente" class="text-danger" style="display: none;">
                    As senhas não coincidem.
                </small>
            </div>
        </div>



        <div class="card-footer">
            <button type="submit" id="btn-atualizar" class="btn btn-primary w-100" disabled>Atualizar Senha</button>
        </div>

    </form>
@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const senha = document.getElementById('senha');
        const confirmacao = document.getElementById('senha_confirmation');
        const erroMin = document.getElementById('erro-minlength');
        const erroDiff = document.getElementById('erro-diferente');
        const btn = document.getElementById('btn-atualizar');

        function validar() {
            let senhaVal = senha.value;
            let confirmVal = confirmacao.value;
            let valido = true;

            // Valida mínimo de caracteres
            if (senhaVal.length < 8) {
                erroMin.style.display = 'block';
                senha.classList.add('is-invalid');
                valido = false;
            } else {
                erroMin.style.display = 'none';
                senha.classList.remove('is-invalid');
            }

            // Valida se as senhas são iguais
            if (confirmVal && senhaVal !== confirmVal) {
                erroDiff.style.display = 'block';
                confirmacao.classList.add('is-invalid');
                valido = false;
            } else {
                erroDiff.style.display = 'none';
                confirmacao.classList.remove('is-invalid');
            }

            // Habilita ou desabilita o botão
            btn.disabled = !valido;
        }

        senha.addEventListener('input', validar);
        confirmacao.addEventListener('input', validar);
    });
</script>
@stop


@stop
