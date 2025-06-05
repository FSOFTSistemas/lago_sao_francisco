<div>
    <ul class="nav nav-tabs" id="cardapioTab" role="tablist" >
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'geral' ? 'active' : '' }}" id="geral-tab" href="#" role="tab"
                wire:click.prevent="$set('abaAtual', 'geral')">Informações Gerais</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'sessoes' ? 'active' : '' }}" id="sessoes-tab" href="#"
                role="tab" wire:click.prevent="$set('abaAtual', 'sessoes')" >Seções</a>
        </li>
        @if ($cardapioID ?? false)
            @if ($PossuiOpcaoEscolhaConteudoPrincipalRefeicao)
                <li class="nav-item">
                    <a class="nav-link {{ $abaAtual === 'opcoes' ? 'active' : '' }}" id="opcoes-tab" href="#"
                        role="tab" wire:click.prevent="$set('abaAtual', 'opcoes')">Opções de Refeição</a>
                </li>
            @endif
        @endif
        <li class="nav-item">
            <a href="#" class="nav-link {{ $abaAtual === 'categorias' ? 'active' : ''}}" id="categorias-tab" role="tab" wire:click.prevent="$set('abaAtual', 'categorias')">Categorias</a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="cardapioTabContent">
        <div class="tab-pane fade {{ $abaAtual === 'geral' ? 'show active' : '' }}" id="geral" role="tabpanel">
            {{-- Informações Gerais --}}
            <h5 class="mb-3">Novo Cardápio</h5>
            <div class="alert alert-secondary">
                        <strong>DICA:</strong> Campos referentes ao Cardápio. <br>
                        <em>O preenchimento de todos os campos é obrigatório.</em>          
            </div>

            <form wire:submit.prevent="save">
               <div class="row">
                   <div class="form-group col-md-3">
                       <label>Nome do Cardápio</label>
                           <input type="text" wire:model.defer="NomeCardapio" class="form-control">
                       @error('NomeCardapio')
                           <span class="text-danger">{{ $message }}</span>
                       @enderror
                   </div>
                    <div class="form-group col-md-3">
                    <label for="ano-cardapio">Ano do Cardápio</label>
                    <select id="ano-cardapio" wire:model.defer="AnoCardapio" class="form-control">
                        @php
                            $anoAtual = date('Y');
                            $anos = range($anoAtual - 3, $anoAtual + 3);
                        @endphp
                        @foreach ($anos as $ano)
                            <option>{{ $ano }}</option>
                        @endforeach
                    </select>
                    @error('AnoCardapio')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
               </div>

                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Preço Base por Pessoa</label>
                        <div class="input-group">
                            <input type="text" wire:model.defer="PrecoBasePorPessoa" class="form-control">
                            <i class="fas fa-info-circle info-icon"></i>
                           <div class="info-tooltip">
                                       <strong>Dica:</strong> Defina o valor base por pessoa.
                                   </div>
                        </div>
                        @error('PrecoBasePorPessoa')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Validade do Orçamento (dias)</label>
                        <div class="inpunt-group">
                            <input type="number" wire:model.defer="ValidadeOrcamentoDias" class="form-control">
                                <i class="fas fa-info-circle info-icon"></i>
                                <div class="info-tooltip">
                                    <strong>Aviso:</strong> Determine por quantos dias esse valor do orçamento será válido!
                                </div>
                        </div>
                        @error('ValidadeOrcamentoDias')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <br>
                <h5>Política para Crianças</h5>
                <hr>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Idade limite para gratuidade</label>
                        <div class="input-group">
                            <input type="number" wire:model.defer="PoliticaCriancaGratisLimiteIdade" class="form-control">
                            <i class="fas fa-info-circle info-icon"></i>
                                <div class="info-tooltip">
                                    <strong>Dica:</strong> Insira a idade máxima para gratuitidade.
                                </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Idade início do desconto</label>
                        <div class="input-group">
                            <i class="fas fa-info-circle info-icon"></i>
                                <div class="info-tooltip">
                                    <strong>Dica:</strong> Insira a idade mínima em que é cobrado o valor com desconto.
                                </div>
                        </div>
                        <input type="number" wire:model.defer="PoliticaCriancaDescontoIdadeInicio"
                            class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Idade fim do desconto</label>
                        <div class="input-group">
                            <input type="number" wire:model.defer="PoliticaCriancaDescontoIdadeFim" class="form-control">
                                <i class="fas fa-info-circle info-icon"></i>
                                <div class="info-tooltip">
                                    <strong>Dica:</strong> Insira a idade máxima em que o desconto é aplicado.
                                </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Desconto (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" wire:model.defer="PoliticaCriancaDescontoPercentual"
                                class="form-control">
                                <i class="fas fa-info-circle info-icon"></i>
                                <div class="info-tooltip">
                                    <strong>Dica:</strong> determine o Desconto para a faixa de idade!
                                </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Idade com preço integral</label>
                        <div class="input-group">
                            <input type="number" wire:model.defer="PoliticaCriancaPrecoIntegralIdadeInicio"
                            class="form-control">
                            <i class="fas fa-info-circle info-icon"></i>
                                <div class="info-tooltip">
                                    <strong>Dica:</strong> insira a idade em que o preço integral é cobrado.
                                </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="form-group row"
     x-data="{ possuiOp: false }"
     x-init="possuiOp = @js((bool) $this->PossuiOpcaoEscolhaConteudoPrincipalRefeicao)">
    <label class="form-label d-block label-control">
        Permite escolher conteúdo principal da refeição?
    </label>
    <div class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            id="PossuiOpcaoSwitch"
            x-model="possuiOp"
            @change="$wire.set('PossuiOpcaoEscolhaConteudoPrincipalRefeicao', possuiOp ? 1 : 0)"
        >
        <label class="form-check-label ms-2" for="PossuiOpcaoSwitch">
            <span x-text="possuiOp ? 'Sim' : 'Não'"></span>
        </label>
    </div>

    @error('PossuiOpcaoEscolhaConteudoPrincipalRefeicao')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>




                <div class="col d-flex justify-content-end">
                    <button class="btn btn-success mt-3 new" wire:click.prevent= "avancar">Seguinte</button>
                </div>
            </form>
        </div>

        <div class="tab-pane fade {{ $abaAtual === 'sessoes' ? 'show active' : '' }}" id="sessoes" role="tabpanel">
            @if ($cardapioID ?? false)
                @livewire('cardapio-sessoes', ['cardapioId' => $cardapioID, 'refeicao' => $PossuiOpcaoEscolhaConteudoPrincipalRefeicao])
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
            {{-- @php dd($categorias) @endphp --}}
            @if($cardapioID ?? false)
        <div class="tab-pane fade {{ $abaAtual === 'categorias' ? 'show active' : '' }}" id="categorias" role="tabpanel">
           @include('categoriaItensCardapio.index', ['categorias' => $categorias]);
        </div>
        @endif
        {{-- <div class="tab-pane fade {{ $abaAtual === 'categorias' ? 'show active' : '' }}" id="categorias" role="tabpanel">
            @livewire('categoria-itens-new', ['cardapioId' => $cardapioID])
        </div> --}}
    </div>
    @section('css')
     <style>
        .info-icon {
            position: absolute;
            right: 10px;
            top: 10px;
            color: var(--green-2);
            cursor: pointer;
        }

        .info-tooltip {
            display: none;
            position: absolute;
            top: 35px;
            right: 0;
            color: white;
            background-color: var(--green-2);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            width: 300px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .info-icon:hover+.info-tooltip {
            display: block;
        }
    </style>
    @endsection

        @script
    <script>
        $wire.on("confirmed", (event) => {
            Swal.fire({
            title: "Continuar para a próxima página?",
            text: "Revise todos os campos",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, continuar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("avancou")
            }
            });
        })
    </script>
    @endscript
</div>
