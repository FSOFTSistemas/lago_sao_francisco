<!-- Modal de NCM -->
<div class="modal fade" id="modalNCM" tabindex="-1" aria-labelledby="modalNCMLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNCMLabel">Selecionar NCM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-6">
                        <input type="text" id="filtroCodigo" class="form-control" placeholder="Filtrar por código">
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="filtroDescricao" class="form-control"
                            placeholder="Filtrar por descrição">
                    </div>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered table-hover" id="tabelaNCM">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ncm as $item)
                                <tr ondblclick="selecionarNCM('{{ $item->ncm }}')">
                                    <td>{{ $item->ncm }}</td>
                                    <td>{{ $item->descricao }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
