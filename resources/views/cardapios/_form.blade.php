@extends('adminlte::page')

@section('title', 'Cardápio new')

@section('content_header')
    <h5>Criar Cardápio</h5>
    <hr>
@stop

@section('content')
    @livewire('CardapioNew', ['id' => $id ?? null])
@stop

@section('css')

@stop

@section('js')
@stop
