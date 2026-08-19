<?php

namespace App\Http\Controllers;

use App\Models\RecebimentoExcursao;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecebimentoExcursaoController extends Controller
{
    public function comprovante(RecebimentoExcursao $recebimento): StreamedResponse
    {
        abort_unless(
            $recebimento->comprovante_path
                && Storage::disk('local')->exists($recebimento->comprovante_path),
            404,
            'Comprovante não encontrado.',
        );

        return Storage::disk('local')->download(
            $recebimento->comprovante_path,
            basename($recebimento->comprovante_path),
        );
    }
}
