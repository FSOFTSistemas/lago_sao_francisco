<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cfops', function (Blueprint $table) {
            $table->id();
            $table->string('cfop');
            $table->string('natureza');
            $table->timestamps();
        });
        $path = Storage::path('public/cfops/cfops.txt');
        $cfops = explode("\r\n", file_get_contents($path));
        foreach ($cfops as $line) {
            if ($line) {
                $cfop = (explode("  ", $line));
                DB::table('cfops')->insert([
                    ['cfop' => $cfop[0], 'natureza' => $cfop[1], 'created_at' => date('y-m-d h:m:s'), 'updated_at' => date('y-m-d h:m:s')],
                ]);
            }
        }
            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfops');
    }
};
