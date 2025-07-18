<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\PreferenciasHotel;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function gerarVoucher($id)
    {
        try {
            $reserva = Reserva::with(['quarto', 'hospede', 'transacoes'])->findOrFail($id);
            
            // Verificar se a situação é válida para gerar voucher
            if (!in_array($reserva->situacao, ['reserva', 'pre-reserva'])) {
                return back()->with('error', 'Voucher só pode ser gerado para reservas com situação "reserva" ou "pre-reserva".');
            }

            $preferencias = PreferenciasHotel::first();
            $hora_checkin = Carbon::parse($preferencias->checkin)->format('H:i');
            $hora_checkout = Carbon::parse($preferencias->checkout)->format('H:i');

            // Formatar o número do voucher
            $numeroVoucher = 'HO_' . str_pad($reserva->id, 6, '0', STR_PAD_LEFT);
            
            // Formatar datas
            $dataCheckin = Carbon::parse($reserva->data_checkin)->format('d/m/Y');
            $dataCheckout = Carbon::parse($reserva->data_checkout)->format('d/m/Y');
            
            // Calcular número de diárias
            $numDiarias = Carbon::parse($reserva->data_checkin)->diffInDays(Carbon::parse($reserva->data_checkout));
            
            // Calcular valores
            $valorDiaria = $reserva->valor_diaria ?? 0;
            $valorTotal = $valorDiaria * $numDiarias;
            
            
            // Dados para o PDF
            $data = [
                'reserva' => $reserva,
                'numeroVoucher' => $numeroVoucher,
                'dataCheckin' => $dataCheckin,
                'dataCheckout' => $dataCheckout,
                'numDiarias' => $numDiarias,
                'valorDiaria' => $valorDiaria,
                'valorTotal' => $valorTotal,
                'dataEmissao' => Carbon::now()->format('d/m/Y'),
                'horaCheckin' => $hora_checkin,
                'horaCheckout' => $hora_checkout
            ];
            
            // Gerar o HTML do voucher
            $html = view('vouchers.template', $data)->render();
            
            // Configurar o DomPDF
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'Arial');
            $options->setIsRemoteEnabled(true);
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Definir o nome do arquivo
            $filename = 'voucher_' . $numeroVoucher . '.pdf';
            
            // Retornar o PDF para download
            return $dompdf->stream($filename, ['Attachment' => false]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar voucher: ' . $e->getMessage());
        }
    }
}
