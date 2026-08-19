<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->renameColumn('valor', 'valor_pessoa');
        });

        Schema::table('excursoes', function (Blueprint $table) {
            $table->decimal('comissao', 10, 2)->default(0)->after('valor_pessoa');
            $table->decimal('valor_almoco', 10, 2)->default(0)->after('comissao');
            $table->unsignedInteger('qtd_almoco')->default(0)->after('valor_almoco');
            $table->decimal('total_almoco', 10, 2)->default(0)->after('qtd_almoco');
            $table->decimal('subtotal', 10, 2)->default(0)->after('total_almoco');
            $table->decimal('acrescimo', 10, 2)->default(0)->after('subtotal');
            $table->decimal('desconto', 10, 2)->default(0)->after('acrescimo');
            $table->decimal('total', 10, 2)->default(0)->after('desconto');
        });

        DB::table('excursoes')->update([
            'subtotal' => DB::raw('valor_pessoa'),
            'total' => DB::raw('valor_pessoa'),
        ]);
    }

    public function down(): void
    {
        Schema::table('excursoes', function (Blueprint $table) {
            $table->dropColumn([
                'comissao',
                'valor_almoco',
                'qtd_almoco',
                'total_almoco',
                'subtotal',
                'acrescimo',
                'desconto',
                'total',
            ]);
        });

        Schema::table('excursoes', function (Blueprint $table) {
            $table->renameColumn('valor_pessoa', 'valor');
        });
    }
};
