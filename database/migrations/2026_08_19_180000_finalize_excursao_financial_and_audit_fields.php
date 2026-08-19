<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE excursoes CHANGE comissao percentual_comissao DECIMAL(5, 2) NOT NULL DEFAULT 10');
        } else {
            Schema::table('excursoes', function (Blueprint $table) {
                $table->renameColumn('comissao', 'percentual_comissao');
            });

            Schema::table('excursoes', function (Blueprint $table) {
                $table->decimal('percentual_comissao', 5, 2)->default(10)->change();
            });
        }

        Schema::table('excursoes', function (Blueprint $table) {
            $table->timestamp('iniciada_em')->nullable()->after('status');
            $table->timestamp('finalizada_em')->nullable()->after('iniciada_em');
            $table->timestamp('cancelada_em')->nullable()->after('finalizada_em');
            $table->string('motivo_cancelamento', 500)->nullable()->after('cancelada_em');
        });

        DB::table('excursoes')
            ->where('percentual_comissao', 0)
            ->update(['percentual_comissao' => 10]);

        DB::table('excursoes')->update([
            'total_almoco' => DB::raw('valor_almoco * qtd_almoco'),
            'subtotal' => DB::raw('(valor_pessoa * qtd_pessoas) + (valor_almoco * qtd_almoco)'),
            'total' => DB::raw('((valor_pessoa * qtd_pessoas) + (valor_almoco * qtd_almoco)) + acrescimo - desconto'),
        ]);
    }

    public function down(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->dropColumn([
                'iniciada_em',
                'finalizada_em',
                'cancelada_em',
                'motivo_cancelamento',
            ]);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE excursoes CHANGE percentual_comissao comissao DECIMAL(10, 2) NOT NULL DEFAULT 0');
        } else {
            Schema::table('excursoes', function (Blueprint $table) {
                $table->decimal('percentual_comissao', 10, 2)->default(0)->change();
                $table->renameColumn('percentual_comissao', 'comissao');
            });
        }
    }
};
