<div>
    <ul class="nav nav-tabs" id="cardapioTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'geral' ? 'active' : '' }}" id="geral-tab" href="#" role="tab"
                wire:click.prevent="$set('abaAtual', 'geral')">Informações Gerais</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'sessoes' ? 'active' : '' }}" id="sessoes-tab" href="#"
                role="tab" wire:click.prevent="$set('abaAtual', 'sessoes')">Seções</a>
        </li>
        @if ($cardapioID ?? false)
            @if ($PossuiOpcaoEscolhaConteudoPrincipalRefeicao)
                <li class="nav-item">
                    <a class="nav-link {{ $abaAtual === 'opcoes' ? 'active' : '' }}" id="opcoes-tab" href="#"
                        role="tab" wire:click.prevent="$set('abaAtual', 'opcoes')">Opções de Refeição</a>
                </li>
            @endif
        @endif
    </ul>

    <div class="tab-content mt-3" id="cardapioTabContent">
        <div class="tab-pane fade {{ $abaAtual === 'geral' ? 'show active' : '' }}" id="geral" role="tabpanel">
            {{-- Informações Gerais --}}
            <h5 class="mb-3">Novo Cardápio</h5>


            <form wire:submit.prevent="save">
                <div class="form-group">
                    <label>Nome do Cardápio</label>
                    <input type="text" wire:model.defer="NomeCardapio" class="form-control">
                    @error('NomeCardapio')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="row">

                    <div class="form-group col-md-4">
                        <label>Ano do Cardápio</label>
                        <input type="number" wire:model.defer="AnoCardapio" class="form-control">
                        @error('AnoCardapio')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Preço Base por Pessoa</label>
                        <input type="text" wire:model.defer="PrecoBasePorPessoa" class="form-control">
                        @error('PrecoBasePorPessoa')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Validade do Orçamento (dias)</label>
                        <input type="number" wire:model.defer="ValidadeOrcamentoDias" class="form-control">
                        @error('ValidadeOrcamentoDias')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>


                <hr>
                <h6>Política para Crianças</h6>

                <div class="form-row">
                    <div class="col">
                        <label>Idade limite para gratuidade</label>
                        <input type="number" wire:model.defer="PoliticaCriancaGratisLimiteIdade" class="form-control">
                    </div>
                    <div class="col">
                        <label>Desconto (%)</label>
                        <input type="number" step="0.01" wire:model.defer="PoliticaCriancaDescontoPercentual"
                            class="form-control">
                    </div>
                </div>

                <div class="form-row mt-2">
                    <div class="col">
                        <label>Idade início do desconto</label>
                        <input type="number" wire:model.defer="PoliticaCriancaDescontoIdadeInicio"
                            class="form-control">
                    </div>
                    <div class="col">
                        <label>Idade fim do desconto</label>
                        <input type="number" wire:model.defer="PoliticaCriancaDescontoIdadeFim" class="form-control">
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label>Idade com preço integral</label>
                    <input type="number" wire:model.defer="PoliticaCriancaPrecoIntegralIdadeInicio"
                        class="form-control">
                </div>

                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input"
                        wire:model.defer="PossuiOpcaoEscolhaConteudoPrincipalRefeicao">
                    <label class="form-check-label">Permite escolher conteúdo principal da refeição?</label>
                </div>

                <button class="btn btn-success mt-3">Seguinte</button>
            </form>
        </div>

        <div class="tab-pane fade {{ $abaAtual === 'sessoes' ? 'show active' : '' }}" id="sessoes" role="tabpanel">
            @if ($cardapioID ?? false)
                @livewire('cardapio-sessoes', ['cardapioId' => $cardapioID])
            @else
                <div class="text-muted mt-3">Salve o cardápio para adicionar seções.</div>
            @endif
        </div>

        @if ($cardapioID ?? false)
            @if ($PossuiOpcaoEscolhaConteudoPrincipalRefeicao)
                <div class="tab-pane fade {{ $abaAtual === 'opcoes' ? 'show active' : '' }}" id="opcoes"
                    role="tabpanel">
                    @livewire('cardapio-opcoes-refeicao', ['cardapioId' => $cardapioID])
                </div>
            @endif
        @endif
    </div>
</div>
