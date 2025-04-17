@extends('adminlte::page')

@section('title', 'Hóspedes')

@section('content_header')
    <h5>Lista de Hóspedes</h5>
    <hr>
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{route('hospede.create')}}" class="btn btn-success new">
            <i class="fas fa-plus"></i> Novo Hóspede
        </a>
    </div>

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => 3],
            ['responsivePriority' => 5, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 0,
    ])

            <thead class="bg-primary text-white">
                <tr>
                    <th>Ativo?</th>
                    <th>Nome completo</th>
                    <th>Email</th>
                    <th>Fone</th>
                    <th>Cidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($hospede as $hospede)
                @continue($hospede->nome === 'Bloqueado')
                    <tr>
                        <td>
                          @if($hospede->status == true)
                          <i class="fa-regular fa-circle-check"></i>
                          @else
                          <i class="fa-regular fa-circle-xmark"></i>
                          @endif
                        </td>
                        <td>
                          <a id="editlink" href="{{ route('hospede.edit', $hospede->id) }}">
                            {{ $hospede->nome }}
                          </a>
                        </td>
                        <td>{{ $hospede->email }}</td>
                        <td>{{ $hospede->telefone }}</td>
                        <td>{{ $hospede->endereco?->cidade ?? '' }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteHospedeModal{{ $hospede->id }}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    @include('hospede.modals._delete', ['hospede' => $hospede])
                @endforeach
            </tbody>
    @endcomponent

    @include('components.endereco-modal')
@stop

@section('css')
<style>

</style>
<style>
    .new {
        background-color: var(--green-1) !important;
        border: none !important;
    }
    .new:hover{
        background-color: var(--green-2) !important;
    }
</style>