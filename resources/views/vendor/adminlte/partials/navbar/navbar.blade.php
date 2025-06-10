@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        <form action="{{ route('filtro.empresa') }}" method="POST" class="form-inline ml-2">
            @csrf
            <div class="input-group input-group-sm align-items-center">
                <div class="input-group-prepend mr-2">
                    <span class="input-group-text bg-white border-0 p-0 pr-2" style="font-size: 0.875rem; color: var(--green-2) !important;">
                        <label for="selectEmpresa" class="mb-0">Selecione uma Empresa:</label>
                    </span>
                </div>
                <select class="form-control form-control-navbar" id="selectEmpresa" name="empresa_id" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <option value="1" {{ session('empresa_id') == 1 ? 'selected' : '' }}>Lago</option>
                    <option value="2" {{ session('empresa_id') == 2 ? 'selected' : '' }}>Restaurante</option>
                    <option value="3" {{ session('empresa_id') == 3 ? 'selected' : '' }}>Hotel</option>
                </select>
            </div>
        </form>   
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
