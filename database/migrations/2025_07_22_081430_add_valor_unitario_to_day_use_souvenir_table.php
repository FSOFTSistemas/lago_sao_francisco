<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorUnitarioToDayUseSouvenirTable extends Migration
{
    public function up()
    {
        Schema::table('day_use_souvenir', function (Blueprint $table) {
            $table->decimal('valor_unitario', 10, 2)->nullable()->after('dayuse_id');
        });
    }

    public function down()
    {
        Schema::table('day_use_souvenir', function (Blueprint $table) {
            $table->dropColumn('valor_unitario');
        });
    }
}
