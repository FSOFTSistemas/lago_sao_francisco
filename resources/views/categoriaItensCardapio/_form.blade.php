@extends('adminlte::page')

@section('title', 'Categoria new')

@section('content_header')
    <h1>Criar Categoria de Itens</h1>
@stop

@section('content')
    @livewire('CategoriaItensNew',['id'=>$id??null])
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
