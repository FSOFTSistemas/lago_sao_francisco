<div>
    <h5 class="mb-3">Opções de Refeição</h5>

    <form wire:submit.prevent="addOpcao" class="mb-4">
        <div class="form-row">
            <div class="col-md-4">
                <label>Nome da Opção</label>
                <input type="text" wire:model.defer="nomeOpcao" class="form-control">
                @error('nomeOpcao') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-2">
                <label>Preço por Pessoa</label>
                <input type="text" wire:model.defer="precoPorPessoa" class="form-control">
                @error('precoPorPessoa') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4">
                <label>Descrição</label>
                <input type="text" wire:model.defer="descricaoOpcao" class="form-control">
                @error('descricaoOpcao') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Adicionar</button>
            </div>
        </div>
    </form>

    @if($opcoes->count())
        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($opcoes as $opcao)
                    <tr>
                        <td>{{ $opcao->NomeOpcaoRefeicao }}</td>
                        <td>R$ {{ number_format($opcao->PrecoPorPessoa, 2, ',', '.') }}</td>
                        <td>{{ $opcao->DescricaoOpcaoRefeicao }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary" disabled>✏️</button>
                            <button class="btn btn-sm btn-outline-danger" disabled>🗑️</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">Nenhuma opção cadastrada ainda.</p>
    @endif
</div>
