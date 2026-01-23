<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\PreferenciasHotel;
use App\Models\Funcionario;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function gerarVoucher($id)
    {
        try {
            $reserva = Reserva::with(['quarto', 'hospede', 'transacoes', 'pets'])->findOrFail($id);
            
            if (!in_array($reserva->situacao, ['reserva', 'pre-reserva'])) {
                return back()->with('error', 'Voucher só pode ser gerado para reservas com situação "reserva" ou "pre-reserva".');
            }

            $preferencias = PreferenciasHotel::first();
            $hora_checkin = Carbon::parse($preferencias->checkin)->format('H:i');
            $hora_checkout = Carbon::parse($preferencias->checkout)->format('H:i');

            // --- Número do Voucher ---
            $prefixo = '';
            if ($reserva->vendedor_id) {
                $vendedor = Funcionario::find($reserva->vendedor_id);
                if ($vendedor) {
                    $iniciais = substr($vendedor->nome, 0, 2);
                    $iniciais = iconv('UTF-8', 'ASCII//TRANSLIT', $iniciais);
                    $prefixo = strtoupper($iniciais) . '_';
                }
            }
            $numeroVoucher = $prefixo . str_pad($reserva->id, 6, '0', STR_PAD_LEFT);

            // Dados
            $dataCheckin = Carbon::parse($reserva->data_checkin)->format('d/m/Y');
            $dataCheckout = Carbon::parse($reserva->data_checkout)->format('d/m/Y');
            $numDiarias = Carbon::parse($reserva->data_checkin)->diffInDays(Carbon::parse($reserva->data_checkout));
            
            $valorDiaria = $reserva->valor_diaria ?? 0;
            $valorTotal = $reserva->valor_total; // Valor correto do banco
            
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
            
            $html = view('vouchers.template', $data)->render();
            
            $options = new \Dompdf\Options();
            $options->set('defaultFont', 'Arial');
            $options->setIsRemoteEnabled(true);
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $filename = 'voucher_' . $numeroVoucher . '.pdf';

            // --- CORREÇÃO DO ERRO DE SÍMBOLOS ESTRANHOS ---
            // Limpa qualquer saída (espaços, logs, erros) que tenha ocorrido antes
            // para garantir que apenas o binário do PDF seja enviado.
            if (ob_get_length() > 0) {
                ob_end_clean();
            }
            // ----------------------------------------------
            
            return $dompdf->stream($filename, ['Attachment' => false]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar voucher: ' . $e->getMessage());
        }
    }
}