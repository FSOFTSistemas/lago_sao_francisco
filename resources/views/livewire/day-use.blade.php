<div>
    <ul class="nav nav-tabs" id="dayuseTab" role="tablist" style="display: none">
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'geral' ? 'active' : '' }}" id="geral-tab" href="#" role="tab"
                wire:click.prevent="$set('abaAtual', 'geral')">Informações Gerais</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'pagamento' ? 'active' : '' }}" id="pagamento-tab" href="#"
                role="tab" wire:click.prevent="$set('abaAtual', 'pagamento')" >Pagamento</a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="dayuseTabContent">
        <div class="tab-pane fade {{ $abaAtual === 'geral' ? 'show active' : '' }}" id="geral" role="tabpanel">
            <form wire:submit.prevent="save">
                {{-- Campos de Data --}}
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="data" class="form-label" style="font-size: 1.5rem">Data de Entrada</label>
                        <input type="date" class="form-control" id="data" wire:model="data">
                        @error('data') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col d-flex justify-content-end">
                         <button type="submit" class="btn btn-primary mt-3" style="font-size: 1.5rem !important">Continuar</button>
                    </div>

                </div>

                {{-- Seletores de Cliente e Vendedor --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="clienteSelect" class="form-label pr-2" style="font-size: 1.5rem">Cliente</label>
                        <select id="clienteSelect" class="form-select form-control-lg" wire:model.live="selectedClientId">
                            <option value="">Selecione um Cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->apelido_nome_fantasia }}</option>
                            @endforeach
                        </select>
                        @error('selectedClientId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="vendedorSelect" class="form-label pr-2" style="font-size: 1.5rem">Vendedor</label>
                        <select id="vendedorSelect" class="form-select form-control-lg" wire:model.live="selectedVendorId">
                            <option value="">Selecione um Vendedor</option>
                            @foreach($vendedores as $vendedor)
                                <option value="{{ $vendedor->id }}">{{ $vendedor->nome }}</option>
                            @endforeach
                        </select>
                        @error('selectedVendorId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Cards dos Itens --}}
                <div class="row row-cols-2 row-cols-md-3 g-3 justify-content-center mt-3">
                    @foreach($items as $item)
                        <div class="col" wire:key="item-{{ $item->id }}">
                            <div class="card h-80 text-center border-success">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    {{-- Nome do Item --}}
                                    <h5>{{ $item->descricao }}</h5>

                                    <div class="icon-placeholder mb-3">
                                        @if($item->descricao == 'Meia Entrada')
                                            <i class="fas fa-percent fa-3x" style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Adulto')
                                            <i class="fas fa-user fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Cine')
                                            <i class="fas fa-film fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Ensaio')
                                            <i class="fas fa-camera fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Pedalinho')
                                            <i class="fas fa-water fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Tirolesa')
                                            <i class="fas fa-mountain fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Pônei')
                                            <i class="fas fa-horse fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Circuito')
                                            <i class="fas fa-layer-group fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Outros')
                                            <i class="fas fa-ellipsis-h fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Excursão')
                                            <i class="fas fa-route fa-3x " style="color: #679A4C;"></i> 
                                        @else
                                            <i class="fas fa-layer-group fa-3x " style="color: #679A4C;"></i> {{-- Ícone genérico --}}
                                        @endif
                                    </div>

                                    {{-- Quantidade e Botões --}}
                                    <div class="d-flex justify-content-center align-items-center mb-2">
                                        <button type="button" class="btn btn-outline-success btn-md me-2" wire:click="decrementQuantity({{ $item->id }})" {{ $quantidade[$item->id] == 0 ? 'disabled' : '' }}>-</button>
                                        <span class="fs-6 pl-2 pr-2" style="font-size: 1.5rem">{{ $quantidade[$item->id] ?? 0 }}</span>
                                        <button type="button" class="btn btn-outline-success btn-md ms-2" wire:click="incrementQuantity({{ $item->id }})">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Campo Total --}}
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <label for="totalField" class="form-label fs-3" style="font-size: 1.5rem">Subtotal:</label>
                        <input type="text" id="totalField" class="form-control form-control-lg text-center" style="font-size: 2rem" value="R$ {{ number_format($total, 2, ',', '.') }}" readonly name="total">
                        @error('total') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <br>
                <br>

               
            </form>
        </div>

        <div class="tab-pane fade {{ $abaAtual === 'pagamento' ? 'show active' : '' }}" id="pagamento" role="tabpanel">
                @if($dayUse && $dayUse->id)
                @livewire('DayUsePagamento', ['dayUseId' => $dayUse->id, 'itemSubtotal' => $total])
            @else
             <div class="alert alert-warning">
                    Por favor, preencha e salve as informações gerais antes de prosseguir para o pagamento.
                </div>
            @endif
        </div>

    </div>

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
