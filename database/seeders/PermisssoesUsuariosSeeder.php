<?php

namespace Database\Seeders;
use App\Models\Empresa;
use App\Models\Hospede;
use App\Models\Movimento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisssoesUsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public $empresa_id;
    public $usuario_id;

    public function run()
    {
        $empresa_id = $this->empresa_id;
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
            'empresa_id' => $empresa_id,

        ]);
        $masterUser->assignRole('Master');
        $masterUser->givePermissionTo(Permission::all()->pluck('name')->toArray());
        $this->usuario_id = $masterUser->id;

        $funcionarioUser = User::firstOrCreate([
            'email' => 'funcionario@teste.com',
            'name' => 'Funcionario Teste',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresa_id,
        ]);
        $funcionarioUser->assignRole('funcionario');
        $funcionarioUser->givePermissionTo('gerenciar produto', 'gerenciar caixa');

        $financeiroUser = User::firstOrCreate([
            'email' => 'financeiro@teste.com',
            'name' => 'Financeiro Teste',
            'password' => bcrypt('12345678'),
            'empresa_id' => $empresa_id,
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
    }
}
