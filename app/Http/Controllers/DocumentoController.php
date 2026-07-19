<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class DocumentoController extends Controller
{
    /**
     * FNRH em branco, sem vínculo com uma reserva — para preenchimento manual.
     */
    public function fnrhBranco()
    {
        $pdf = Pdf::loadView('reserva.fnrh_blank');

        return $pdf->stream('FNRH_em_branco.pdf');
    }
}
