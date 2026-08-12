# Repository Guidelines

## Project Structure & Module Organization
This is a Laravel 11 application with a Vite-powered frontend. Core PHP code lives in `app/`, especially `app/Http/Controllers`, `app/Models`, `app/Livewire`, `app/Console/Commands`, and supporting utilities under `app/Utils` and `app/services`. Routes are defined in `routes/web.php` and `routes/console.php`. Blade views are organized by domain in `resources/views`, while frontend entry points and React code are in `resources/js`; Sass/CSS assets are in `resources/sass` and `resources/css`. Database schema and sample data are managed through `database/migrations`, `database/seeders`, and `database/factories`. Tests are split between `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands
- `composer install` installs PHP dependencies.
- `npm install` installs Vite, React, Bootstrap, Tailwind, and frontend tooling.
- `cp .env.example .env && php artisan key:generate` prepares a local environment when needed.
- `php artisan migrate --seed` applies migrations and seeders.
- `composer dev` runs the Laravel server, queue listener, and Vite dev server together.
- `php artisan serve` starts only the Laravel app.
- `npm run dev` starts only Vite for asset development.
- `npm run build` creates production frontend assets in `public/build`.
- `php artisan test` runs the PHPUnit test suite.
- `vendor/bin/pint` formats PHP code with Laravel Pint.

## Coding Style & Naming Conventions
Follow Laravel conventions and PSR-12 formatting with 4-space indentation for PHP. Use singular, PascalCase model names such as `Reserva` and controller names ending in `Controller`, such as `ReservaController`. Keep Blade templates grouped by feature under `resources/views/<domain>`. JavaScript modules should stay in `resources/js`, using clear camelCase names for functions and variables. Prefer existing project patterns before introducing new helpers or abstractions.

## Testing Guidelines
Use PHPUnit through `php artisan test`. Put HTTP, routing, database, and user-flow coverage in `tests/Feature`; put isolated service or utility behavior in `tests/Unit`. Name test methods descriptively, following the existing `test_the_application_returns_a_successful_response` style. Add or update tests when changing controller logic, financial calculations, reservations, fiscal/NF-e behavior, permissions, or migrations.

## Commit & Pull Request Guidelines
Recent commits use short Portuguese messages with prefixes such as `feat:` and `fix:`; keep that style, for example `fix: ajuste filtro de reservas`. Pull requests should include a concise description, affected modules, migration or seeder notes, test results, and screenshots for visible UI changes. Link related issues or tasks when available.

## Security & Configuration Tips
Never commit `.env`, credentials, certificates, generated fiscal files, or private customer data. Keep writable runtime files in Laravel-managed storage paths and verify permission-sensitive changes against `config/permission.php`.
