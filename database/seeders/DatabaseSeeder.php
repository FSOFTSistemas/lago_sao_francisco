<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $contador = new ContadorSeeder();
        $contador->run();

        $resposavelTecnico = new ResponsavelTecnicoSeeder();
        $resposavelTecnico->run();

        $empresa = new EmpresaSeeder();
        $empresa->contador1 = $contador->contador1->id;
        $empresa->contador2 = $contador->contador2->id;
        $empresa->contador3 = $contador->contador3->id;
        $empresa->rt = $resposavelTecnico->RT;
        $empresa->run();

        $preferencia = new PreferenciaSeeder();
        $preferencia->empresa1Id = $empresa->empresaLago->id;
        $preferencia->empresa2Id = $empresa->empresaRestaurante->id;
        $preferencia->empresa3Id = $empresa->empresaHotel->id;
        $preferencia->run();

        $formaPagamento = new FormaPagamentoSeeder();
        $formaPagamento->run();

        $produtoEstoque = new ProdutoEstoqueSeeder();
        $produtoEstoque->run();

        $permissionUsuarios = new PermisssoesUsuariosSeeder();
        $permissionUsuarios->empresa_id = $empresa->empresaLago->id;
        $permissionUsuarios->run();
        
        $notaFiscalItens  = new NotaFiscalItensSeeder();
        $notaFiscalItens->usuarioId = $permissionUsuarios->usuario_id;
        $notaFiscalItens->run();

        $quartos = new QuartosSeeder();
        $quartos->run();

    }

}

    /**
     * 1 venda-dinheiro
     * 2 venda-cartão
     * 3 venda-pix
     * 4 venda-carteira
     * 5 venda-cheque
     * 6 recebimento-dinheiro
     * 7 recebimento-cartão
     * 8 recebimento-pix
     * 9 recebimento-carteira
     * 10 recebimento-cheque
     * 11 sangria
     * 12 suprimento
     */