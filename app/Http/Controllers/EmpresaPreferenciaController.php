<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EmpresaPreferencia;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use NFePHP\Common\Certificate;

class EmpresaPreferenciaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->salvarPreferencias($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        return $this->salvarPreferencias($request, $id);
    }

    /**
     * Salva ou atualiza as preferências fiscais e valida o certificado digital (.pfx).
     */
    protected function salvarPreferencias(Request $request, $id = null): RedirectResponse
    {
        try {
            $data = $request->validate([
                'certificado_digital' => 'nullable|file',
                'senha_certificado'   => 'nullable|string|max:255',
                'ambiente_dfe'        => 'nullable|in:1,2',
                'numero_ultima_nota'  => 'nullable|integer',
                'serie'               => 'nullable|string|max:20',
                'cfop_padrao'         => 'nullable|string|max:20',
                'regime_tributario'   => 'nullable|string|max:50',
                'empresa_id'          => 'required|exists:empresas,id',
            ]);

            $empresaId = (int) $data['empresa_id'];
            $empresa = Empresa::findOrFail($empresaId);

            $preferencia = EmpresaPreferencia::firstOrNew(['empresa_id' => $empresaId]);

            // Se enviou um novo arquivo de Certificado Digital (.pfx)
            if ($request->hasFile('certificado_digital')) {
                $file = $request->file('certificado_digital');
                $senha = (string) $request->input('senha_certificado', '');

                $conteudo = file_get_contents($file->getRealPath());

                // Validação Imediata (Fail-Fast): confere senha e integridade do .pfx
                try {
                    $cert = Certificate::readPfx($conteudo, $senha);
                } catch (\Throwable $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Senha do certificado incorreta ou arquivo .pfx inválido/corrompido: ' . $e->getMessage());
                }

                // Confere se o certificado não está vencido
                if ($cert->isExpired()) {
                    $vencimento = $cert->getValidTo()->format('d/m/Y H:i');
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "O certificado digital selecionado expirou em {$vencimento}. Envie um certificado válido.");
                }

                // Salva o arquivo em pasta segura fora de public: storage/app/certificados/
                $diretorio = storage_path('app/certificados');
                if (!File::exists($diretorio)) {
                    File::makeDirectory($diretorio, 0755, true);
                }

                $nomeArquivo = 'empresa_' . $empresaId . '_' . time() . '.pfx';
                file_put_contents($diretorio . '/' . $nomeArquivo, $conteudo);

                $preferencia->certificado_digital = 'certificados/' . $nomeArquivo;
                $preferencia->senha_certificado = !empty($senha) ? Crypt::encryptString($senha) : null;
            } elseif ($request->filled('senha_certificado')) {
                // Se o arquivo já existia e o usuário apenas atualizou a senha
                $preferencia->senha_certificado = Crypt::encryptString((string) $request->input('senha_certificado'));
            }

            if ($request->filled('ambiente_dfe')) {
                $preferencia->ambiente_dfe = (int) $request->input('ambiente_dfe');
            }

            if ($request->has('numero_ultima_nota')) {
                $preferencia->numero_ultima_nota = $data['numero_ultima_nota'];
            }
            if ($request->has('serie')) {
                $preferencia->serie = $data['serie'];
            }
            if ($request->has('cfop_padrao')) {
                $preferencia->cfop_padrao = $data['cfop_padrao'];
            }
            if ($request->has('regime_tributario')) {
                $preferencia->regime_tributario = $data['regime_tributario'];
            }

            $preferencia->save();

            return redirect()->back()->with('success', 'Preferências e Certificado Digital salvos e validados com sucesso!');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocorreu um erro ao salvar as preferências: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmpresaPreferencia $empresaPreferencia): RedirectResponse
    {
        $empresaPreferencia->delete();
        return redirect()->back()->with('success', 'Preferências excluídas.');
    }
}
