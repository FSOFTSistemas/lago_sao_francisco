<?php

namespace App\Http\Controllers;

use App\Models\EmpresaContador;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class EmpresaContadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            
            $data = $request->validate([
                'cnpj' => 'nullable|string|max:20',
                'nome' => 'nullable|string|max:255',
                'crc' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'telefone' => 'nullable|string|max:20',
            ]);

            EmpresaContador::create($data);

            return redirect()->back()->with('success', 'Contador cadastrado com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao cadastrar o contador. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmpresaContador $empresaContador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpresaContador $empresaContador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmpresaContador $empresaContador)
    {
        dd($request);
        try {
            $data = $request->validate([
                'cnpj' => 'nullable|string|max:20',
                'nome' => 'nullable|string|max:255',
                'crc' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'telefone' => 'nullable|string|max:20',
            ]);

            $empresaContador->update($data);

            return redirect()->back()->with('success', 'Contador atualizado com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao atualizar o contador. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpresaContador $empresaContador)
    {
        //
    }
}
