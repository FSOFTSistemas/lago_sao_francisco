<?php

namespace App\Services;

use DateTime;

class ContasService{

    public static function proximoMes($dataOriginal)
    {
        $data = new DateTime($dataOriginal);
        $data->modify('+1 month');

        $diaDaSemana = $data->format('w'); // 'w' retorna um número de 0 (domingo) a 6 (sábado)
        if ($diaDaSemana == 6) {
            $data->modify('+2 days');
        }
        elseif ($diaDaSemana == 0) {
            $data->modify('+1 day');
        }

        return $data->format('Y-m-d');
    }
}
