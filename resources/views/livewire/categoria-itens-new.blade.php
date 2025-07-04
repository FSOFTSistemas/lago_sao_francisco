<div  x-data="{
    ehExclusiva: @entangle('eh_grupo_escolha_exclusiva'),
    get valorEscolhas() {
        return this.ehExclusiva ? 1 : @entangle('numero_escolhas_permitidas')
    }
}">
<div class="alert alert-secondary">
    <strong>ATENÇÃO:</strong> Ao criar uma categoria nova, você deve selecionar OU uma seção OU uma Refeição. <br>
    <em>O campo Escolha exclusiva define o nº de escolhas para 1. ex: escolher entre Arroz Branco OU Arroz de Brócolis</em>
</div>
    <h5 class="mb-3">{{ $categoriaSalva ? 'Editar' : 'Nova' }} Categoria do Cardápio</h5>
    

    <form wire:submit.prevent="save">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nome_categoria_item" class="font-weight-bold">Nome da Categoria*</label>
                    <input type="text" wire:model="nome_categoria_item" class="form-control @error('nome_categoria_item') is-invalid @enderror" 
                           id="nome_categoria_item" placeholder="Ex: Pratos Principais">
                    @error('nome_categoria_item')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="ordem_exibicao" class="font-weight-bold">Ordem de Exibição*</label>
                    <input type="number" wire:model="ordem_exibicao" class="form-control @error('ordem_exibicao') is-invalid @enderror" 
                           id="ordem_exibicao" min="1">
                    @error('ordem_exibicao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="numero_escolhas_permitidas" class="font-weight-bold">Nº de Escolhas Permitidas*</label>
                        <input type="number"
                        wire:model="numero_escolhas_permitidas"
                        x-bind:readonly="ehExclusiva"
                        x-bind:value="ehExclusiva ? 1 : $wire.numero_escolhas_permitidas"
                        class="form-control @error('numero_escolhas_permitidas') is-invalid @enderror"
                        id="numero_escolhas_permitidas" min="1" max="10">

                    @error('numero_escolhas_permitidas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-group">
                    <label class="font-weight-bold">Escolha Exclusiva?</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox"
                        wire:model="eh_grupo_escolha_exclusiva"
                        x-model="escolhaExclusiva"
                        class="custom-control-input"
                        id="eh_grupo_escolha_exclusiva">
                        <label class="custom-control-label" for="eh_grupo_escolha_exclusiva">Sim</label>
                    </div>
                    @error('eh_grupo_escolha_exclusiva')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="refeicao_principal_id">Refeição Principal</label>
                    <select wire:model="refeicao_principal_id" wire:change="limparSessao"
                    class="form-control @error('refeicao_principal_id') is-invalid @enderror"
                    id="refeicao_principal_id" wire:key="refeicaoselect-{{ $inputKey }}">
                        <option value="">Nenhuma</option>
                        @foreach($refeicoes as $refeicao)
                            <option value="{{ $refeicao->id }}">
                                {{ $refeicao->NomeOpcaoRefeicao }}
                            </option>
                        @endforeach
                    </select>
                    @error('refeicao_principal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

                    <div class="col-md-6">
            <div class="form-group">
                <label for="sessao_cardapio_id" class="font-weight-bold">Seção do Cardápio</label>
               <select wire:model="sessao_cardapio_id" wire:change="limparRefeicao"
                class="form-control @error('sessao_cardapio_id') is-invalid @enderror"
                id="sessao_cardapio_id" wire:key="secaoselect-{{ $inputKey }}">
                    <option value="">Selecione a seção...</option>
                    @foreach($secoes as $secao)
                        <option value="{{ $secao->id }}">
                            {{ $secao->nome_secao_cardapio }}
                        </option>
                    @endforeach
                </select>
                @error('sessao_cardapio_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Salvar Categoria
            </button>
        </div>
    </form>

    <!-- Seção de Itens -->
    <div class="mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h5>Itens da Categoria</h5>
            <button class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus mr-1"></i> Adicionar Item
            </button>
        </div>

        <!-- Modal para adicionar item -->
        <div class="modal fade @if ($modalAberto) show d-block @endif" tabindex="-1"
        style="@if ($modalAberto) background-color: rgba(0,0,0,0.5); @else display:none; @endif"
        aria-modal="true" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Adicionar Item à Categoria</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Selecione o Item</label>
                            <select wire:model="selectedItem" class="form-control" wire:key="item-{{ $inputKey }}">
                                <option value="">Selecione um item...</option>
                                @foreach($allItems ?? [] as $item)
                                    <option value="{{ $item->id }}">{{ $item->nome_item }}</option>
                                @endforeach
                            </select>
                            @error('selectedItem') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="addItem">
                            Adicionar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de itens -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Nome do Item</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itensTemporarios ?? [] as $item)
                    <tr>
                            <td>{{ $item['nome_item']}}</td>
                            <td>{{ $item['tipo_item']}}</td>
                            <td>
                                <button wire:click="removeItem({{ $item['id'] }})" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Nenhum item adicionado ainda</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
