<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DayUse as DayUseModel;
use App\Models\Cliente; 
use App\Models\Vendedor; 
use App\Models\ItensDayUse as Item; 
use App\Models\MovDayUse;

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
    public $selectedClientId; // ID do cliente selecionado
    public $selectedVendorId; // ID do vendedor selecionado

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

    // Métodos para reagir a mudanças nos seletores de cliente e vendedor (opcional, para reatividade)
    public function updatedSelectedClientId($value)
    {
        // Não precisa de lógica complexa aqui, a reatividade do Livewire já atualiza a propriedade
    }

    public function updatedSelectedVendorId($value)
    {
        // Não precisa de lógica complexa aqui
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
}
