<?php

use App\Models\Ncm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ncms', function (Blueprint $table) {
            $table->id();
            $table->string('ncm');
            $table->string('descricao');
            $table->timestamps();
        });
        $path = Storage::path('public/ncms/ncms.csv');
        $ncms = explode("\r\n", file_get_contents($path));
        foreach ($ncms as $n) {
            if ($n) {
                $ncm = (explode(";", $n));
                Ncm::create([
                    'ncm' => $ncm[0],
                    'descricao' => $ncm[1],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncms');
    }
};
