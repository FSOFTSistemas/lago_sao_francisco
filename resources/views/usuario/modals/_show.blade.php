<!-- resources/views/users/modals/_show.blade.php -->
<div class="modal fade" id="showUsuario{{ $user->id }}" tabindex="-1" aria-labelledby="showUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="showUsuarioLabel">Detalhes do Usuário</h5>
              <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <p><strong>Nome:</strong> <span id="name">{{$user->name}}</span></p>
              <p><strong>Email:</strong> <span id="email">{{$user->email}}</span></p>
              <p><strong>Tipo:</strong> <span id="role">
                {{ $user->roles->first()->name ?? 'Não definido' }}
            </span></p>
            <p><strong>Permissões:</strong> <span id="permissions">
              {{ $user->permissions->pluck('name')->join(' | ') ?? 'Nenhuma' }}
          </span></p>
          <p><strong>Empresa:</strong> <span id="empresa">
            {{ $user->empresa->nome_fantasia ?? 'Não definida' }}
        </span></p>

          </div>
      </div>
  </div>
</div>
