# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Laravel 11 back-office system for a hotel/pousada ("Lago São Francisco"): reservations and room map,
day-use, event/space rentals (aluguel), buffet menus (cardápio), guests/clients, front-desk cash
register (caixa) and cash flow, accounts payable/receivable, partners (parceiros), inventory
(estoque), and Brazilian electronic invoicing (NFe). Domain vocabulary and most code comments are
in Portuguese (pt-br); keep new code consistent with that (`config/app.php` locale is `pt-br`).

## Commands

```bash
composer install
npm install

# Local dev: runs php artisan serve + queue:listen + vite concurrently
composer run dev

# Frontend only
npm run dev        # vite dev server
npm run build       # production build

# Code style (Laravel Pint)
./vendor/bin/pint
./vendor/bin/pint --test   # check only, no changes

# Tests
php artisan test
php artisan test --filter=TestName
./vendor/bin/phpunit tests/Feature/SomeTest.php

# DB
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed --class=DatabaseSeeder
```

**Testing caveat:** `phpunit.xml` has the sqlite in-memory `DB_CONNECTION`/`DB_DATABASE` overrides
commented out, so `php artisan test` runs against whatever database is configured in `.env`
(currently MySQL, database `lago_sao_francisco`) — not an isolated in-memory DB. Be careful running
tests/seeders/migrations locally, as they can affect real dev data. `tests/` currently only contains
the default Laravel example tests (no app-specific test suite yet).

## Architecture

### Multi-tenancy (empresa)

The app serves multiple "empresas" (hotel properties/companies) from one codebase, scoped via
`session('empresa_id')` — set when a user picks/switches the active company (see usages in
`HomeController`, `CaixaController`, etc.). Two scoping patterns coexist, so check the model before
assuming behavior:

- Some models apply `App\Scopes\EmpresaScope` as a global scope in their `booted()` method (e.g.
  `Aluguel`, `Adiantamento`) — this scope filters by the model's own `empresa_id` column, or, if the
  model has no such column but has a `funcionario()` relation, filters through the employee's company.
- Most other models/controllers filter by `empresa_id` manually and explicitly inside controller
  methods (grep `session('empresa_id')` before adding a new query on empresa-scoped data).

Users with the `Master` role bypass company/cash-register restrictions everywhere (see
`VerificaCaixaAberto` middleware and controller checks like `$user->hasRole('Master')`).

### Access control

Roles/permissions come from `spatie/laravel-permission`. Most `Route::resource(...)` entries in
`routes/web.php` (a single flat route file) are gated with `->middleware('permission:<permission
name>')`, using Portuguese permission strings like `gerenciar contas a pagar`. Permissions/roles are
seeded via `database/seeders/PermisssoesUsuariosSeeder.php`.

### Cash register gate

`App\Http\Middleware\VerificaCaixaAberto` (aliased `caixa.aberto`) blocks non-Master users from
routes like `reserva`, `aluguel/create`, and `/mapa` unless they have a `Caixa` with `status =
'aberto'` opened *today* for the active `empresa_id`. Front-desk-facing routes generally need this
middleware.

### Fat controllers + a few extracted services

Business logic mostly lives directly in `app/Http/Controllers` (several are large — e.g.
`ReservaController.php` is ~1300 lines, `ContasAPagarController.php` ~600). A handful of more complex
or cross-cutting domains are extracted into service classes under `app/services/` (note: the
**directory is lowercase** `services`, but the namespace is `App\Services` — this only works because
autoloading happens on a case-insensitive filesystem; keep this in mind if deploying to Linux, and
don't "fix" the casing without checking `composer.json` PSR-4 autoload + case sensitivity
implications): `NFeService`, `CaixaService`, `ContaCorrenteService`, `ContasService`,
`PlanoDeContasService`, `AdiantamentoService`.

### Frontend: Blade/AdminLTE by default, Livewire and React for specific islands

- Default UI is server-rendered Blade using `jeroennoten/laravel-adminlte` (AdminLTE 3) +
  Bootstrap 5 + jQuery/SweetAlert2, one folder per domain under `resources/views/`.
- `app/Livewire/*` components (paired with views under `resources/views/livewire`) handle richer
  interactive flows: cardápio/menu builder (`CardapioNew`, `CardapioSessoes`,
  `CardapioOpcoesRefeicao`, `CategoriaItensNew`, `IndexCategoria`), day-use flow (`DayUse`,
  `DayUsePagamento`, `ShowDayUse`), and NFe creation (`NFeNew`).
- React is used narrowly for one page — the reservation map — entry point
  `resources/js/mapa-reservas.jsx`, built by Vite alongside `resources/js/app.js`
  (see `vite.config.js`). Don't default to React for new features; it's the exception here, not
  the pattern.

### NFe (electronic invoicing)

`app/services/NFeService.php` wraps `nfephp-org/sped-nfe` for Brazilian fiscal note (NFe) issuance,
backed by the `NotaFiscal`/`NotaFiscalItens` models and the `NFeNew` Livewire component. PDFs
(vouchers, contracts, reports) are generated with `barryvdh/laravel-dompdf`.

### Scheduled jobs

`routes/console.php` schedules `App\Console\Commands\AtualizarStatusContasAtrasadas` daily at 01:00
to update overdue accounts-payable status.
