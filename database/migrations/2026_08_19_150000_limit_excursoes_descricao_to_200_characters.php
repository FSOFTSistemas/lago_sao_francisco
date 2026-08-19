<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('UPDATE excursoes SET descricao = LEFT(descricao, 200) WHERE CHAR_LENGTH(descricao) > 200');
            DB::statement("ALTER TABLE excursoes MODIFY descricao VARCHAR(200) NOT NULL DEFAULT 'Não informado'");
        } elseif ($driver === 'pgsql') {
            DB::statement('UPDATE excursoes SET descricao = LEFT(descricao, 200) WHERE CHAR_LENGTH(descricao) > 200');
            DB::statement('ALTER TABLE excursoes ALTER COLUMN descricao TYPE VARCHAR(200)');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE excursoes MODIFY descricao VARCHAR(1000) NOT NULL DEFAULT 'Não informado'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE excursoes ALTER COLUMN descricao TYPE VARCHAR(1000)');
        }
    }
};
