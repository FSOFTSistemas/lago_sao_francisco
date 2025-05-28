<?php

namespace App\Http\Controllers;

use App\Models\EmpresaRT;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class EmpresaRTController extends Controller
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
                'nome' => 'nullable|string|max:255',
                'cnpj' => 'nullable|string|max:20',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            EmpresaRT::create($data);

            return redirect()->back()->with('success', 'Responsável Técnico cadastrado com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao cadastrar Responsável Técnico. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmpresaRT $empresaRT)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpresaRT $empresaRT)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmpresaRT $empresaRT)
    {
        try {
            $data = $request->validate([
                'nome' => 'nullable|string|max:255',
                'cnpj' => 'nullable|string|max:20',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            $empresaRT->update($data);

            return redirect()->back()->with('success', 'Responsável Técnico atualizado com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar Responsável Técnico. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpresaRT $empresaRT)
    {
        //
    }
}
