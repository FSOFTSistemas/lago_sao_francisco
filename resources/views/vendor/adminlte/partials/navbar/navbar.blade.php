@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Documentos rápidos --}}
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Documentos e relatórios rápidos">
                <i class="fas fa-clipboard-list"></i>
            </a>
            <div class="dropdown-menu">
                <span class="dropdown-item dropdown-header">Documentos rápidos</span>
                <div class="dropdown-divider"></div>
                <a href="{{ route('documentos.fnrh_branco') }}" target="_blank" class="dropdown-item">
                    <i class="fas fa-id-card mr-2 text-muted"></i> FNRH em branco
                </a>
                <a href="#" data-toggle="modal" data-target="#modalRelatorioCafeDaManha" class="dropdown-item">
                    <i class="fas fa-mug-hot mr-2 text-muted"></i> Relatório de Café da Manhã
                </a>
                <a href="#" data-toggle="modal" data-target="#modalRelatorioMovimentacao" class="dropdown-item">
                    <i class="fas fa-calendar-week mr-2 text-muted"></i> Previsão de Movimentação
                </a>
                {{-- Novos documentos/relatórios podem ser adicionados aqui como mais um <a class="dropdown-item"> --}}
            </div>
        </li>

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

{{-- Modal: gerar relatório de café da manhã por data --}}
<div class="modal fade" id="modalRelatorioCafeDaManha" tabindex="-1" role="dialog"
    aria-labelledby="modalRelatorioCafeDaManhaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('relatorios.cafe.pdf') }}" method="GET" target="_blank" id="formRelatorioCafeDaManha">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelatorioCafeDaManhaLabel">
                        <i class="fas fa-mug-hot mr-1"></i> Relatório de Café da Manhã
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="dataCafeDaManha">Data</label>
                        <input type="date" class="form-control" id="dataCafeDaManha" required
                            value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <input type="hidden" name="data_inicio" id="dataCafeDaManhaInicio">
                    <input type="hidden" name="data_fim" id="dataCafeDaManhaFim">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Gerar PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal: gerar relatório de previsão de movimentação por período --}}
<div class="modal fade" id="modalRelatorioMovimentacao" tabindex="-1" role="dialog"
    aria-labelledby="modalRelatorioMovimentacaoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('relatorios.movimentacao.pdf') }}" method="GET" target="_blank">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRelatorioMovimentacaoLabel">
                        <i class="fas fa-calendar-week mr-1"></i> Previsão de Movimentação
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="dataMovimentacaoInicio">Data Início</label>
                        <input type="date" class="form-control" id="dataMovimentacaoInicio" name="data_inicio" required
                            value="{{ now()->startOfWeek()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="dataMovimentacaoFim">Data Fim</label>
                        <input type="date" class="form-control" id="dataMovimentacaoFim" name="data_fim" required
                            value="{{ now()->endOfWeek()->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Gerar PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('formRelatorioCafeDaManha')?.addEventListener('submit', function () {
        var data = document.getElementById('dataCafeDaManha').value;
        document.getElementById('dataCafeDaManhaInicio').value = data;
        document.getElementById('dataCafeDaManhaFim').value = data;
    });
</script>
