<div class="modal fade" id="deleteContasAPagarModal{{ $contasAPagar->id }}" tabindex="-1"
    aria-labelledby="deleteContasAPagarModalLabel{{ $contasAPagar->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Cabeçalho -->
            <div class="modal-header">
                <h5 class="modal-title" id="deleteContasAPagarModalLabel{{ $contasAPagar->id }}">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <!-- Corpo -->
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a conta <strong>{{ $contasAPagar->conta_descricao }}</strong> com todas as suas parcelas?</p>

                @if($contasAPagar->parcelas && $contasAPagar->parcelas->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Parcela</th>
                                    <th>Valor</th>
                                    <th>Data de Vencimento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contasAPagar->parcelas as $parcela)
                                    <tr>
                                        <td>
                                            {{ ($parcela->numero_parcela ?? '-') . '/' . ($contasAPagar->total_parcelas ?? '-') }}
                                        </td>
                                        <td>R$ {{ number_format($parcela->valor, 2, ',', '.') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Essa conta não possui parcelas registradas.</p>
                @endif
            </div>

            <!-- Rodapé -->
            <div class="modal-footer">
                <form action="{{ route('contasAPagar.destroy', $contasAPagar->conta_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sim, excluir tudo</button>
                </form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
