<div class="container py-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">DayUse #{{ $dayUseId}}</h2>
    </div>

    <!-- Card Principal -->
    <div class="card shadow-sm mb-4 w-100">
        <div class="card-header bg-verde text-white">
            <h4 class="mb-0">Informações Gerais</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Cliente:</strong> {{ $dayUse->cliente->nome_razao_social?? '' }}
                    </div>
                    <div class="mb-3">
                        <strong>Data:</strong> {{ $this->dataFormatada }}
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
    <div class="card shadow-sm mb-4 w-100">
        <div class="card-header bg-verde text-white">
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
                        @foreach($itens??[] as $item)
                        <tr>
                            <td>{{ $item['descricao'] }}</td>
                            <td>{{ $item['quantidade'] }}</td>
                            <td>R$ {{ number_format($item['valor'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item['valor_total'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card de Pagamentos -->
    <div class="card shadow-sm mb-4 w-100">
        <div class="card-header bg-verde text-white">
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
                        @foreach($dayUse->formaPag??[] as $pagamento)
                        <tr>
                            <td>{{ $pagamento->formaPagamento->descricao }}</td>
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
    <div class="card shadow-sm w-100">
        <div class="card-header bg-verde text-white">
            <h4 class="mb-0">Resumo Financeiro</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <strong>Subtotal:</strong> R$ {{ number_format($valorLiquido, 2, ',', '.') }}
                    </div>
                    <div class="mb-3">
                        <strong>Acréscimo:</strong> R$ {{ number_format($dayUse->acrecimo, 2, ',', '.') }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <strong>Desconto:</strong> R$ {{ number_format($dayUse->desconto, 2, ',', '.') }}
                    </div>
                    <div class="mb-3">
                        <strong>Total:</strong> R$ {{ number_format($dayUse->total, 2, ',', '.') }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <strong>Pago:</strong> R$ {{ number_format($valorPago, 2, ',', '.') }}
                    </div>
                    <div class="mb-3">
                        <strong>Saldo:</strong> 
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

@push('css')
<style>
    .bg-verde {
        background-color: #3e7222;
        background: linear-gradient(to right, #3e7222, #4a8a29);
    }
    .container {
        max-width: 100%;
        padding: 0 15px;
    }
    .card {
        width: 100%;
    }
</style>
@endpush

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