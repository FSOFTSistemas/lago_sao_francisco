<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Hospede;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['quarto', 'hospede'])->latest()->paginate(10);
        return view('reserva.index', compact('reservas'));
    }

    public function create(Request $request)
{
    $checkin = $request->data_checkin;
    $checkout = $request->data_checkout;

    $quartos = Quarto::query();

    if ($checkin && $checkout) {
        $ocupados = Reserva::where(function ($query) use ($checkin, $checkout) {
            $query->whereBetween('data_checkin', [$checkin, $checkout])
                  ->orWhereBetween('data_checkout', [$checkin, $checkout])
                  ->orWhere(function ($query) use ($checkin, $checkout) {
                      $query->where('data_checkin', '<=', $checkin)
                            ->where('data_checkout', '>=', $checkout);
                  });
        })->pluck('quarto_id');

        $quartos = $quartos->whereNotIn('id', $ocupados);
    }

    // Buscar quartos agrupados por categoria
    $quartosAgrupados = $quartos->with('categoria')->get()->groupBy('categoria.titulo');
    $categorias = Categoria::where('status', 1)->orderBy('posicao')->get();
    $formasPagamento = FormaPagamento::whereNotIn('descricao', [
            'sympla',
            'boleto-bancário',
            'crediário'
        ])->get();
    
    // Buscar produtos ativos para o select
    $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();

    $hospedes = Hospede::all();
    $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();

    return view('reserva.create', compact('quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos', 'checkin', 'checkout'));
}


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'required',
                'valor_total' => 'numeric',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
            ]);

            // Remover a máscara do valor_diaria antes de salvar
            $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);
    
            $reserva = Reserva::create($validatedData);
    
            return redirect()->route('reserva.edit', $reserva->id)->with('success', 'Reserva criada com sucesso!');
        } catch (\Exception $e) {
            // Redirecionar de volta com os inputs para que old() funcione
            return redirect()->back()->withInput()->with('error', 'Erro ao criar reserva!: ' . $e->getMessage());
        }
    }

    public function edit(Reserva $reserva)
    {
        // Buscar quartos agrupados por categoria para edição
        $quartosAgrupados = Quarto::with('categoria')->get()->groupBy('categoria.titulo');
        $categorias = Categoria::where('status', 1)->orderBy('posicao')->get();
        $formasPagamento = FormaPagamento::whereNotIn('descricao', [
            'sympla',
            'boleto-bancário',
            'crediário'
        ])->get();
        
        // Buscar produtos ativos para o select
        $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();
        
        $hospedes = Hospede::all();
        $hospedeBloqueado = Hospede::where('nome', 'Bloqueado')->first();
        
        return view('reserva.create', compact('reserva', 'quartosAgrupados', 'categorias', 'hospedes', 'hospedeBloqueado', 'formasPagamento', 'produtos'));
    }

    public function update(Request $request, Reserva $reserva)
    {
        try {
        $validatedData = $request->validate([
                'quarto_id' => 'required|exists:quartos,id',
                'hospede_id' => 'nullable|exists:hospedes,id',
                'data_checkin' => 'required|date',
                'data_checkout' => 'required|date|after_or_equal:data_checkin',
                'valor_diaria' => 'required',
                'valor_total' => 'numeric',
                'situacao' => 'required|in:pre-reserva,reserva,hospedado,bloqueado',
                'n_adultos' => 'required',
                'n_criancas' => 'required',
        ]);

        // Remover a máscara do valor_diaria antes de salvar
        $validatedData['valor_diaria'] = str_replace(['.', ','], ['', '.'], $validatedData['valor_diaria']);

        $reserva->update($validatedData);

        return redirect()->back()->with('success', 'Reserva atualizada com sucesso!');
    } catch (\Exception $e) {
        // Redirecionar de volta com os inputs para que old() funcione
        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar reserva!: ' . $e->getMessage());
        }
    }

    public function destroy(Reserva $reserva)
    {
        try {
        $reserva->delete();
        return redirect()->route('reserva.index')->with('success', 'Reserva removida com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover reserva!');
            }
    }

    public function quartosDisponiveis(Request $request)
{
    $checkin = Carbon::createFromFormat('d/m/Y', $request->checkin);
    $checkout = Carbon::createFromFormat('d/m/Y', $request->checkout);
    $reservaId = $request->reserva_id; // pode ser null

    $quartosIndisponiveis = Reserva::where(function ($query) use ($checkin, $checkout) {
            $query->whereBetween('data_checkin', [$checkin, $checkout->copy()->subDay()])
                    ->orWhereBetween('data_checkout', [$checkin->copy()->addDay(), $checkout])
                    ->orWhere(function ($query) use ($checkin, $checkout) {
                        $query->where('data_checkin', '<', $checkin)
                            ->where('data_checkout', '>', $checkout);
                    });
        })
        ->when($reservaId, function ($query, $reservaId) {
            $query->where('id', '!=', $reservaId);
        })
        ->pluck('quarto_id');

    $quartosDisponiveis = Quarto::with('categoria')
        ->whereNotIn('id', $quartosIndisponiveis)
        ->get()
        ->groupBy('categoria.titulo');

    // Formatar resposta para incluir informações da categoria
    $response = [];
    foreach ($quartosDisponiveis as $categoria => $quartos) {
        $response[] = [
            'categoria' => $categoria,
            'quartos' => $quartos->map(function($quarto) {
                return [
                    'id' => $quarto->id,
                    'nome' => $quarto->nome,
                    'categoria_id' => $quarto->categoria_id
                ];
            })
        ];
    }

    return response()->json($response);
}




}
