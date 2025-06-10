<?php

namespace App\Utils;

class FormatationUtil {

    public static function retiraAcentos($texto)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N c"), $texto);
    }

    public static function format($number, $dec = 2)
    {
        return number_format((float) $number, $dec, ".", "");
    }

    public static function retiraPontuacoes($texto)
    {
        $texto = str_replace(".", "", $texto);
        $texto = str_replace("/", "", $texto);
        $texto = str_replace("-", "", $texto);
        $texto = str_replace(" ", "", $texto);
        $texto = str_replace('(', '', $texto);
        $texto = str_replace(')', '', $texto);
        return $texto;
    }

}