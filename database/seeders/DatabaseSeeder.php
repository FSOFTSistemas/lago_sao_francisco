<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Hospede;
use App\Models\Movimento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $empresaLago = Empresa::create([
            'razao_social' => 'Maria Laura Pereira Paes',
            'nome_fantasia' => 'Lago São Francisco',
            'cnpj' => '40.065.099/0001-24',
            'endereco_id' => null,
            'inscricao_estadual' => '092969305',
        ]);
        
        Empresa::create([
            'razao_social' => 'Maria Laura Pereira Paes',
            'nome_fantasia' => 'Restaurante Dom Dina',
            'cnpj' => '40.065.099/0001-24',
            'endereco_id' => null,
            'inscricao_estadual' => '092969305',
        ]);
        
        Empresa::create([
            'razao_social' => 'Maria Laura Pereira Paes',
            'nome_fantasia' => 'Hotel Estação Chico',
            'cnpj' => '40.065.099/0001-24',
            'endereco_id' => null,
            'inscricao_estadual' => '092969305',
        ]);
        $permissions = [
            'gerenciar usuarios',
            'gerenciar financeiro',
            'gerenciar funcionario',
            'gerenciar empresa',
            'gerenciar adiantamento',
            'gerenciar banco',
            'gerenciar caixa',
            'gerenciar cliente',
            'gerenciar conta corrente',
            'gerenciar contas a pagar',
            'gerenciar contas a receber',
            'gerenciar fluxo de caixa',
            'gerenciar fornecedor',
            'gerenciar plano de conta',
            'gerenciar produto',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        Role::firstOrCreate(['name' => 'Master']);
        Role::firstOrCreate(['name' => 'financeiro']);
        Role::firstOrCreate(['name' => 'funcionario']);

        $masterUser = User::firstOrCreate([
            'email' => 'master@teste.com',
            'name' => 'Master Admin',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresaLago->id,

        ]);
        $masterUser->assignRole('Master');
        $masterUser->givePermissionTo(Permission::all()->pluck('name')->toArray());

        $funcionarioUser = User::firstOrCreate([
            'email' => 'funcionario@teste.com',
            'name' => 'Funcionario Teste',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresaLago->id,
        ]);
        $funcionarioUser->assignRole('funcionario');
        $funcionarioUser->givePermissionTo('gerenciar produto', 'gerenciar caixa');

        $financeiroUser = User::firstOrCreate([
            'email' => 'financeiro@teste.com',
            'name' => 'Financeiro Teste',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresaLago->id,
        ]);
        $financeiroUser->assignRole('financeiro');
        $financeiroUser->givePermissionTo(
            'gerenciar financeiro',
            'gerenciar funcionario',
            'gerenciar adiantamento',
            'gerenciar cliente',
            'gerenciar contas a pagar',
            'gerenciar contas a receber',
            'gerenciar fornecedor',
            'gerenciar plano de conta',
            'gerenciar produto');

        $movimentos = [
            'venda-dinheiro',                   
            'venda-cartão',
            'venda-pix',
            'venda-carteira',
            'venda-cheque',
            'recebimento-dinheiro',
            'recebimento-cartão',
            'recebimento-pix',
            'recebimento-carteira',
            'recebimento-cheque',
            'sangria',
            'suprimento',
        ];

        foreach ($movimentos as $descricao) {
            Movimento::firstOrCreate([
                'descricao' => $descricao,
            ]);
        }

        Hospede::firstOrCreate(
            ['nome' => 'Bloqueado']
        );

         $this->call([
            ProdutoEstoqueSeeder::class,
            NotaFiscalItensSeeder::class,
        ]);
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