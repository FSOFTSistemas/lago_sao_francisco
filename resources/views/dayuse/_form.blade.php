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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('hideSidebarAndButton', () => {
                console.log('Evento hideSidebarAndButton recebido no AdminLTE page!');

                const sidebar = document.querySelector('.main-sidebar');
                if (sidebar) {
                    sidebar.style.display = 'none';
                    console.log('Sidebar oculta.');
                } else {
                    console.log('Sidebar não encontrada.');
                }

                const contentWrapper = document.querySelector('.content-wrapper');
                if (contentWrapper) {
                    contentWrapper.style.marginLeft = '0';
                    console.log('Content wrapper ajustado.');
                } else {
                    console.log('Content wrapper não encontrado.');
                }
                
                const menuToggleButton = document.querySelector('[data-widget="pushmenu"]');
                if (menuToggleButton) {
                    menuToggleButton.style.display = 'none';
                    console.log('Botão de toggle oculto.');
                } else {
                    console.log('Botão de toggle não encontrado.');
                }
            });
        });
    </script>

@livewireScripts
@stop