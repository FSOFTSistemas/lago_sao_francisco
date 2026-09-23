<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dfe_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nsu', 15)->index();
            $table->string('chave', 44)->index();
            $table->string('numero_nota', 20)->nullable()->index();
            $table->string('serie', 10)->nullable();
            $table->string('schema', 30); // resNFe, procNFe, resEvento, procEventoNFe
            $table->string('tipo_documento', 10)->default('NFE'); // NFE, CTE, EVENTO
            $table->string('cnpj_emitente', 20)->nullable()->index();
            $table->string('nome_emitente')->nullable();
            $table->string('ie_emitente', 20)->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->dateTime('data_emissao')->nullable();
            $table->unsignedTinyInteger('tipo_nfe')->nullable(); // 0: Entrada, 1: Saída
            $table->unsignedTinyInteger('situacao_nfe')->nullable(); // 1: Autorizada, 2: Cancelada, 3: Denegada
            $table->string('situacao_manifestacao', 30)->default('sem_manifestacao'); // sem_manifestacao, ciencia, confirmada, desconhecida, nao_realizada
            $table->dateTime('data_manifestacao')->nullable();
            $table->string('protocolo_manifestacao', 30)->nullable();
            $table->text('mensagem_manifestacao')->nullable();
            $table->longText('xml')->nullable();
            $table->boolean('importado_entrada')->default(false);
            $table->unsignedBigInteger('entrada_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['empresa_id', 'chave', 'schema']);
            $table->index(['empresa_id', 'situacao_manifestacao']);
            $table->index(['empresa_id', 'importado_entrada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dfe_documentos');
    }
};
