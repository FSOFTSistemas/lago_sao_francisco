<?php

namespace App\Utils;

class ValidationEAN13Util {

    public static function validate_EAN13Barcode($ean)
    {
        $sumEvenIndexes = 0;
        $sumOddIndexes = 0;
        $eanAsArray = array_map('intval', str_split($ean));
        if (!self::has13Numbers($eanAsArray)) {
            return false;
        };
        for ($i = 0; $i < count($eanAsArray) - 1; $i++) {
            if ($i % 2 === 0) {
                $sumOddIndexes += $eanAsArray[$i];
            } else {
                $sumEvenIndexes += $eanAsArray[$i];
            }
        }
        $rest = ($sumOddIndexes + (3 * $sumEvenIndexes)) % 10;
        if ($rest !== 0) {
            $rest = 10 - $rest;
        }
        return $rest === $eanAsArray[12];
    }

    public static function has13Numbers(array $ean)
    {
        return count($ean) === 13;
    }

}
