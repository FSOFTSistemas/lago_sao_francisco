@extends('adminlte::page')

@section('title', 'Tarifas')

@section('content_header')
    <h5>Tarifas do Hotel</h5>
    <hr>
@stop

@section('content')
<div class="row mb-3">
  <div class="col d-flex justify-content-start">
      <a href="{{ route('preferencias') }}" class="btn btn-success new">
          <i class="fas fa-arrow-left"></i> Voltar
      </a>
  </div>
</div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 4,
    ])
        <thead class="table-primary">
            <tr>
                <th>Ativo?</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Alterado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tarifa as $tarifa)
                <tr>
                    <td>
                      @if($tarifa->ativo == true)
                          <i class="fa-regular fa-circle-check"></i>
                      @else
                          <i class="fa-regular fa-circle-xmark"></i>
                      @endif
                    </td>
                    <td>
                        <a id="editlink" href="{{ route('tarifa.edit', $tarifa->id) }}">
                            {{ $tarifa->nome }}
                          </a>
                    </td>
                    <td>{{ $tarifa->categoria->titulo }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($tarifa->updated_at)->format('d/m/Y H:i') }}</td>
                    <td>
                        {{-- <!-- Botão Editar -->
                        <a href="{{route('tarifa.edit', $tarifa->id)}}" class="btn btn-warning btn-sm">
                          ✏️
                      </a> --}}
                    </td>
                </tr>

                <!-- Modal Excluir -->
                <div class="modal fade" id="deleteTarifaModal{{ $tarifa->id }}" tabindex="-1"
                    aria-labelledby="deleteTarifaModalLabel{{ $tarifa->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('tarifa.destroy', $tarifa->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-trash"></i> Confirmar Exclusão
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Tem certeza que deseja excluir a Tarifa
                                    <strong>{{ $tarifa->nome }}</strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </tbody>
    @endcomponent
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@stop

@section('css')
<style>
    .new {
        background-color: #679A4C !important;
        border: none !important;
    }
    .new:hover{
        background-color: #3e7222 !important;
    }
</style>
