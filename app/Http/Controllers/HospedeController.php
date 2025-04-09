<?php

namespace App\Http\Controllers;

use App\Models\Hospede;
use App\Http\Controllers\Controller;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class HospedeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hospede = Hospede::all();
        $endereco = Endereco::all();
        return view('hospede.index', compact('hospede', 'endereco'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $endereco = Endereco::all();
        return view('hospede.create', compact('endereco'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'nome' => 'required|string',
            'email' => 'nullable|string',
            'telefone' => 'nullable|string',
            'passaporte' => 'nullable|string',
            'nascimento' => 'nullable|date',
            'sexo' => 'nullable|in:masculino,feminino,outro',
            'profissao' => 'nullable|string',
            'observacoes' => 'nullable|string',
            'status' => 'nullable|boolean',
            'endereco_id' => 'nullable|exists:enderecos,id',
            'avatar_base64' => 'nullable|string',
        ]);

        if ($request->filled('avatar_base64')) {
            $imageData = $request->input('avatar_base64');
            $filename = uniqid() . '.jpg';
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $imageData = base64_decode($imageData);
            Storage::disk('public')->put("avatars/{$filename}", $imageData);
            $validated['avatar'] = "avatars/{$filename}";
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }
        Hospede::create($validated);

        return redirect()->route('hospede.index')->with('success', 'Hóspede criado com sucesso');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Erro ao criar hóspede: ' . $e->getMessage());
    }
}



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospede $hospede)
    {
        $hospede = Hospede::findOrFail($hospede->id);
        $endereco = Endereco::all();
        return view('hospede.create', compact('hospede', 'endereco'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    try {
        $hospede = Hospede::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string',
            'email' => 'nullable|string',
            'telefone' => 'nullable|string',
            'passaporte' => 'nullable|string',
            'nascimento' => 'nullable|date',
            'sexo' => 'nullable|in:masculino,feminino,outro',
            'profissao' => 'nullable|string',
            'observacoes' => 'nullable|string',
            'status' => 'nullable|boolean',
            'endereco_id' => 'nullable|exists:enderecos,id',
            'avatar_base64' => 'nullable|string',
        ]);

        if ($request->filled('avatar_base64')) {
            $imageData = $request->input('avatar_base64');
            $filename = uniqid() . '.jpg';

            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $imageData = base64_decode($imageData);

            Storage::disk('public')->put("avatars/{$filename}", $imageData);

            if ($hospede->avatar && Storage::exists("public/{$hospede->avatar}")) {
                Storage::delete("public/{$hospede->avatar}");
            }

            $validated['avatar'] = "avatars/{$filename}";
        }

        $hospede->update($validated);

        return redirect()->route('hospede.index')->with('success', 'Hóspede atualizado com sucesso');
    } catch (\Exception $e) {
        dd($e->getMessage());
        return redirect()->back()->with('error', 'Erro ao atualizar hóspede');
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hospede $hospede)
    {
        try{
            $hospede = Hospede::findOrFail($hospede->id);
            $hospede->delete();
            return redirect()->route('hospede.index')->with('success', 'Hospede deletado com sucesso');
        } catch(\Exception $e){
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar hospede');
        }
    }
}
