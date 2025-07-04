<form wire:submit.prevent="savePayments">
   <h5 class="mt-4">Adicionar Souvenir</h5>

<div class="row mb-3">
    <div class="col-md-6">
        <label>Souvenir</label>
        <select class="form-control" wire:model="souvenirSelecionadoId">
            <option value="">Selecione...</option>
           @foreach($souvenirs as $souvenir)
    @php
        $quantidadeAdicionada = collect($souvenirsAdicionados)
            ->where('id', $souvenir->id)
            ->sum('quantidade');

        $estoqueRestante = $souvenir->estoque - $quantidadeAdicionada;
    @endphp

    <option value="{{ $souvenir->id }}">
        {{ $souvenir->descricao }} 
        (R${{ number_format($souvenir->valor, 2, ',', '.') }}, 
        Estoque disponível: {{ max($estoqueRestante, 0) }})
    </option>
@endforeach
        </select>
        @error('souvenirSelecionadoId') 
            <span class="text-danger">{{ $message }}</span> 
        @enderror
    </div>

    <div class="col-md-3">
        <label>Quantidade</label>
       <input type="number" min="1"
       class="form-control"
       wire:model.lazy="souvenirQuantidade"
       @if(!is_null($this->estoqueDisponivel)) max="{{ $this->estoqueDisponivel }}" @endif>

@if(!is_null($estoqueDisponivel) && $souvenirQuantidade > $estoqueDisponivel)
    <span class="text-danger">Estoque insuficiente! Máximo: {{ $estoqueDisponivel }}</span>
@endif

        @error('souvenirQuantidade') 
            <span class="text-danger">{{ $message }}</span> 
        @enderror
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-success w-100" type="button" wire:click="addSouvenir">
            Adicionar
        </button>
    </div>
</div>

@if(count($souvenirsAdicionados) > 0)
    <ul class="list-group mb-3">
        @foreach($souvenirsAdicionados as $index => $souvenir)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $souvenir['descricao'] }}
                - {{ $souvenir['quantidade'] }} x R${{ number_format($souvenir['valor_unitario'], 2, ',', '.') }}
                = <strong>R${{ number_format($souvenir['valor_total'], 2, ',', '.') }}</strong>

                <button type="button" class="btn btn-danger btn-sm ml-3" wire:click="removeSouvenir({{ $index }})">
                    Remover
                </button>
            </li>
        @endforeach
    </ul>

    <div class="form-group row">
        <label class="col-md-3 label-control">Total Souvenirs:</label>
        <div class="col-md-6 ">
            <input type="text" class="form-control" 
                   value="R$ {{ number_format($subtotalSouvenir, 2, ',', '.') }}" readonly>
        </div>
    </div>
@endif



    <div class="form-group row">
        <label for="subtotalItems" class="col-md-3 label-control">Subtotal (Day Use)</label>
        <div class="col-md-6">
            <input type="text" id="subtotalItems" class="form-control" value="R$ {{ number_format($itemSubtotal, 2, ',', '.') }}" readonly>
        </div>
    </div>

    <div class="form-group row">
        <label for="acrescimo" class="label-control col-md-3">Acréscimo</label>
        <div class="col-md-6">
            <input type="number" step="0.01" class="form-control" id="acrescimo" wire:model.live.debounce.1000ms="acrescimo" min="0">
            @error('acrescimo') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
    
    <div class="form-group row">
        <label for="desconto" class="label-control col-md-3">Desconto</label>
        <div class="col-md-6">
            <input type="number" step="0.01" class="form-control" id="desconto" wire:model.live.debounce.1000ms="desconto" min="0">
            @error('desconto') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="form-group row">
        <label for="finalTotal" class="label-control col-md-3">Total a Pagar</label>
        <div class="col-md-6">
            <input type="text" id="finalTotal" class="form-control form-control-lg text-success fw-bold" value="R$ {{ number_format($finalPagamentoTotal, 2, ',', '.') }}" readonly>
        </div>
    </div>

    <hr>

    {{-- Adicionar Pagamento --}}
    <h5 class="mt-4">Adicionar Pagamento</h5>
    <br>
    <div class="row form-group mb-3 ">
        <div class="col-md-6">
            <label for="formaDePagamento" class="form-label">Método de Pagamento</label>
            <select id="formaDePagamento" class="form-select form-control" wire:model="metodoSelecionadoID">
                <option value="">Selecione</option>
                @foreach($formaPagamento as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->descricao }}</option>
                @endforeach
            </select>
            @error('metodoSelecionadoID') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-4">
            <label for="pagamentoValor" class="form-label">Valor</label>
            <input type="number" step="0.01" class="form-control" id="pagamentoValor" wire:model.live="pagamentoValor" min="0.01"  {{ $restante == 0 ? 'readonly' : '' }}>
            <div class="text-danger" style="min-height: 20px;">
                @error('pagamentoValor') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="col-md-2">
             <label style="visibility: hidden" class="form-label">.</label>
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
