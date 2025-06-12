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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@stop

@section('js')
@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@stop