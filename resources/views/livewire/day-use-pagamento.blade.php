<form wire:submit.prevent="savePayments">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="subtotalItems" class="form-label">Subtotal (Itens)</label>
            <input type="text" id="subtotalItems" class="form-control" value="R$ {{ number_format($itemSubtotal, 2, ',', '.') }}" readonly>
        </div>
        <div class="col-md-6">
            <label for="acrescimo" class="form-label">Acréscimo</label>
            <input type="number" step="0.01" class="form-control" id="acrescimo" wire:model.live.debounce.1000ms="acrescimo" min="0">
            @error('acrescimo') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="desconto" class="form-label">Desconto</label>
            <input type="number" step="0.01" class="form-control" id="desconto" wire:model.live.debounce.1000ms="desconto" min="0">
            @error('desconto') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
            <label for="finalTotal" class="form-label">Total a Pagar</label>
            <input type="text" id="finalTotal" class="form-control form-control-lg text-success fw-bold" value="R$ {{ number_format($finalPagamentoTotal, 2, ',', '.') }}" readonly>
        </div>
    </div>

    <hr>

    {{-- Adicionar Pagamento --}}
    <h5 class="mt-4">Adicionar Pagamento</h5>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="formaDePagamento" class="form-label">Método de Pagamento</label>
            <select id="formaDePagamento" class="form-select" wire:model="metodoSelecionadoID">
                <option value="">Selecione</option>
                @foreach($formaPagamento as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->descricao }}</option>
                @endforeach
            </select>
            @error('metodoSelecionadoID') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-4">
            <label for="pagamentoValor" class="form-label">Valor</label>
            <input type="number" step="0.01" class="form-control" id="pagamentoValor" wire:model="pagamentoValor" min="0.01">
            @error('pagamentoValor') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-success w-100" wire:click="addPayment">Adicionar</button>
        </div>
    </div>

    {{-- Lista de Pagamentos Adicionados --}}
    <h5 class="mt-4">Pagamentos Adicionados</h5>
    @if(count($pagamentosAtuais) > 0)
        <ul class="list-group mb-3">
            @foreach($pagamentosAtuais as $index => $pagamento)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $pagamento['descricao'] }}: R$ {{ number_format($pagamento['valor'], 2, ',', '.') }}
                    <button type="button" class="btn btn-danger btn-sm" wire:click="removePayment({{ $index }})">Remover</button>
                </li>
            @endforeach
        </ul>
    @else
        <p>Nenhum pagamento adicionado ainda.</p>
    @endif

    {{-- Valor Restante a Pagar --}}
    <div class="row mt-4">
        <div class="col-12 text-center">
            <label for="restanteField" class="form-label fs-3">Restante a Pagar</label>
            <input type="text" id="restanteField" class="form-control form-control-lg text-center {{ $restante > 0 ? 'text-danger' : 'text-success' }} fw-bold" value="R$ {{ number_format($restante, 2, ',', '.') }}" readonly>
            @error('restante') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Finalizar Pagamento</button>
</form>
