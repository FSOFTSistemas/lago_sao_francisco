@extends('adminlte::page')

@section('title', 'Cardápio new')

@section('content_header')
    <h1>Criar Cardápio</h1>
@stop

@section('content')
    @livewire('CardapioNew')
@stop

@section('css')

@stop

@section('js')
<script>
    window.addEventListener('fecharModalDelete', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalDeleteSessao'));
        modal.hide();
    });
</script>
@stop
