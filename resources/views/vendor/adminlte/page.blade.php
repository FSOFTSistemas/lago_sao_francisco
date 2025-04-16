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
    .brand-link {
        text-decoration: none;
        color: #fff !important;
    }
    .card-header, .green {
        background-color: #679A4C !important;
        color: #fff !important;
    }
    .card-footer {
        text-align: right
    }
    .green:hover{
        background-color: #3e7222 !important;
    }
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
    h5 {
        text-transform: uppercase;
        color: var(--green-2) !important;
    }
    .label-control{
    text-align: right
  }



  @media (max-width: 768px) {
      .label-control{
        text-align: start
      }
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        title: 'Sucesso!',
        text: "{{ session('success') }}",
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

    @stack('js')
    @yield('js')
@stop
