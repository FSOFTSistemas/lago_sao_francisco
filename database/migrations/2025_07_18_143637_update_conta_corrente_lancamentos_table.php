<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $foreignKey = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'conta_corrente_lancamentos'
              AND COLUMN_NAME = 'banco_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        Schema::table('conta_corrente_lancamentos', function (Blueprint $table) use ($foreignKey) {
            // Removendo a foreign key e a coluna banco_id, se existirem
            if ($foreignKey) {
                $table->dropForeign($foreignKey->CONSTRAINT_NAME);
            }

            if (Schema::hasColumn('conta_corrente_lancamentos', 'banco_id')) {
                $table->dropColumn('banco_id');
            }

            // Adicionando a nova coluna conta_corrente_id, se ainda não existir
            if (!Schema::hasColumn('conta_corrente_lancamentos', 'conta_corrente_id')) {
                $table->unsignedBigInteger('conta_corrente_id');
            }

            if (!$this->foreignKeyExists('conta_corrente_lancamentos', 'conta_corrente_id')) {
                $table->foreign('conta_corrente_id')
                      ->references('id')
                      ->on('contas_correntes')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        $foreignKey = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'conta_corrente_lancamentos'
              AND COLUMN_NAME = 'conta_corrente_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        Schema::table('conta_corrente_lancamentos', function (Blueprint $table) use ($foreignKey) {
            // Revertendo a coluna conta_corrente_id, se existir
            if ($foreignKey) {
                $table->dropForeign($foreignKey->CONSTRAINT_NAME);
            }

            if (Schema::hasColumn('conta_corrente_lancamentos', 'conta_corrente_id')) {
                $table->dropColumn('conta_corrente_id');
            }

            // Recriando banco_id, se ainda não existir
            if (!Schema::hasColumn('conta_corrente_lancamentos', 'banco_id')) {
                $table->unsignedBigInteger('banco_id');
            }

            if (!$this->foreignKeyExists('conta_corrente_lancamentos', 'banco_id')) {
                $table->foreign('banco_id')
                      ->references('id')
                      ->on('bancos')
                      ->onDelete('cascade');
            }
        });
    }

    private function foreignKeyExists(string $tableName, string $columnName): bool
    {
        return DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$tableName, $columnName]) !== null;
    }
};
