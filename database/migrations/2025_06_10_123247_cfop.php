<?php

use App\Models\Cfop;
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
            $table->string('natureza');
            $table->string('cfop');
            $table->timestamps();
        });

        $path = Storage::path('public/cfops/cfops.txt');
        $cfops = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
