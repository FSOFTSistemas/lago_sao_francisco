@extends('adminlte::page')

@section('title', 'Day Use')

@section('content_header')
    <h5>Day Use</h5>
    <hr>
@stop

@section('content')
    @livewire('DayUse', ['id' => $id ?? null])
@stop

@section('css')
@livewireStyles
@stop

@section('js')
@livewireScripts
@stop
