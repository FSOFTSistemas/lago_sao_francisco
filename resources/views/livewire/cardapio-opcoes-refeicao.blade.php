<div>
    <h5 class="mb-3">Opções de Refeição</h5>
        <div class="col d-flex justify-content-end">
        <button class="btn btn-success new" wire:click="proximoCategoria">Próximo</button>
    </div>

    <form wire:submit.prevent="addOpcao" class="mb-4">
        <div class="form-row">
            <div class="col-md-4">
                <label>Nome da Opção</label>
                <input type="text" wire:model="nomeOpcao" class="form-control" wire:key="nomeOpcao-{{ $inputKey }}">
                @error('nomeOpcao') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-2">
                <label>Preço por Pessoa</label>
                <input type="text" wire:model="precoPorPessoa" class="form-control" wire:key="precoPorPessoa-{{ $inputKey }}">
                @error('precoPorPessoa') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4">
                <label>Descrição</label>
                <input type="text" wire:model="descricaoOpcao" class="form-control" wire:key="descricaoOpcao-{{ $inputKey }}">
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
                            <button 
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                wire:click="deletarOpcao({{$opcao->id}})"
                                title="Excluir Opção"
                            >
                                🗑️
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">Nenhuma opção cadastrada ainda.</p>
    @endif

     @script
    <script>
        $wire.on("confirm", (event) => {
            Swal.fire({
            title: "Deletar opção?",
            text: "Você não poderá desfazer!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, deletar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("deleteOpcao", { id: event.id})
            }
            });
        })
  
        $wire.on("confirmProxAba", () => {
            Swal.fire({
            title: "Ir para a próxima aba?",
            text: "Você tem certeza? Você será direcionado para a criação das categorias do cardapio e seus itens",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, Prosseguir!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("proxAbaConfirmado",)
            }
            });
        })
    </script>
    @endscript
</div>
