<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excursao_almoco', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excursao_id')
                ->unique()
                ->constrained('excursoes')
                ->cascadeOnDelete();
            $table->foreignId('cardapio_excursao_id')
                ->nullable()
                ->constrained('cardapios_excursao')
                ->nullOnDelete();
            $table->string('nome_cardapio');
            $table->text('descricao_cardapio')->nullable();
            $table->unsignedInteger('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        $agora = now();

        DB::table('excursoes')
            ->where('qtd_almoco', '>', 0)
            ->orderBy('id')
            ->chunkById(500, function ($excursoes) use ($agora) {
                $registros = $excursoes->map(fn ($excursao) => [
                    'excursao_id' => $excursao->id,
                    'cardapio_excursao_id' => null,
                    'nome_cardapio' => 'Almoço legado',
                    'descricao_cardapio' => 'Almoço registrado antes da implantação do cadastro de cardápios de excursão.',
                    'quantidade' => $excursao->qtd_almoco,
                    'valor_unitario' => $excursao->valor_almoco,
                    'total' => $excursao->total_almoco,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ])->all();

                DB::table('excursao_almoco')->insert($registros);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursao_almoco');
    }
};
