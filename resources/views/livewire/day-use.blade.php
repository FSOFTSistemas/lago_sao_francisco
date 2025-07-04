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
                <div class="row d-none">
                    <div class="mb-3 col-md-4">
                        <label for="data" class="form-label" style="font-size: 1.5rem">Data de Entrada</label>
                        <input type="date" class="form-control" id="data" wire:model="data">
                        @error('data') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Seletores de Cliente e Vendedor --}}
                <div class="form-group row mb-3" wire:ignore>
                    <div class="col-md-6">
                        <label for="clienteSelect" class="form-label pr-2" style="font-size: 1.5rem">Cliente :</label>
                        <button wire:click="openModal" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                        </button>
                            <select id="clienteSelect" class="form-select form-control-lg w-100" wire:model.live="selectedClientId">
                                <option value="">Selecione um Cliente</option>
                            </select>
                           
                            @error('selectedClientId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    <div class="col-md-6">
                        <label for="vendedorSelect" class="form-label pr-2" style="font-size: 1.5rem">Vendedor :</label>
                        <select id="vendedorSelect" class="form-select form-control-lg w-100" wire:model.live="selectedVendorId">
                            <option value="">Selecione um Vendedor</option>
                        </select>
                        @error('selectedVendorId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Cards dos Itens --}}
                <div class="row row-cols-2 row-cols-md-5 g-1 justify-content-center mt-3 custom-cols" >
                    @foreach($items as $item)
                        <div class="col" wire:key="item-{{ $item->id }}">
                            <div class="card h-80 text-center border {{ $quantidade[$item->id] ? 'border-success' : '' }}" style="{{ $quantidade[$item->id] ? 'background-color: #e7f8de' : '' }}">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    {{-- Nome do Item --}}
                                    <h5>{{ $item->descricao }} / R${{$item->valor}}</h5>

                                    <div class="icon-placeholder mb-3">
                                        @if($item->descricao == 'Meia Entrada')
                                            <i class="fas fa-percent fa-3x" style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Entrada Adulto')
                                            <i class="fas fa-user fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Passeio Cine 6D')
                                            <i class="fas fa-film fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Ensaio Individual')
                                            <i class="fas fa-camera fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Passeio de Pedalinho')
                                            <i class="fas fa-water fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Passeio de Tirolesa')
                                            <i class="fas fa-mountain fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Passeio de Cavalo/Pônei')
                                            <i class="fas fa-horse fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Circuito de Passeios')
                                            <i class="fas fa-layer-group fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Outros')
                                            <i class="fas fa-ellipsis-h fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Entrada Excursão')
                                            <i class="fas fa-route fa-3x " style="color: #679A4C;"></i> 
                                        @elseif($item->descricao == 'Ensaio Pré-Wedding')
                                            <i class="fas fa-ring fa-3x " style="color: #679A4C;"></i>
                                        @elseif($item->descricao == 'Venda Passaporte')
                                            <i class="fas fa-ticket-alt fa-3x " style="color: #679A4C;"></i>
                                        @elseif($item->descricao == 'Entrada Passaporte')
                                            <i class="fas fa-check fa-3x " style="color: #679A4C;"></i>
                                        @elseif($item->descricao == 'Entrada Infantil até 4 anos')
                                            <i class="fas fa-child fa-3x " style="color: #679A4C;"></i>
                                        @else
                                            <i class="fas fa-layer-group fa-3x " style="color: #679A4C;"></i> {{-- Ícone genérico --}}
                                        @endif
                                    </div>

                                    {{-- Quantidade e Botões --}}
                                    <div class="d-flex justify-content-center align-items-center mb-2">
                                        <button type="button" class="btn btn-outline-success btn-lg me-2" wire:click="decrementQuantity({{ $item->id }})" {{ $quantidade[$item->id] == 0 ? 'disabled' : '' }}>-</button>
                                        <span class="fs-6 pl-3 pr-3" style="font-size: 2rem; {{$quantidade[$item->id] ? "" : ''}}">{{ $quantidade[$item->id] ?? 0 }}</span>
                                        <button type="button" class="btn btn-outline-success btn-lg ms-2" wire:click="incrementQuantity({{ $item->id }})">+</button>
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
                    <div class="col d-flex justify-content-end">
                         <button type="submit" class="btn btn-primary mt-3 w-100" style="font-size: 1.5rem !important">Pagamento</button>
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


     <!-- Modal de Cadastro de Cliente -->
          <div class="modal fade @if ($modalAberto) show d-block @endif" tabindex="-1"
        style="@if ($modalAberto) background-color: rgba(0,0,0,0.5); @else display:none; @endif"
        aria-modal="true" role="dialog">
            <div class="modal-dialog " role="document">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalClienteLabel">Cadastrar Cliente</h5>
                        <button type="button" class="btn new" wire:click="fecharModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                            <form>
                                <div class="row mb-3">
                                    <span><em>Todos os campos são obrigatórios</em></span>
                                </div>
                            <div class="form-group row">
                                <label class="col-md-3 label-control"  for="nome">* Nome completo:</label>
                                <div class="col-md-6">
                                <div><input class="form-control" required="required" type="text" wire:model='nome_razao_social' id="nome" autocomplete="off"></div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 label-control"  for="telefone">* Telefone:</label>
                                <div class="col-md-6">
                                <div><input class="form-control" type="number" wire:model='telefone' id="telefone" autocomplete="off" required="required"></div>
                                </div>
                            </div>
                        </div>
      
                    </form>
                    <div class="modal-footer">
                        <a href="{{route('cliente.create')}}">Cadastro Completo</a>
                        <button type="submit" wire:click="saveCliente" class="btn btn-primary">Salvar</button>
                    </div>
                    </div>
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

        //select2
        $(document).ready(function () {
        $('#clienteSelect').select2({
            placeholder: 'Selecione um Cliente',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route('clientes.search') }}', // rota que você vai criar
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // o que o usuário digitou
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(cliente) {
                            return { id: cliente.id, text: cliente.nome_razao_social };
                        })
                    };
                },
                cache: true
            },
            width: '100%',
             language: {
        noResults: function() {
            return "Nenhum resultado encontrado";
        },
        searching: function() {
            return "Buscando...";
        },
        inputTooShort: function (args) {
        var remainingChars = args.minimum - args.input.length;
        return 'Digite mais ' + remainingChars + ' caractere' + (remainingChars !== 1 ? 's' : '') + ' para buscar';
    },
        loadingMore: function() {
            return "Carregando mais resultados...";
        }
    }
        });
        $('#clienteSelect').on('change', function () {
            @this.set('selectedClientId', $(this).val());
        });
    });

        //select2 para vendedores
        $(document).ready(function () {
        $('#vendedorSelect').select2({
            placeholder: 'Selecione um Vendedor',
            // minimumInputLength: 3, ativar caso queira buscar digitando
            ajax: {
                url: '{{ route('vendedors.search') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // o que o usuário digitou
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(vendedor) {
                            return { id: vendedor.id, text: vendedor.nome };
                        })
                    };
                },
                cache: true
            },
            width: '100%',
            language: {
        noResults: function() {
            return "Nenhum resultado encontrado";
        },
        searching: function() {
            return "Buscando...";
        },
        inputTooShort: function (args) {
        var remainingChars = args.minimum - args.input.length;
        return 'Digite mais ' + remainingChars + ' caractere' + (remainingChars !== 1 ? 's' : '') + ' para buscar';
    },
        loadingMore: function() {
            return "Carregando mais resultados...";
        }
    }
        });

        // sincroniza com o Livewire
                $('#vendedorSelect').on('change', function () {
            @this.set('selectedVendorId', $(this).val());
        });
    });
    </script>
    @endscript
</div>
