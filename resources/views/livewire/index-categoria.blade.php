<div>
  <div class="col d-flex justify-content-end">
    <h5 class="mb-3">Categorias do Cardápio</h5>
    <button class="btn btn-success new" wire:click="finalizarCardapio">Finalizar</button>
  </div>

  <div class="row mb-3">
    <div class="col d-flex justify-content-end">
        <button class="btn btn-success new" wire:click="novaCategoria">
        <i class="fas fa-plus"></i> Nova Categoria</button>
    </div>
  </div>

  <h4>Categorias por Seções</h4>
<ul wire:key="listaOpcoes-{{ $inputKey }}">
    @foreach($cardapio->secoes ?? [] as $secao)
    <li>{{$secao->nome_secao_cardapio}} (ID: {{ $secao->id }})</li>
        @foreach($secao->categorias as $categoria)
            <li>
               *------ {{ $categoria->nome_categoria_item }} (ID: {{ $categoria->id }})
                <button 
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    wire:click="editCat({{$categoria->id}})"
                    title="Editar Categoria"
                >
                    ✏️
                </button>
                <button 
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    wire:click="deletarCat({{$categoria->id}})"
                    title="Excluir Opção"
                >
                    🗑️
                </button>
            </li>
        @endforeach
    @endforeach
</ul>

<hr>

<h4>Categorias por Refeições</h4>
<ul>
    @foreach($cardapio->opcoes ?? [] as $refeicao)
        <li>{{ $refeicao->NomeOpcaoRefeicao }} (ID: {{ $refeicao->id }})</li>
            @foreach($refeicao->categorias as $categoria)
            <li>
               * {{ $categoria->nome_categoria_item }} (ID: {{ $categoria->id }})
                <button 
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    wire:click="editCat({{$categoria->id}})"
                    title="Editar Categoria"
                >
                    ✏️
                </button>
                <button 
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    wire:click="deletarCat({{$categoria->id}})"
                    title="Excluir Opção"
                >
                    🗑️
                </button>
            </li>
            @endforeach
    @endforeach
</ul>

 @script
    <script>
        $wire.on("confirmCat", (event) => {
            Swal.fire({
            title: "Deletar Categoria?",
            text: "Você não poderá desfazer!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, deletar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("deleteCat", { id: event.id})
            }
            });
        })

        $wire.on("confirmFinalizarCardapio", () => {
            Swal.fire({
            title: "Deseja finalizar o cardápio?",
            text: "Revise todos os campos antes de salvar!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, finalizar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("finalizadoCardapio")
            }
            });
        })
    </script>
    @endscript

</div>