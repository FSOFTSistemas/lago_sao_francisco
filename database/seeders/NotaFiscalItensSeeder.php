<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\NotaFiscal;
use App\Models\NotaFiscalItens;

class NotaFiscalItensSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ncms')->insert([
            ['ncm' => '00000000', 'descricao' => 'Produto genérico 1'],
            ['ncm' => '00000001', 'descricao' => 'Produto genérico 2'],
        ]);

        // Inserir cliente para garantir cliente_id = 1
        DB::table('clientes')->insert([
            'nome_razao_social' => 'Cliente Teste Ltda',
            'apelido_nome_fantasia' => 'Cliente Teste',
            'telefone' => '(11) 99999-9999',
            'whatsapp' => '(11) 98888-8888',
            'data_nascimento' => '1990-01-01',
            'endereco_id' => null,
            'cpf_cnpj' => '12345678000199',
            'rg_ie' => '123456789',
            'empresa_id' => 1,
            'tipo' => 'PJ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Criar notas fiscais
        $nota1 = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 1,
            'cfop_id' => 1,
            'usuario_id' => 1,
            'data' => now(),
            'empresa_id' => 1,
            'chave' => '12345678901234567890123456789012345678901234',
            'numero' => '1001',
            'serie' => '1',
            'observacoes' => 'Nota fiscal teste 1',
            'info_complementares' => '',
            'peso_liquido' => 10,
            'peso_bruto' => 11,
            'tp_frete' => 1,
            'tp_transporte' => 3,
            'tp_nota' => 2,
            'nfe_referenciavel' => '0',
            'total_produtos' => 100,
            'total_nota' => 110,
            'total_desconto' => 5,
            'outras_despesas' => 2,
            'base_ICMS' => 90,
            'vICMS' => 18,
            'base_ST' => 0,
            'v_ST' => 0,
        ]);

        $nota2 = NotaFiscal::create([
            'cliente_id' => 1,
            'ncm_id' => 2,
            'cfop_id' => 2,
            'usuario_id' => 1,
            'data' => now(),
            'empresa_id' => 1,
            'chave' => '98765432109876543210987654321098765432109876',
            'numero' => '1002',
            'serie' => '1',
            'observacoes' => 'Nota fiscal teste 2',
            'info_complementares' => '',
            'peso_liquido' => 20,
            'peso_bruto' => 21,
            'tp_frete' => 2,
            'tp_transporte' => 1,
            'tp_nota' => 2,
            'nfe_referenciavel' => '0',
            'total_produtos' => 200,
            'total_nota' => 220,
            'total_desconto' => 10,
            'outras_despesas' => 5,
            'base_ICMS' => 180,
            'vICMS' => 36,
            'base_ST' => 0,
            'v_ST' => 0,
        ]);

        // Criar itens para a nota 1
        NotaFiscalItens::create([
            'nota_fiscal_id' => $nota1->id,
            'produto_id' => 1,
            'quantidade' => 5,
            'v_unitario' => 20,
            'desconto' => 0,
            'subtotal' => 100,
            'cst' => '00',
            'cfop_id' => 1,
            'csosm' => '',
            'total' => 100,
            'base_ICMS' => 90,
            'vICMS' => 18,
            'base_st' => 0,
            'v_ST' => 0,
        ]);

        // Criar itens para a nota 2
        NotaFiscalItens::create([
            'nota_fiscal_id' => $nota2->id,
            'produto_id' => 2,
            'quantidade' => 10,
            'v_unitario' => 20,
            'desconto' => 0,
            'subtotal' => 200,
            'cst' => '00',
            'cfop_id' => 2,
            'csosm' => '',
            'total' => 200,
            'base_ICMS' => 180,
            'vICMS' => 36,
            'base_st' => 0,
            'v_ST' => 0,
        ]);
    }
}
