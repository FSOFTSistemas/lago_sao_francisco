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
<style>
    .select2-container--default .select2-selection--single {
        height: calc(2.875rem + 2px);
        padding: 0.5rem 1rem;
        font-size: 1.25rem;
    }

    .select2-container--default .select2-results__option {
        font-size: 1.25rem;
        padding: 0.5rem 1rem;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        font-size: 1.25rem;
        padding: 0.5rem;
    }
</style>
@stop

@section('js')
@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@stop