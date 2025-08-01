<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cfop;

class CfopController extends Controller
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
        $validated = $request->validate([
            'natureza' => 'required|string|max:255',
            'cfop' => 'required|string|max:10',
        ]);

        Cfop::create($validated);

        return redirect()->back()->with('success', 'CFOP criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'natureza' => 'required|string|max:255',
            'cfop' => 'required|string|max:10',
        ]);

        $cfop = Cfop::findOrFail($id);
        $cfop->update($validated);

        return redirect()->back()->with('success', 'CFOP atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
