@php
    $saldoReceber = max((float) $excursao->total - (float) $excursao->recebimentos
        ->whereNotNull('fluxo_caixa_id')
        ->whereNull('fluxo_cancelamento_id')
        ->sum('valor'), 0);
    $origemRecebimento = (string) old('_receber_excursao_id') === (string) $excursao->id;
    $iniciarAposRecebimento = $origemRecebimento && (bool) old('iniciar_apos_recebimento');
@endphp

<div class="modal fade" id="modalReceberExcursao{{ $excursao->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('eventos.excursoes.recebimentos.store', $excursao) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_receber_excursao_id" value="{{ $excursao->id }}">
                <input type="hidden" class="iniciar-apos-recebimento" name="iniciar_apos_recebimento" value="{{ $iniciarAposRecebimento ? 1 : 0 }}">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title titulo-modal-recebimento">
                        <i class="fas fa-{{ $iniciarAposRecebimento ? 'play' : 'hand-holding-usd' }} mr-2"></i>
                        {{ $iniciarAposRecebimento ? 'Receber saldo e iniciar excursão' : 'Receber excursão #'.$excursao->id }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <div class="d-flex justify-content-between">
                            <span>Saldo a receber</span>
                            <strong class="text-success">R$ {{ number_format($saldoReceber, 2, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="receber_valor_{{ $excursao->id }}_display">Valor recebido <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                            <input type="text" id="receber_valor_{{ $excursao->id }}_display"
                                class="form-control recebimento-excursao-valor-display @if($origemRecebimento) @error('valor') is-invalid @enderror @endif"
                                data-money-target="receber_valor_{{ $excursao->id }}" inputmode="numeric"
                                value="{{ $origemRecebimento && old('valor') ? number_format((float) old('valor'), 2, ',', '.') : '' }}"
                                placeholder="0,00" required>
                            <input type="hidden" id="receber_valor_{{ $excursao->id }}" name="valor"
                                value="{{ $origemRecebimento ? old('valor') : '' }}">
                            @if ($origemRecebimento) @error('valor') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="receber_forma_{{ $excursao->id }}">Forma de pagamento <span class="text-danger">*</span></label>
                        <select id="receber_forma_{{ $excursao->id }}" name="forma_pagamento_id"
                            class="form-control forma-pagamento-recebimento @if($origemRecebimento) @error('forma_pagamento_id') is-invalid @enderror @endif" required>
                            <option value="">Selecione</option>
                            @foreach ($formasPagamento as $forma)
                                <option value="{{ $forma->id }}" data-exige-comprovante="{{ $forma->movimentoSlug() === 'pix' ? '1' : '0' }}"
                                    @selected($origemRecebimento && (string) old('forma_pagamento_id') === (string) $forma->id)>
                                    {{ $forma->descricao }}
                                </option>
                            @endforeach
                        </select>
                        @if ($origemRecebimento) @error('forma_pagamento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                    </div>

                    <div class="form-group mb-0 comprovante-recebimento-group d-none">
                        <label for="receber_comprovante_{{ $excursao->id }}">
                            Comprovante <span class="text-danger comprovante-recebimento-obrigatorio d-none">*</span>
                        </label>
                        <input type="file" id="receber_comprovante_{{ $excursao->id }}" name="comprovante"
                            class="form-control-file @if($origemRecebimento) @error('comprovante') is-invalid @enderror @endif"
                            accept="image/*,.pdf">
                        @if ($origemRecebimento) @error('comprovante') <div class="text-danger small mt-1">{{ $message }}</div> @enderror @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success confirmar-recebimento">
                        <i class="fas fa-{{ $iniciarAposRecebimento ? 'play' : 'save' }} mr-1"></i>
                        {{ $iniciarAposRecebimento ? 'Receber e iniciar' : 'Registrar recebimento' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
