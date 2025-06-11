<div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Categorias do Cardápio</h5>
    <button class="btn btn-success" wire:click="finalizarCardapio">Finalizar</button>
  </div>

  <div class="mb-4 d-flex justify-content-end">
    <button class="btn btn-success" wire:click="novaCategoria">
      <i class="fas fa-plus"></i> Nova Categoria
    </button>
  </div>

  <!-- Categorias por Seções -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-success">
      <h6 class="mb-0">Categorias por Seções</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Seção</th>
              <th>Categoria</th>
              <th class="text-center" style="width: 120px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cardapio->secoes ?? [] as $secao)
              @foreach($secao->categorias as $categoria)
              <tr>
                <td>{{ $secao->nome_secao_cardapio }} (ID: {{ $secao->id }})</td>
                <td>{{ $categoria->nome_categoria_item }} (ID: {{ $categoria->id }})</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary me-1" wire:click="editCat({{ $categoria->id }})" title="Editar Categoria">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" wire:click="deletarCat({{ $categoria->id }})" title="Excluir Categoria">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              @endforeach
            @empty
              <tr><td colspan="3" class="text-center text-muted">Nenhuma categoria por seção encontrada.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Categorias por Refeições -->
  <div class="card shadow-sm">
    <div class="card-header bg-success">
      <h6 class="mb-0">Categorias por Refeições</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Refeição</th>
              <th>Categoria</th>
              <th class="text-center" style="width: 120px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cardapio->opcoes ?? [] as $refeicao)
              @foreach($refeicao->categorias as $categoria)
              <tr>
                <td>{{ $refeicao->NomeOpcaoRefeicao }} (ID: {{ $refeicao->id }})</td>
                <td>{{ $categoria->nome_categoria_item }} (ID: {{ $categoria->id }})</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary me-1" wire:click="editCat({{ $categoria->id }})" title="Editar Categoria">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" wire:click="deletarCat({{ $categoria->id }})" title="Excluir Categoria">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              @endforeach
            @empty
              <tr><td colspan="3" class="text-center text-muted">Nenhuma categoria por refeição encontrada.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

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
          $wire.dispatch("deleteCat", { id: event.id });
        }
      });
    });

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
          $wire.dispatch("finalizadoCardapio");
        }
      });
    });
  </script>
  @endscript
</div>
