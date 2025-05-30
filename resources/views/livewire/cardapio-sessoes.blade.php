<div>
    <h5 class="mb-3">Seções do Cardápio</h5>

    <form wire:submit.prevent="addSessao" class="mb-4">
        <div class="form-row">
            <div class="col">
                <label>Nome da Seção</label>
                <input type="text" wire:model.defer="nomeSessao" class="form-control" required>
                @error('nomeSessao') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col">
                <label>Ordem de Exibição</label>
                <input type="number" wire:model.defer="ordemExibicao" class="form-control" required>
                @error('ordemExibicao') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col">
                <label>É conteúdo principal?</label>
                <select wire:model.defer="ehOpcaoPrincipal" class="form-control" required>
                    <option value="0">Não</option>
                    <option value="1">Sim</option>
                </select>
                @error('ehOpcaoPrincipal') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-auto d-flex align-items-end">
                <button class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </form>

    @if($sessoes->count())
        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Ordem</th>
                    <th>Principal?</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sessoes as $sessao)
                    <tr>
                        <td>{{ $sessao->NomeSecaoCardapio }}</td>
                        <td>{{ $sessao->OrdemExibicao }}</td>
                        <td>{{ $sessao->EhOpcaoConteudoPrincipalRefeicao ? 'Sim' : 'Não' }}</td>
                        <td>
                            {{-- Futuro: editar e excluir --}}
                            <button class="btn btn-sm btn-outline-secondary" disabled>✏️</button>
                            <button class="btn btn-sm btn-outline-danger" disabled>🗑️</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">Nenhuma seção cadastrada ainda.</p>
    @endif
</div>
