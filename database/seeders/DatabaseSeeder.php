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

        $cardapio = new CardapioSeeder();
        $cardapio->run();

        $secoes = new SecoesCardapioSeeder();
        $secoes->cardapio1_id = $cardapio->cardapios[0];
        $secoes->cardapio2_id = $cardapio->cardapios[1];
        $secoes->cardapio3_id = $cardapio->cardapios[2];
        $secoes->cardapio4_id = $cardapio->cardapios[3];
        $secoes->cardapio5_id = $cardapio->cardapios[4];
        $secoes->run();

        $refeicao= new RefeicaoPrincipalSeeder();
        $refeicao->cardapio1_id = $cardapio->cardapios[0];
        $refeicao->cardapio2_id = $cardapio->cardapios[1];
        $refeicao->cardapio3_id = $cardapio->cardapios[2];
        $refeicao->cardapio4_id = $cardapio->cardapios[3];
        $refeicao->cardapio5_id = $cardapio->cardapios[4];
        $refeicao->run();

        $categoriasCardapio = new CategoriaCardapioSeeder();
        $categoriasCardapio->sessao_cardapio1_id = $secoes->secoes[0];
        $categoriasCardapio->sessao_cardapio2_id = $secoes->secoes[1];
        $categoriasCardapio->sessao_cardapio3_id = $secoes->secoes[2];
        $categoriasCardapio->sessao_cardapio4_id = $secoes->secoes[3];
        $categoriasCardapio->sessao_cardapio5_id = $secoes->secoes[4];
        $categoriasCardapio->sessao_cardapio6_id = $secoes->secoes[5];
        $categoriasCardapio->sessao_cardapio7_id = $secoes->secoes[6];
        $categoriasCardapio->sessao_cardapio8_id = $secoes->secoes[7];
        $categoriasCardapio->sessao_cardapio9_id = $secoes->secoes[8];
        $categoriasCardapio->sessao_cardapio10_id = $secoes->secoes[9];
        $categoriasCardapio->sessao_cardapio11_id = $secoes->secoes[10];
        $categoriasCardapio->sessao_cardapio12_id = $secoes->secoes[11];
        $categoriasCardapio->sessao_cardapio13_id = $secoes->secoes[12];
        $categoriasCardapio->sessao_cardapio14_id = $secoes->secoes[13];
        $categoriasCardapio->refeicaop1_id = $refeicao->risotoCogumelos;
        $categoriasCardapio->refeicaop2_id = $refeicao->lasanhaBerinjela;

        $categoriasCardapio->run();

        $itensCardapio = new ItensCardapioSeeder();
        $itensCardapio->run();

        $ncm = new ncmSeeder();
        $ncm->run();

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