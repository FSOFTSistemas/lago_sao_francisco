@extends('adminlte::page')

@section('title', 'Day Use')

@section('content_header')
    <h5>Day Use</h5>
    <hr>
@stop

@section('content')
    @livewire('ShowDayUse', ['id' => $id ?? null])
@stop
