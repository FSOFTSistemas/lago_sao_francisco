# Matriz de permissões do aplicativo

Esta matriz é exclusiva da API do aplicativo. As permissões web legadas continuam
existindo e não devem ser usadas para autorizar endpoints da API.

## Perfis

| Capacidade | Dona / Master | Financeiro |
| --- | --- | --- |
| Dashboard e projeção | Consulta | Consulta |
| Contas a pagar | Consulta | Consulta e operação |
| Contas a receber | Consulta | Consulta e operação |
| Fluxo de caixa | Consulta | Consulta e lançamento |
| Caixas | Consulta | Consulta, abertura e fechamento |
| Financeiro das reservas | Consulta | Consulta |
| Cadastros auxiliares | Consulta | Consulta |
| Comprovantes | Consulta | Consulta e envio |
| Auditoria | Consulta | Consulta |
| Estorno | Não permitido | Permitido com permissão específica |
| Exclusão física | Não permitido | Não permitido |

## Permissões de consulta

- `app.visualizar.dashboard`
- `app.visualizar.projecao`
- `app.visualizar.contas-pagar`
- `app.visualizar.contas-receber`
- `app.visualizar.fluxo-caixa`
- `app.visualizar.caixas`
- `app.visualizar.reservas-financeiro`
- `app.visualizar.dados-auxiliares`
- `app.visualizar.comprovantes`
- `app.visualizar.auditoria`

## Permissões operacionais do financeiro

- `app.criar.contas-pagar`
- `app.editar.contas-pagar`
- `app.pagar.contas-pagar`
- `app.criar.contas-receber`
- `app.editar.contas-receber`
- `app.receber.contas-receber`
- `app.criar.lancamento-financeiro`
- `app.abrir.caixa`
- `app.fechar.caixa`
- `app.enviar.comprovante`
- `app.estornar.lancamento-financeiro`

## Regras de aplicação

- Endpoints devem exigir a permissão exata; não basta verificar o nome do perfil.
- O papel Master não implica escrita na API.
- Um usuário inativo não pode autenticar, mesmo que possua permissões.
- A permissão nunca substitui a validação da empresa ativa.
- O perfil da dona não deve ser combinado com o papel financeiro.
- Cancelamento financeiro será representado por estorno auditável, nunca por
  exclusão física.
- O seeder é idempotente e pode ser executado isoladamente com
  `php artisan db:seed --class=ApiPermissionsSeeder`.

As constantes usadas pelos futuros middlewares e controllers ficam em
`App\Support\ApiPermissions`, evitando nomes de permissão espalhados pelo código.
