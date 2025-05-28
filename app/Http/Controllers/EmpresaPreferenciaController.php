<?php

namespace App\Http\Controllers;

use App\Models\EmpresaPreferencia;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class EmpresaPreferenciaController extends Controller
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
                'certificado_digital' => 'nullable|string|max:255',
                'numero_ultima_nota' => 'nullable|integer',
                'serie' => 'nullable|string|max:20',
                'cfop_padrao' => 'nullable|string|max:20',
                'regime_tributario' => 'nullable|string|max:50',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            EmpresaPreferencia::create($data);

            return redirect()->back()->with('success', 'Preferências cadastradas com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao salvar as preferências. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmpresaPreferencia $empresaPreferencia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmpresaPreferencia $empresaPreferencia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmpresaPreferencia $empresaPreferencia)
    {
        try {
            $data = $request->validate([
                'certificado_digital' => 'nullable|string|max:255',
                'numero_ultima_nota' => 'nullable|integer',
                'serie' => 'nullable|string|max:20',
                'cfop_padrao' => 'nullable|string|max:20',
                'regime_tributario' => 'nullable|string|max:50',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            $empresaPreferencia->update($data);

            return redirect()->back()->with('success', 'Preferências atualizadas com sucesso.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao atualizar as preferências. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpresaPreferencia $empresaPreferencia)
    {
        //
    }
}
