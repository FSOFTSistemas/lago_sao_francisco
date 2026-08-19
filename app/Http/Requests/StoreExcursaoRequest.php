<?php

namespace App\Http\Requests;

use App\Models\FormaPagamento;
use App\Services\ExcursaoFinanceiroService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class StoreExcursaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'percentual_comissao' => $this->input('percentual_comissao', 10),
            'valor_almoco' => $this->input('valor_almoco', 0),
            'qtd_almoco' => $this->input('qtd_almoco', 0),
            'acrescimo' => $this->input('acrescimo', 0),
            'desconto' => $this->input('desconto', 0),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'data' => ['required', 'date', 'after_or_equal:today'],
            'qtd_pessoas' => ['required', 'integer', 'min:1'],
            'valor_pessoa' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'percentual_comissao' => ['required', 'numeric', 'between:0,100'],
            'valor_almoco' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'qtd_almoco' => ['required', 'integer', 'min:0'],
            'acrescimo' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'desconto' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'responsavel' => ['required', 'string', 'max:255'],
            'telefone_responsavel' => ['required', 'string', 'max:20'],
            'descricao' => ['required', 'string', 'max:200'],
            'recebimentos' => ['required', 'array', 'min:1'],
            'recebimentos.*.valor' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'recebimentos.*.forma_pagamento_id' => ['required', 'integer', 'exists:forma_pagamentos,id'],
            'recebimentos.*.comprovante' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'data.required' => 'Informe a data da excursão.',
            'data.date' => 'Informe uma data válida.',
            'data.after_or_equal' => 'A data da excursão não pode estar no passado.',
            'qtd_pessoas.required' => 'Informe a quantidade de pessoas.',
            'qtd_pessoas.integer' => 'A quantidade de pessoas deve ser um número inteiro.',
            'qtd_pessoas.min' => 'A excursão deve ter pelo menos uma pessoa.',
            'valor_pessoa.required' => 'Informe o valor por pessoa.',
            'valor_pessoa.numeric' => 'Informe um valor por pessoa válido.',
            'valor_pessoa.min' => 'O valor por pessoa não pode ser negativo.',
            'percentual_comissao.between' => 'O percentual de comissão deve estar entre 0 e 100.',
            'qtd_almoco.integer' => 'A quantidade de almoços deve ser um número inteiro.',
            'qtd_almoco.min' => 'A quantidade de almoços não pode ser negativa.',
            'responsavel.required' => 'Informe o responsável pela excursão.',
            'telefone_responsavel.required' => 'Informe o telefone do responsável.',
            'descricao.required' => 'Informe a descrição da excursão.',
            'descricao.max' => 'A descrição deve ter no máximo 200 caracteres.',
            'recebimentos.required' => 'Informe pelo menos um recebimento inicial.',
            'recebimentos.min' => 'Informe pelo menos um recebimento inicial.',
            'recebimentos.*.valor.required' => 'Informe o valor do recebimento.',
            'recebimentos.*.valor.min' => 'O recebimento deve ser maior que zero.',
            'recebimentos.*.forma_pagamento_id.required' => 'Informe a forma de pagamento.',
            'recebimentos.*.forma_pagamento_id.exists' => 'A forma de pagamento selecionada é inválida.',
            'recebimentos.*.comprovante.file' => 'O comprovante deve ser um arquivo válido.',
            'recebimentos.*.comprovante.mimes' => 'O comprovante deve ser PDF, JPG, JPEG ou PNG.',
            'recebimentos.*.comprovante.max' => 'O comprovante deve ter no máximo 2 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validarComprovantesObrigatorios($validator);

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                $recebimentos = collect($this->input('recebimentos', []))->pluck('valor');
                $calculos = app(ExcursaoFinanceiroService::class)
                    ->calcular($this->all(), $recebimentos);
                $valorPago = (float) $calculos['valor_pago'];
                $total = (float) $calculos['total'];

                if ($valorPago + 0.01 < $total * 0.5) {
                    $validator->errors()->add(
                        'recebimentos',
                        'O pagamento inicial deve ser de pelo menos 50% do total da excursão.',
                    );
                }

                if ($valorPago > $total + 0.01) {
                    $validator->errors()->add(
                        'recebimentos',
                        'A soma dos recebimentos não pode ser maior que o total da excursão.',
                    );
                }
            } catch (InvalidArgumentException $exception) {
                $validator->errors()->add('total', $exception->getMessage());
            }
        });
    }

    private function validarComprovantesObrigatorios(Validator $validator): void
    {
        $recebimentos = $this->input('recebimentos', []);
        $formasIds = collect($recebimentos)
            ->pluck('forma_pagamento_id')
            ->filter()
            ->unique();
        $formas = FormaPagamento::query()
            ->whereIn('id', $formasIds)
            ->get()
            ->keyBy('id');

        foreach ($recebimentos as $indice => $recebimento) {
            $forma = $formas->get($recebimento['forma_pagamento_id'] ?? null);

            if ($forma?->exige_comprovante
                && ! $this->hasFile("recebimentos.{$indice}.comprovante")) {
                $validator->errors()->add(
                    "recebimentos.{$indice}.comprovante",
                    "O comprovante é obrigatório para pagamentos via {$forma->descricao}.",
                );
            }
        }
    }
}
