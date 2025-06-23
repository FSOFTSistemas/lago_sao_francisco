<div>
    <div class="container py-4">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">DayUse #{{ $dayUseId}}</h2>
            <div>
                <button wire:click="confirmDelete" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>

        <!-- Card Principal -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Informações Gerais</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Cliente:</strong> {{ $dayUse->cliente->nome?? '' }}
                        </div>
                        <div class="mb-3">
                            <strong>Data:</strong> {{ $dayUse->data ?? ''}}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Vendedor:</strong> {{ $dayUse->vendedor->nome ?? ''}}
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            <span class="badge bg-success">Ativo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Itens -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">Itens</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itens as $item)
                            <tr>
                                <td>{{ $item->item->nome }}</td>
                                <td>{{ $item->quantidade }}</td>
                                <td>R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card de Pagamentos -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">Formas de Pagamento</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Forma</th>
                                <th>Valor</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dayUse->formaPag as $pagamento)

                            <tr>
                                <td>{{ $pagamento->formaPagamento->nome }}</td>
                                <td>R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</td>
                                <td>{{ $pagamento->observacao ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Resumo Financeiro -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">Resumo Financeiro</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <strong>Subtotal:</strong> {{ $dayUse->total_formatado }}
                        </div>
                        <div class="mb-3">
                            <strong>Acréscimo:</strong> R$ {{ number_format($dayUse->acrescimo, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <strong>Desconto:</strong> R$ {{ number_format($dayUse->desconto, 2, ',', '.') }}
                        </div>
                        <div class="mb-3">
                            <strong>Total:</strong> {{ $dayUse->valor_liquido_formatado }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <strong>Pago:</strong> R$ {{ number_format($dayUse->formaPag->sum('valor'), 2, ',', '.') }}
                        </div>
                        <div class="mb-3">
                            <strong>Saldo:</strong> 
                            @php
                                $saldo = $dayUse->valor_liquido - $dayUse->formaPag->sum('valor');
                            @endphp
                            R$ {{ number_format($saldo, 2, ',', '.') }}
                            <span class="badge bg-{{ $saldo == 0 ? 'success' : 'warning' }}">
                                {{ $saldo == 0 ? 'Quitado' : 'Pendente' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('confirm-delete', () => {
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Você não poderá reverter isso!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('delete');
                    }
                });
            });
        });
    </script>
    @endpush
</div>