@extends('adminlte::page')

@section('title', 'Espaços')

@section('content_header')
    <h5>Lista de Espacos para Aluguel</h5>
    <hr>
@stop

@section('content')
<div class="row mb-3">
    <div class="col d-flex justify-content-start">
        <a href="{{ route('preferencias') }}" class="btn btn-success new">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="col d-flex justify-content-end">
      <button class="btn btn-success new" data-toggle="modal" data-target="#createEspacoModal">
          <i class="fas fa-plus"></i> Novo Espaço
      </button>
    </div>
</div>
    

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 1,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Valor na Semana</th>
                    <th>Valor no Final de semana</th>
                    {{-- <th>Situação</th> --}}
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($espacos as $espaco)
                    <tr>
                        <td>{{ $espaco->id }}</td>
                        <td>{{ $espaco->nome }}</td>
                        <td>R${{ $espaco->valor_semana }}</td>
                        <td>R${{ $espaco->valor_fim }}</td>
                        {{-- <td>
                            @if($espaco->status == "disponivel")
                            <p>Disponível <i class="fa-regular fa-circle-check"></i></p>
                            
                            @else
                            <p>Alugado <i class="fa-regular fa-circle-xmark"></i></p>
                            
                            @endif
                        </td> --}}
                        <td>

                          <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                          data-target="#editEspacoModal{{ $espaco->id }}">
                          ✏️
                          </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteEspacoModal{{ $espaco->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>
                    @include('espacos.modals._edit', ['espaco' => $espaco])
                    @include('espacos.modals._delete', ['espaco' => $espaco])
                @endforeach
            </tbody>
    @endcomponent
    @include('espacos.modals._create')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

@stop
