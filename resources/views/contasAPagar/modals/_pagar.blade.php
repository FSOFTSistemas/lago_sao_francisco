<!-- Modal de Pagamento -->
<div class="modal fade" id="pagarContasAPagarModal{{ $contasAPagar->id }}" tabindex="-1" role="dialog"
    aria-labelledby="pagarContasAPagarModalLabel{{ $contasAPagar->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('contasAPagar.pagar', $contasAPagar->id) }}" method="POST">
            @csrf
            @method('POST')
            <input type="hidden" name="id" value="{{ $contasAPagar->id ?? '' }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pagar Conta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @php
                    $valorRestante = $contasAPagar->valor - $contasAPagar->valor_pago;
                @endphp
                <div class="modal-body">
                    <p><strong>Descrição:</strong> {{ $contasAPagar->descricao }}</p>
                    <p><strong>Valor restante:</strong> R$ {{ number_format($valorRestante, 2, ',', '.') }}</p>

                    <div class="form-group">
                        <label for="data_pagamento">Data do Pagamento</label>
                        <input type="date" name="data_pagamento" class="form-control"
                            value="{{ now()->toDateString() }}" required>
                    </div>

                    <div class="form-group">
                        <label for="valor_pago">Valor a ser Pago</label>
                        <input type="number"
                            step="0.01"
                            min="0.01"
                            max="{{ number_format($valorRestante, 2, '.', '') }}"
                            name="valor_pago"
                            class="form-control"
                            value="{{ number_format($valorRestante, 2, '.', '') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="fonte_pagadora">De onde sairá o valor?</label>
                        <select name="fonte_pagadora" class="form-control" id="fonte_pagadora" required>
                            <option value="conta_corrente">Conta Corrente</option>
                            <option value="caixa">Caixa</option>
                        </select>
                    </div>

                    {{-- Select de Conta Corrente --}}
                    <div class="form-group" id="select_conta_corrente">
                        <label for="conta_corrente_id">Conta Corrente</label>
                        <select name="conta_corrente_id" class="form-control">
                            <option value="">Selecione a Conta Corrente</option>
                            @foreach($contas_corrente as $conta)
                                <option value="{{ $conta->id }}">{{ $conta->nome ?? $conta->descricao ?? 'Conta #' . $conta->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Select de Caixa --}}
                    <div class="form-group d-none" id="select_caixa">
                        <label for="caixa_id">Caixa</label>
                        <select name="caixa_id" class="form-control">
                            <option value="">Selecione o Caixa</option>
                            @foreach($caixas as $caixa)
                                <option value="{{ $caixa->id }}">{{ $caixa->nome ?? $caixa->descricao ?? 'Caixa #' . $caixa->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <script>
                        $(document).ready(function() {
                            $('#fonte_pagadora').on('change', function() {
                                let valor = $(this).val();

                                $('#select_conta_corrente').addClass('d-none');
                                $('#select_caixa').addClass('d-none');

                                if (valor === 'conta_corrente') {
                                    $('#select_conta_corrente').removeClass('d-none');
                                } else if (valor === 'caixa') {
                                    $('#select_caixa').removeClass('d-none');
                                }
                            });
                        });
                    </script>



                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirmar Pagamento</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>
