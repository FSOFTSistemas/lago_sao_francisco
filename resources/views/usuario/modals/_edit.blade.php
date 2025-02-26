<div class="modal fade" id="editUsuarioModal{{$user->id}}" tabindex="-1" aria-labelledby="editUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUsuarioModalLabel">Editar Usuário</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUsuarioForm" action="{{ route('usuarios.update',$user->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required value="{{$user->name}}">
                    </div>
                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="{{$user->email}}" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="password">Senha</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="role">Permissão</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="financeiro">Financeiro</option>
                            <option value="funcionario">Funcionário</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

