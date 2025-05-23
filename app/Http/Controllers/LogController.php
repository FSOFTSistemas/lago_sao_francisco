<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $logs = Log::orderBy('data_hora', 'desc')->paginate(20);
            return view('logs.index', compact('logs'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao listar logs: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo_acao' => 'required|in:create,read,update,delete',
                'descricao' => 'required|string',
                'data_hora' => 'nullable|date',
            ]);
            $validated['usuario_id'] = Auth::user()->id;
            Log::create($validated);

            return redirect()->back()->with('success', 'Log criado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar log: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Log $log)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Log $log)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $log = Log::findOrFail($id);

            $validated = $request->validate([
                'tipo_acao' => 'required|in:create,read,update,delete',
                'descricao' => 'required|string',
                'usuario_id' => 'required|exists:users,id',
                'data_hora' => 'nullable|date',
            ]);
            $validated['usuario_id'] = Auth::user()->id;
            $log->update($validated);

            return redirect()->back()->with('success', 'Log atualizado com sucesso!');
        } catch (ModelNotFoundException $mnfe) {
            return redirect()->back()->with('error', 'Log não encontrado.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar log: ' . $e->getMessage());
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $log = Log::findOrFail($id);
            $log->delete();

            return redirect()->back()->with('success', 'Log removido com sucesso!');
        } catch (ModelNotFoundException $mnfe) {
            return redirect()->back()->with('error', 'Log não encontrado.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover log: ' . $e->getMessage());
        }
    }
    
}
