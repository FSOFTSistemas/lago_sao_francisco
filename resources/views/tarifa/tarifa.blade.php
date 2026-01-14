@extends('adminlte::page')

@section('title', 'Tarifas')

@section('content_header')
    <h5>Gestão de Tarifas por Categoria</h5>
    <hr>
@stop

@section('content')
<div class="row mb-3">
  <div class="col d-flex justify-content-start">
      <a href="{{ route('preferencias') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Voltar
      </a>
  </div>
</div>

<div id="accordion">
    @foreach ($categorias as $categoria)
        <div class="card mb-2">
            <div class="card-header d-flex justify-content-between align-items-center p-2" id="heading{{ $categoria->id }}">
                <h5 class="mb-0">
                    {{-- Removi o data-parent do comportamento, mas mantive o toggle --}}
                    <button class="btn btn-link text-dark font-weight-bold text-decoration-none" data-toggle="collapse" data-target="#collapse{{ $categoria->id }}" aria-expanded="true" aria-controls="collapse{{ $categoria->id }}">
                        <i class="fas fa-bed mr-2"></i> {{ $categoria->titulo }} 
                        <span class="badge badge-info ml-2">{{ $categoria->tarifas->count() }} Tarifas</span>
                    </button>
                </h5>
                
                <a href="{{ route('tarifa.create', ['categoria_id' => $categoria->id]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Nova Tarifa
                </a>
            </div>

            {{-- 
                ALTERAÇÃO AQUI:
                Removi: data-parent="#accordion"
                Mantive: class="collapse {{ $loop->first ? 'show' : '' }}" para o primeiro já vir aberto
            --}}
            <div id="collapse{{ $categoria->id }}" class="collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $categoria->id }}">
                <div class="card-body p-0">
                    @if($categoria->tarifas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Nome da Tarifa</th>
                                        <th>Tipo</th>
                                        <th>Validade</th>
                                        <th>Alterado em</th>
                                        <th class="text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categoria->tarifas as $tarifa)
                                        <tr>
                                            <td>
                                                @if($tarifa->ativo)
                                                    <span class="badge badge-success">Ativa</span>
                                                @else
                                                    <span class="badge badge-secondary">Inativa</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $tarifa->nome }}</strong>
                                                @if($tarifa->observacoes)
                                                    <br><small class="text-muted">{{ Str::limit($tarifa->observacoes, 30) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tarifa->alta_temporada)
                                                    <span class="badge badge-warning"><i class="fas fa-sun"></i> Alta Temp.</span>
                                                @else
                                                    <span class="badge badge-light border">Padrão</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tarifa->alta_temporada && $tarifa->data_inicio)
                                                    <small>
                                                        {{ \Carbon\Carbon::parse($tarifa->data_inicio)->format('d/m') }} até 
                                                        {{ \Carbon\Carbon::parse($tarifa->data_fim)->format('d/m') }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $tarifa->updated_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-right">
                                                <a href="{{ route('tarifa.edit', $tarifa->id) }}" class="btn btn-primary btn-sm mr-1" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteTarifaModal{{ $tarifa->id }}" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="deleteTarifaModal{{ $tarifa->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('tarifa.destroy', $tarifa->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title"><i class="fas fa-trash"></i> Confirmar Exclusão</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Tem certeza que deseja excluir a Tarifa <strong>{{ $tarifa->nome }}</strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-danger">Excluir</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-3 text-muted">
                            Nenhuma tarifa cadastrada para esta categoria.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@stop

@section('js')
    <script>
        // Opcional: Manter o estado do accordion aberto se recarregar a página
    </script>
@stop

@section('css')
<style>
    .card-header .btn-link {
        width: 100%;
        text-align: left;
    }
    .card-header .btn-link:hover {
        text-decoration: none;
        color: #0056b3;
    }
</style>
@stop