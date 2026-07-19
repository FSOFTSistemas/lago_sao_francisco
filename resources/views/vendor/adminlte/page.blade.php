@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
@endpush

@section('adminlte_css')
<style>
    .main-sidebar {
        background-color: #3e7222 !important;
    }
    .main-sidebar .nav-link {
        color: #fff !important;
    }
    .main-sidebar .nav-link:hover {
        color: #fff !important;
    }
    .main-sidebar .nav-link.active {
        color: #fff !important;
    }
    .nav-sidebar>.nav-item>.nav-link.active{
        background-color: #679A4C !important;
    }
    .nav-treeview>.nav-item>.nav-link.active {
        background-color: #fff !important;
        color: #679A4C !important;
    }
    .nav-treeview>.nav-item>.nav-link.active:hover {
        background-color: #679A4C;
    }

    /* Menu (nível 1): mais peso, ícone em destaque, barra indicadora quando ativo */
    .nav-sidebar > .nav-item > .nav-link {
        font-size: .93rem;
        font-weight: 600;
        letter-spacing: .01em;
        padding: .68rem 1rem;
        border-left: 3px solid transparent;
        transition: background-color .15s ease, border-color .15s ease;
    }
    .nav-sidebar > .nav-item > .nav-link > i:first-child {
        font-size: 1.05rem;
        width: 1.6rem;
        opacity: .95;
    }
    .nav-sidebar > .nav-item > .nav-link:hover {
        background-color: rgba(255, 255, 255, .08) !important;
    }
    .nav-sidebar > .nav-item > .nav-link.active {
        border-left-color: #fff;
    }
    .nav-sidebar > .nav-item.menu-open > .nav-link {
        border-left-color: rgba(255, 255, 255, .5);
    }

    /* Submenu (nível 2): fonte menor, cor mais suave, linha-guia de indentação */
    .nav-treeview {
        margin-left: 1.15rem;
        border-left: 1px solid rgba(255, 255, 255, .18);
    }
    .nav-treeview .nav-link {
        font-size: .83rem;
        font-weight: 400;
        color: rgba(255, 255, 255, .78) !important;
        padding: .45rem .75rem .45rem 1.4rem;
    }
    .nav-treeview .nav-link > i:first-child {
        font-size: .72rem;
        width: 1.3rem;
        opacity: .8;
    }
    .nav-treeview .nav-link:hover {
        color: #fff !important;
        background-color: rgba(255, 255, 255, .06) !important;
    }
    .nav-treeview > .nav-item > .nav-link.active {
        font-weight: 600;
    }

    /* Cabeçalhos de agrupamento dentro do submenu (ex.: "Day Use", "Aluguel de Espaços") */
    .nav-treeview .nav-header {
        color: rgba(255, 255, 255, .5);
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: .8rem 1rem .3rem 1.4rem;
    }

    .brand-link {
        text-decoration: none;
        color: #fff !important;
    }
    .green {
        background-color: #679A4C !important;
        color: #fff !important;
    }
    .card-footer {
        text-align: right
    }
    /* .green:hover{
        background-color: #3e7222 !important;
    } */
    .new {
        background-color: var(--green-1) !important;
        border: none !important;
    }
    .new:hover{
        background-color: var(--green-2) !important;
    }
    .btn-primary {
        background-color: var(--green-1) !important;
        border: none !important;
    }
    .btn-primary:hover {
        background-color: var(--green-2) !important;
    }
    h5 {
        text-transform: uppercase;
        color: var(--green-2) !important;
    }
    .label-control{
    text-align: right
  }

  #editlink {
        text-decoration: none;
        color: var(--green-1);
        font-weight: 600;
    }
    #editlink:hover {
        color: var(--green-2);
    }

    .editlink {
        text-decoration: none;
        color: var(--green-1);
        font-weight: 600;
    }
    .editlink:hover {
        color: var(--green-2);
    }
        .form-switch {
        padding-left: 3em;
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .form-switch .form-check-input {
        width: 3.5rem;
        height: 1.75rem;
        background-color: #dee2e6;
        border-radius: 1.75rem;
        position: relative;
        transition: background-color 0.3s ease-in-out;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
    }
    
    .form-switch .form-check-input:checked {
        background-color: var(--green-1);
    }
    
    .form-switch .form-check-input::before {
        content: "";
        position: absolute;
        width: 1.5rem;
        height: 1.5rem;
        top: 0.125rem;
        left: 0.125rem;
        border-radius: 50%;
        background-color: white;
        transition: transform 0.3s ease-in-out;
    }
    
    .form-switch .form-check-input:checked::before {
        transform: translateX(1.75rem);
    }



  @media (max-width: 768px) {
      .label-control{
        text-align: start
      }
    }

    /* Design system reutilizável para telas de relatório/listagem (cards de resumo + tabela) */
    .stat-cards-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        border: 1px solid rgba(0, 0, 0, .04);
    }
    .stat-card-dark {
        background: #212529;
        color: #fff;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.15rem;
    }
    .stat-body {
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }
    .stat-number {
        font-size: 1.6rem;
        font-weight: 700;
    }
    .stat-label {
        font-size: .8rem;
        opacity: .7;
    }

    .report-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        border: 1px solid rgba(0, 0, 0, .04);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .report-card-header {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eee;
        font-weight: 700;
        color: #333;
    }
    .report-card-header i {
        color: #3e7222;
    }

    .report-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: .75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #eee;
        background: #fafafa;
    }
    .report-filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .6rem;
    }
    .report-date-field {
        position: relative;
        display: flex;
        align-items: center;
    }
    .report-date-field i {
        position: absolute;
        left: .75rem;
        color: #999;
        font-size: .8rem;
        pointer-events: none;
    }
    .report-date-field .form-control {
        border-radius: 20px;
        padding-left: 2rem;
        border-color: #e0e0e0;
    }
    .report-date-sep {
        color: #999;
        font-size: .85rem;
    }
    .btn-report-filter {
        background-color: #3e7222;
        color: #fff;
        border-radius: 20px;
        padding: .4rem 1.1rem;
        border: none;
    }
    .btn-report-filter:hover {
        background-color: #2d5419;
        color: #fff;
    }
    .btn-report-pdf {
        background-color: #fff;
        color: #dc3545;
        border: 1px solid #dc3545;
        border-radius: 20px;
        padding: .4rem 1.1rem;
    }
    .btn-report-pdf:hover {
        background-color: #dc3545;
        color: #fff;
    }

    .report-table-wrap {
        overflow-x: auto;
    }
    .report-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .report-table thead th {
        position: sticky;
        top: 0;
        background: #fafafa;
        text-transform: uppercase;
        font-size: .72rem;
        letter-spacing: .04em;
        color: #888;
        font-weight: 700;
        padding: .8rem 1.25rem;
        border-bottom: 1px solid #eee;
        white-space: nowrap;
    }
    .report-table tbody td {
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #f2f2f2;
        vertical-align: middle;
        font-size: .9rem;
    }
    .report-table tbody tr:hover {
        background-color: #f8faf7;
    }
    .report-table tfoot td {
        padding: .85rem 1.25rem;
        background: #fafafa;
        border-top: 2px solid #eee;
    }

    .report-badge {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: .15rem .55rem;
        border-radius: 20px;
    }
    .report-avatar {
        width: 30px;
        height: 30px;
        min-width: 30px;
        border-radius: 50%;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        font-weight: 700;
    }
    .report-number-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        padding: 0 .5rem;
        border-radius: 8px;
        background: #eef3ea;
        color: #3e7222;
        font-weight: 700;
    }
    .report-subtext {
        font-size: .72rem;
        color: #999;
        margin-top: .2rem;
    }

    .report-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #999;
    }
    .report-empty i {
        font-size: 2.2rem;
        margin-bottom: .75rem;
        opacity: .5;
    }
    .report-empty h5 {
        color: #666 !important;
        margin-bottom: .3rem;
        text-transform: none;
    }
    .report-empty p {
        font-size: .85rem;
        margin: 0;
    }
</style>
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


@if(session('success'))
<script>
    Swal.fire({
        title: 'Sucesso!',
        html: `{!! session('success') !!}`,
        icon: 'success',
        confirmButtonText: 'OK'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        title: 'Erro!',
        text: "{{ session('error') }}",
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
@endif

@if (session('sweet_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Atenção!',
                text: '{{ session('sweet_error') }}',
                confirmButtonColor: '#d33'
            });
        });
    </script>
@endif


    @stack('js')
    @yield('js')
@stop
