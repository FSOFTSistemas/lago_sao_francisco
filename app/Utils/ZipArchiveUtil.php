<?php

namespace App\Utils;

use Illuminate\Support\Facades\File;
use ZipArchive;

class ZipArchiveUtil {

    public static function zip($archives, $companyId)
    {
        $zip = new ZipArchive;
        $zipFileName = 'xmls' . $companyId . '.zip';

        if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) === TRUE) {
            foreach ($archives as $file) {
                $zip->addFromString($file->chave . '.xml', $file->xml);
            }
            $zip->close();
            return public_path($zipFileName);
        } else {
            return false;
        }
    }

    public static function deleteArchive($filePath)
    {
        if (File::exists($filePath)) {
            File::delete($filePath);
        } else {
            return false;
        }
    }

}
