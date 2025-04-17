<div class="modal fade" id="editContaCorrenteModal{{$contaCorrente->id}}" tabindex="-1" aria-labelledby="editContaCorrenteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContaCorrenteModalLabel">Atualizar Conta Corrente</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editContaCorrenteForm" action="{{ route('contaCorrente.update',$contaCorrente->id) }}" method="POST" >
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="numeroConta">Número da Conta:</label>
                        <input type="text" class="form-control" id="numeroConta" name="numero_conta" value="{{$contaCorrente->numero_conta}}" required>
                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label for="titular">Titular:</label>
                            <input type="text" class="form-control" id="titular" name="titular" value="{{$contaCorrente->titular}}" required>
                        </div>

                    </div>
                    <div class="mb-3">
                        <label for="saldo">Saldo:</label>
                        <input type="text" class="form-control" id="saldo" name="saldo" value="{{$contaCorrente->saldo}}" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" value="{{$contaCorrente->descricao}}" required>
                    </div>


                    <div class="mb-3">
                        <label for="banco">Banco:</label>
                        <select class="form-control" id="banco" name="banco_id" required>
                            <option value="">Selecione</option>
                            @foreach ($banco as $banco)
                                <option value="{{ $banco->id }}" 
                                    {{ old('banco_id', $contaCorrente->banco_id) == $banco->id ? 'selected' : '' }}>
                                    {{ $banco->descricao }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                    </form>
            </div>

        </div>
    </div>
</div>

