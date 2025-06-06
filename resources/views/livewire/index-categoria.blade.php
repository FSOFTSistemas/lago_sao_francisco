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
<ul>
    @foreach($cardapio->secoes ?? [] as $secao)
    <li>{{$secao->nome_secao_cardapio}}</li>
    @dump($secao->categorias)
        @foreach($secao->categorias as $categoria)
            <li>
                {{ $categoria->nome_categoria_item }} (ID: {{ $categoria->id }})
                {{-- <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-sm btn-primary">Editar</a>
                <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Deseja deletar?')">Deletar</button>
                </form> --}}
            </li>
        @endforeach
    @endforeach
</ul>

<hr>

<h4>Categorias por Refeições</h4>
<ul>
    @foreach($cardapio->opcoes ?? [] as $refeicao)
        @foreach($refeicao->categorias as $categoria)
            <li>
                {{ $categoria->nome_categoria_item }} (ID: {{ $categoria->id }})
                {{-- <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-sm btn-primary">Editar</a>
                <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Deseja deletar?')">Deletar</button>
                </form> --}}
            </li>
        @endforeach
    @endforeach
</ul>



</div>