<?php

namespace App\Utils;

class CalculateCouponHeight
{
    public static function calculate($itemsLength, $methodsLength, $client)
    {
        $height = 260;
        if ($itemsLength > 0) {
            for ($i = 0; $i < $itemsLength; $i++) {
                $height += 13;
            }
        }
        if ($methodsLength > 0) {
            for ($i = 0; $i < $methodsLength; $i++) {
                $height += 13;
            }
        }
        if ($client) {
            $height += 15;
        }
        return $height;
    }

}
