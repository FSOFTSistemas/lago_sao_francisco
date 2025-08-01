<?php

use Illuminate\Support\Facades\Auth;

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Lago',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>Lago </b>',
    'logo_img' => 'vendor/imgs/logoSF.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/imgs/logoSF.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/imgs/logoSF.png',
            'alt' => 'Preloader Image',
            'effect' => 'animation__shake',
            'width' => 200,
            'height' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => 'content-wrapper text-sm',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'md',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => false,
        ],
        [
            'text'    => 'Lago',
            'icon'    => 'fas fa-place-of-worship',
            'submenu' => [
                ['header' => 'Day Use'],

                [
                    'text' => 'Cadastrar',
                    'icon' => 'fas fa-ticket-alt',
                    'url'  => '/dayuse/create',
                    'can'  => 'vender dayuse'
                ],
                [
                    'text' => 'Relatórios',
                    'icon' => 'fas fa-file-alt',
                    'url'  => '/dayuse',
                    'can'  => 'gerenciar dayuse'
                ],
                ['header' => 'Aluguel de Espaços'],
                [
                    'text' => 'Cadastrar',
                    'icon' => 'fas fa-campground',
                    'url'  => '/aluguel/create',
                    'can'  => 'cadastrar aluguel'
                ],
                [
                    'text' => 'Relatórios',
                    'icon' => 'fas fa-file-alt',
                    'url'  => '/aluguel',
                    'can'  => 'gerenciar aluguel'
                ],
            ],
        ],
        [
            'text'    => 'Hotel',
            'icon'    => 'fas fa-hotel',
            'can'     => 'hotel',
            'submenu' => [
                [
                    'text' => 'Home',
                    'icon' => 'fas fa-home',
                    'url'  => '/mapaQuarto',
                ],
                [
                    'text' => 'Mapa',
                    'icon' => 'fas fa-map',
                    'url'  => '/mapa',
                ],
                [
                    'text' => 'Reservas',
                    'icon' => 'fas fa-bed',
                    'url'  => '/reserva',
                ],
                [
                    'text' => 'Hóspedes',
                    'icon' => 'fas fa-user',
                    'url'  => '/hospede',
                ],
                [
                    'text' => 'Transações',
                    'icon' => 'fas fa-credit-card',
                    'url'  => '/transacao',
                ],
            ],
        ],
        [
            'text' => 'Produtos',
            'url'  => '/produto',
            'icon' => 'fas fa-boxes',
            'can'  => 'gerenciar produto'
        ],
        [
            'text' => 'Clientes',
            'url'  => '/cliente',
            'icon' => 'fa fa-handshake',
            'can'  => 'gerenciar cliente',
        ],
        [
            'text' => 'Funcionários',
            'url'  => '/funcionario',
            'icon' => 'fas fa-user',
            'can'  => 'gerenciar funcionario',
        ],
        // [
        //     'text' => '!Caixa',
        //     'url'  => '/caixa',
        //     'icon' => 'fas fa-money-bill-alt',
        //     'can'  => 'gerenciar caixa',
        // ],
        [
            'text' => 'Caixa',
            'url'  => '/fluxoCaixa',
            'icon' => 'fas fa-money-bill-alt',
            'can'  => 'gerenciar caixa',
        ],
        [
            'text' => 'Empresas',
            'url'  => '/empresa',
            'icon' => 'fas fa-building',
            'can'  => 'gerenciar empresa',
        ],
        [
            'text' => 'Fornecedores',
            'url'  => '/fornecedor',
            'icon' => 'fas fa-truck',
            'can'  => 'gerenciar fornecedor',
        ],
        [
            'text'    => 'Financeiro',
            'icon'    => 'fas fa-money-check-alt',
            'can'     => 'gerenciar financeiro',
            'submenu' => [

                [
                    'text' => 'Adiantamentos',
                    'url'  => '/adiantamento',
                    'icon' => 'fas fa-cash-register',
                    'can'  => 'gerenciar adiantamento',
                ],
                [
                    'text' => 'Banco',
                    'url'  => '/bancos',
                    'icon' => 'fas fa-university',
                    'can'  => 'gerenciar banco',
                ],


                [
                    'text'    => 'Conta Corrente',
                    'url'     => '#', // Alterado para '#' para que o item principal apenas abra o submenu.
                    'icon'    => 'fas fa-landmark', // Ícone mais representativo para "contas".
                    'can'     => 'gerenciar conta corrente',
                    'submenu' => [
                        [
                            'text'  => 'Contas', // Texto simplificado e mais direto.
                            'url'   => '/contaCorrente', 
                            'icon'  => 'fas fa-university', // Ícone específico para a lista de contas.
                            'shift' => 'ml-2'
                        ],
                        [
                            'text'  => 'Lançamentos',
                            'url'   => '/lancamentos', // URL padronizada e mais limpa.
                            'icon'  => 'fas fa-list-ul',
                            'shift' => 'ml-2'
                        ],
                    ],
                ],
                [
                    'text' => 'Contas a Pagar',
                    'url'  => '/contasAPagar',
                    'icon' => 'fas fa-file-invoice',
                    'can'  => 'gerenciar contas a pagar',
                ],
                [
                    'text' => 'Contas a Receber',
                    'url'  => '/contasAReceber',
                    'icon' => 'fas fa-file-invoice',
                    'can'  => 'gerenciar contas a receber',
                ],
                [
                    'text' => 'Plano de Contas',
                    'url'  => '/planoDeConta',
                    'icon' => 'fas fa-file-invoice',
                    'can'  => 'gerenciar plano de conta',
                ],
                [
                    'text'    => 'NFe',
                    'icon'    => 'far fa-file-alt',
                    'can'     => 'gerenciar NFe',
                    'submenu' => [
                        [
                            'text'        => 'Emitir NFe',
                            'url'         => '/nota_fiscal/create',
                            'icon'        => 'fas fa-upload',
                            'shift' => 'ml-2'
                        ],
                        [
                            'text'        => 'Notas Emitidas (NFe)',
                            'url'         => '/nota_fiscal',
                            'icon'        => 'fas fa-list-ul',
                            'shift' => 'ml-2'
                        ],
                        [
                            'text'        => 'Baixar XML (NFe)',
                            'url'         => '/notas',
                            'icon'        => 'fas fa-download',
                            'shift' => 'ml-2'
                        ],
                        [
                            'text'        => 'Relatórios (NFe)',
                            'url'         => '/relatorios',
                            'icon'        => 'fas fa-chart-area',
                            'shift' => 'ml-2'
                        ],
                    ],
                ],
            ],
        ],
        [
            'text' => 'Usuários',
            'url'  => '/usuarios',
            'icon' => 'fas fa-users',
            'can'  => 'gerenciar usuarios'
        ],
        [
            'text' => 'Preferências',
            'url'  => '/preferencias',
            'icon' => 'fas fa-cogs',
            'can'  => 'gerenciar preferencias'
        ],


    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
