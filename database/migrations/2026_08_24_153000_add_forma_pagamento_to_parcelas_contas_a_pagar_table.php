<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('parcelas_contas_a_pagar', 'forma_pagamento')) {
            Schema::table('parcelas_contas_a_pagar', function (Blueprint $table) {
                $table->string('forma_pagamento')->nullable()->after('valor_pago');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('parcelas_contas_a_pagar', 'forma_pagamento')) {
            Schema::table('parcelas_contas_a_pagar', function (Blueprint $table) {
                $table->dropColumn('forma_pagamento');
            });
        }
    }
};
