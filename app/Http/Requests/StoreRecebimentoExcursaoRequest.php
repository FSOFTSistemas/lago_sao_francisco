<?php

namespace App\Http\Requests;

use App\Models\Excursao;
use App\Models\FormaPagamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRecebimentoExcursaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valor' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'forma_pagamento_id' => ['required', 'integer', 'exists:forma_pagamentos,id'],
            'comprovante' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'iniciar_apos_recebimento' => ['nullable', 'boolean'],
            '_receber_excursao_id' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'valor.required' => 'Informe o valor recebido.',
            'valor.numeric' => 'Informe um valor recebido válido.',
            'valor.min' => 'O valor recebido deve ser maior que zero.',
            'forma_pagamento_id.required' => 'Informe a forma de pagamento.',
            'forma_pagamento_id.exists' => 'A forma de pagamento selecionada é inválida.',
            'comprovante.mimes' => 'O comprovante deve ser PDF, JPG, JPEG ou PNG.',
            'comprovante.max' => 'O comprovante deve ter no máximo 2 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Excursao|null $excursao */
            $excursao = $this->route('excursao');
            if (! $excursao || $excursao->status === Excursao::STATUS_CANCELADO) {
                $validator->errors()->add('valor', 'Não é possível receber valores de uma excursão cancelada.');

                return;
            }

            $totalRecebido = (float) $excursao->recebimentos()
                ->whereNotNull('fluxo_caixa_id')
                ->whereNull('fluxo_cancelamento_id')
                ->sum('valor');
            $saldo = max((float) $excursao->total - $totalRecebido, 0);
            if ((float) $this->input('valor') > $saldo + 0.01) {
                $validator->errors()->add(
                    'valor',
                    'O valor recebido não pode ser maior que o saldo de R$ '.number_format($saldo, 2, ',', '.').'.',
                );
            }

            $forma = FormaPagamento::find($this->input('forma_pagamento_id'));
            if ($forma && str_contains(mb_strtolower($forma->descricao), 'crediário')) {
                $validator->errors()->add('forma_pagamento_id', 'Crediário não pode ser usado para receber a excursão.');
            }

            $pagamentoPix = $forma?->movimentoSlug() === 'pix';
            if ($pagamentoPix && ! $this->hasFile('comprovante')) {
                $validator->errors()->add('comprovante', "O comprovante é obrigatório para pagamentos via {$forma->descricao}.");
            }

            if (! $pagamentoPix && $this->hasFile('comprovante')) {
                $validator->errors()->add('comprovante', 'O comprovante só pode ser enviado para pagamentos via Pix.');
            }
        });
    }
}
