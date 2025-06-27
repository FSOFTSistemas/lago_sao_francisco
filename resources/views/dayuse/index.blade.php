@extends('adminlte::page')

@section('title', 'Day use')

@section('content_header')
    <h5>Vendas Day Use</h5>
    <hr>
@stop

@section('content')
    <div class="row mb-3">
        <div class="col d-flex justify-content-end">
            <a href="{{ route('dayuse.create') }}" class="btn btn-success new">
                <i class="fas fa-plus"></i> Novo Day Use
            </a>
        </div>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 3, 'targets' => 1],
            ['responsivePriority' => 2, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 3,
    ])
        <thead class="bg-primary text-white">
            <tr>
                <th>ID</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dayuses as $dayuse)
                <tr>
                    <td>{{ $dayuse->id }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($dayuse->data)->format('d/m/Y') }}</td>
                    <td>{{ $dayuse->cliente->tipo == 'PJ' ? $dayuse->cliente->apelido_nome_fantasia : $dayuse->cliente->nome_razao_social }}
                    </td>
                    <td>{{ $dayuse->vendedor->nome }}</td>
                    <td>

                        <form action="{{ route('dayuse.show', $dayuse->id) }}" method="GET" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" title="Visualizar">
                                👁️
                            </button>
                        </form>


                        <button type="button" class="btn btn-danger btn-sm" title="Excluir"
                            onclick="confirmarExclusaoDayUse({{ $dayuse->id }})">
                            🗑️
                        </button>
                    </td>
                </tr>

                <!-- Modal para senha do supervisor -->
                <div class="modal fade" id="modalSenhaSupervisor" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form id="formSenhaSupervisor">
                            @csrf
                            <input type="hidden" id="dayuse_id_modal" name="dayuse_id">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel">Confirmação de Supervisor</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Informe a senha do supervisor para confirmar a exclusão:</p>
                                    <input type="password" class="form-control" name="senha" required>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </tbody>
    @endcomponent
    <script>
function confirmarExclusaoDayUse(dayuseId) {
    Swal.fire({
        title: 'Autenticação do Supervisor',
        text: 'Digite a senha do supervisor para confirmar a exclusão do Day Use.',
        input: 'password',
        inputLabel: 'Senha do Supervisor',
        inputPlaceholder: 'Digite a senha',
        inputAttributes: {
            autocapitalize: 'off',
            autocorrect: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        preConfirm: (senha) => {
            return fetch("{{ route('dayuse.verificaSupervisor') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    senha: senha,
                    dayuse_id: dayuseId
                })
            })
            .then(response => {
                console.log('aqui', response)
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message);
                    });
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(error.message);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Excluído!',
                text: result.value.message
            }).then(() => {
                location.reload(); // recarrega para refletir exclusão
            });
        }
    });
}
</script>



@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
@stop

@section('css')
    <style>
        .new {
            background-color: #679A4C !important;
            border: none !important;
        }

        .new:hover {
            background-color: #3e7222 !important;
        }
    </style>
@stop
