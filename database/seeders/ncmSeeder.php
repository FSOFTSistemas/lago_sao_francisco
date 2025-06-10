<?php

namespace Database\Seeders;

use App\Models\Ncm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ncmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = Storage::path('public/ncms/ncms.csv');
        $ncms = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);



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
}
