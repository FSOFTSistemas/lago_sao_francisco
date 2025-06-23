<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DayUse as DayUseModel;
use App\Models\Cliente; 
use App\Models\Vendedor; 
use App\Models\ItensDayUse as Item; 
use App\Models\MovDayUse;
use Illuminate\Support\Facades\Auth;

class DayUse extends Component
{
    public $abaAtual = 'geral';
    public $dayUse; 
   
    public $data;
    public $valor_total; 

    public $clientes;
    public $vendedores;
    public $items; // Coleção de todos os itens disponíveis
    public $quantidade = []; // Array para armazenar as quantidades de cada item, ex: ['item_id' => quantidade]
    public $total = 0; // O total calculado dos itens
    public $selectedClientId = null; // ID do cliente selecionado
    public $selectedVendorId; // ID do vendedor selecionado
    public $modalAberto = false;
    public $nome_razao_social;
    public $telefone;
    public $data_nascimento;
    public $tipo;

    // Listeners para eventos Livewire
    protected $listeners = [
        'avancou' => 'avancou',
    ];

    public function mount($dayUseId = null)
    {
        $this->clientes = Cliente::all();
        $this->vendedores = Vendedor::all();
        $this->items = Item::all();

        // Inicializa as quantidades de todos os itens para 0
        foreach ($this->items as $item) {
            $this->quantidade[$item->id] = 0;
        }

        if ($dayUseId) {
            $this->dayUse = DayUseModel::findOrFail($dayUseId);
            // Preenche as propriedades básicas do DayUse
            $this->data = $this->dayUse->data;
            $this->valor_total = $this->dayUse->valor_total; // Carrega o valor total salvo

            // Popula os seletores de cliente e vendedor
            $this->selectedClientId = $this->dayUse->cliente_id;
            $this->selectedVendorId = $this->dayUse->vendedor_id;

            // Popula as quantidades dos itens a partir da tabela MovDayUse
            foreach ($this->dayUse->itens as $item) {
                $this->quantidade[$item->item_id] = $item->quantity;
            }

        } else {
            $this->dayUse = new DayUseModel();
            $this->data = now()->format('Y-m-d');
        }

        $this->calculateTotal(); // Calcula o total inicial com base nas quantidades carregadas/iniciais
    }

    public function incrementQuantity($itemId)
    {
        $this->quantidade[$itemId]++;
        $this->calculateTotal();
    }

    public function decrementQuantity($itemId)
    {
        if ($this->quantidade[$itemId] > 0) {
            $this->quantidade[$itemId]--;
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total = 0;
        foreach ($this->items as $item) {
            $this->total += ($this->quantidade[$item->id] ?? 0) * $item->valor;
        }
    }

    // Método chamado quando o SweetAlert confirma e despacha 'avancou'
    public function avancou()
    {
        // Chama o método save para persistir os dados antes de avançar
        $this->abaAtual = 'pagamento'; // Muda para a aba de pagamento
    }

    public function save()
    {
        // Regras de validação
        $this->validate([
            'data' => 'required|date',
            'selectedClientId' => 'required|exists:clientes,id',
            'selectedVendorId' => 'required|exists:vendedors,id',
            'quantidade' => 'required|array',
            'total' => 'required|numeric|min:0',
        ], [
            'selectedClientId.required' => 'Por favor, selecione um cliente.',
            'selectedVendorId.required' => 'Por favor, selecione um vendedor.',
            'quantidade.required' => 'Por favor, adicione pelo menos um item.',
            'total.min' => 'O valor total não pode ser negativo.',
        ]);

        // Preenche o modelo DayUse com os dados do formulário
        $this->dayUse->fill([
            'data' => $this->data,
            'total' => $this->total, // Usa o total calculado
            'cliente_id' => $this->selectedClientId,
            'vendedor_id' => $this->selectedVendorId,
            // 'observacoes' não está mais no formulário, se ainda existir no DB, será null ou manterá o valor anterior
        ]);

        $this->dayUse->save();

        // Lógica para salvar os itens na tabela MovDayUse
        // Primeiro, remove todas as entradas existentes para este DayUse
        $this->dayUse->itens()->delete();

        // Em seguida, cria novas entradas para os itens com quantidade > 0
        foreach ($this->quantidade as $itemId => $quantity) {
            if ($quantity > 0) {
                // Opcional: buscar o item para pegar o valor no momento da venda, se MovDayUse tiver campo 'price'
                // $item = Item::find($itemId);
                MovDayUse::create([
                    'dayuse_id' => $this->dayUse->id,
                    'item_dayuse_id' => $itemId,
                    'quantidade' => $quantity,
                ]);
            }
        }

        // Dispara o evento 'confirmed' para o SweetAlert na view
        $this->dispatch('confirmed');
    }

    public function render()
    {
        return view('livewire.day-use');
    }

    public function openModal()
    {
        $this->modalAberto = true;
    }

    public function fecharModal()
    {
        $this->modalAberto = false;
    }

    public function saveCliente()
    {
        
        $this->tipo = 'PF';
        $this->validate([
            'nome_razao_social' => 'string|required',
            'tipo' => 'required|string',
            'data_nascimento' => 'date|required',
            'telefone' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    // Remove todos os caracteres não numéricos
                    $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                    
                    // Validação para telefone brasileiro
                    if (strlen($cleanNumber) < 10 || strlen($cleanNumber) > 11) {
                        $fail('O número de telefone deve conter 10 ou 11 dígitos (DDD + número).');
                    }
                    
                    // Valida DDD válido (11 a 99)
                    $ddd = substr($cleanNumber, 0, 2);
                    if ($ddd < 11 || $ddd > 99) {
                        $fail('O DDD do telefone é inválido.');
                    }
                },
            ],
        ], [
            'nome_razao_social.required' => 'O campo nome é obrigatório.',
            'nome_razao_social.string' => 'O campo nome deve ser uma string.',
            'tipo.required' => 'O campo tipo é obrigatório.',
            'tipo.string' => 'O campo tipo deve ser uma string.',
            'data_nascimento.required' => 'O campo data de nascimento é obrigatório.',
            'data_nascimento.date' => 'O campo data de nascimento deve ser uma data válida.', 
            'telefone.required' => 'O campo telefone é obrigatório.',
            'telefone.max' => 'O telefone não pode ter mais que 20 caracteres.',
        ]);

        Cliente::create([
            'nome_razao_social' => $this->nome_razao_social,
            'tipo' => $this->tipo,
            'data_nascimento' => $this->data_nascimento,
            'telefone' => $this->telefone,
            'empresa_id' =>  Auth::user()->empresa_id
        ]);
        $this->nome_razao_social = '';
        $this->telefone = '';
        $this->data_nascimento = '';
        $this->fecharModal();
    }
}
