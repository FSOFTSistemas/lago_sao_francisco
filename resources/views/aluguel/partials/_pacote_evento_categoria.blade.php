@php
    $pacotesDaCategoria = ($pacotesEvento[$categoriaChave] ?? collect())->groupBy('nome');
@endphp

<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <strong>{{ $categoriaLabel }}</strong>
    </div>
    <div class="card-body">
        @forelse ($pacotesDaCategoria as $nomePacote => $anosDoPacote)
            @php
                $anosOrdenados = $anosDoPacote->sortBy('ano')->values();
                $primeiroAno = $anosOrdenados->first();
                $grupo = \Illuminate\Support\Str::slug($nomePacote);

                $selecionado = null;
                $anoSelecionado = null;
                foreach ($anosOrdenados as $opcaoAno) {
                    if ($pacotesSelecionadosArray->has($opcaoAno->id)) {
                        $selecionado = $pacotesSelecionadosArray->get($opcaoAno->id);
                        $anoSelecionado = $opcaoAno->ano;
                        break;
                    }
                }
                $quantidade = $selecionado->quantidade ?? 0;
                $observacao = $selecionado->observacao ?? '';
                $valorTotal = $selecionado->valor_total ?? 0;
                $anoAtivo = $anoSelecionado ?? $primeiroAno->ano;
                $idAtivo = $selecionado->pacote_evento_id ?? $primeiroAno->id;
            @endphp

            <div class="row mb-3 align-items-start border-bottom pb-2 pacote-evento-linha" data-grupo="{{ $grupo }}">
                <div class="col-md-4">
                    <strong>{{ $nomePacote }}</strong>
                    @if ($primeiroAno->observacao_padrao)
                        <br><small class="text-info">{{ $primeiroAno->observacao_padrao }}</small>
                    @endif
                    @if ($primeiroAno->descricao)
                        <br><small class="text-muted">{{ $primeiroAno->descricao }}</small>
                    @endif
                </div>

                <div class="col-md-2">
                    <label>Ano:</label>
                    <select class="form-control pacote-evento-ano">
                        @foreach ($anosOrdenados as $opcaoAno)
                            <option value="{{ $opcaoAno->id }}" data-valor="{{ $opcaoAno->valor }}"
                                {{ $anoAtivo == $opcaoAno->ano ? 'selected' : '' }}>
                                {{ $opcaoAno->ano }} (R$ {{ number_format($opcaoAno->valor, 2, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" class="pacote-evento-id"
                        name="pacotes_evento[{{ $grupo }}][pacote_evento_id]" value="{{ $idAtivo }}">
                </div>

                <div class="col-md-2">
                    <label>Nº de Pessoas:</label>
                    <input type="number" min="0" class="form-control pacote-evento-quantidade"
                        name="pacotes_evento[{{ $grupo }}][quantidade]" value="{{ $quantidade }}">
                </div>

                <div class="col-md-2">
                    <label>Observação:</label>
                    <input type="text" class="form-control" name="pacotes_evento[{{ $grupo }}][observacao]"
                        value="{{ $observacao }}">
                </div>

                <div class="col-md-2">
                    <label>Total:</label>
                    <input type="text" readonly class="form-control pacote-evento-total"
                        value="{{ $quantidade > 0 ? 'R$ ' . number_format($valorTotal, 2, ',', '.') : '' }}">
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Nenhum pacote cadastrado.</p>
        @endforelse
    </div>
</div>
