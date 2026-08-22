# Linha de base financeira para a API

Data do levantamento: 22/08/2026.

Este documento registra o comportamento encontrado antes da extração das regras
financeiras para serviços compartilhados. Ele não define o desenho final da API e
não autoriza mudanças no banco de produção.

## Estado da aplicação

- Laravel 11, PHP 8.2 ou superior e autenticação web baseada em sessão.
- Não existe `routes/api.php` nem autenticação por token.
- Há 163 declarações de rotas em `routes/web.php`: 73 GET, 27 POST, 7 PUT,
  4 DELETE, 3 PATCH e 49 resources.
- Os perfis existentes são `Master`, `financeiro` e `funcionario`, com permissões
  gerenciadas por `spatie/laravel-permission`.
- O usuário possui um único `empresa_id`; usuários Master também usam
  `session('empresa_id')` em parte das telas.
- O banco configurado respondeu ao comando de status e todas as migrations até
  `2026_08_21_120000_add_email_fields_to_excursoes_table` estão aplicadas.

## Superfícies financeiras atuais

| Área | Controller principal | Operações encontradas |
| --- | --- | --- |
| Dashboard | `HomeController` | Resumos e gráfico de fluxo de caixa |
| Projeção | `FinanceiroProjecaoController` | Recebido, projetado, atrasado e próximos recebimentos |
| Contas a pagar | `ContasAPagarController` | Listar, criar, editar, excluir, pagar e calendário |
| Contas a receber | `ContasAReceberController` | Listar, criar, editar, excluir e receber |
| Fluxo de caixa | `FluxoCaixaController` | CRUD, filtros e PDF |
| Caixa | `CaixaController` | CRUD, abertura, fechamento e resumo |
| Reservas | `TransacaoController` | Pagamentos, descontos, resumo e cancelamento |
| Receitas avulsas | `ReceitaAvulsaController` | Consulta e lançamento |
| Conta corrente | `ContaCorrenteLancamentoController` | CRUD de lançamentos |
| Excursões | `ExcursaoCaixaService` | Recebimentos, cancelamentos e vínculo com fluxo |

## Regras financeiras caracterizadas

### Projeção

O período padrão é o mês atual. A projeção combina:

- transações ativas de reserva do tipo `pagamento`;
- saldo ainda não agendado das reservas, atribuído ao checkout;
- contas a receber recebidas, pendentes ou atrasadas.

Uma transação de reserva com data de pagamento até hoje é recebida; uma data
futura é projetada. Reservas canceladas, bloqueadas e no-show não geram saldo em
aberto. O saldo não agendado é calculado por:

```text
máximo(0, valor_total - pagamentos até hoje - descontos - pagamentos futuros)
```

O agrupamento temporal muda conforme o tamanho do período, e a projeção também
produz totais por forma de pagamento e os próximos 15 recebimentos.

### Conta a pagar

- Aceita conta única ou parcelada.
- O pagamento pode sair de caixa ou conta corrente.
- Pagamento via caixa exige um caixa aberto no dia para o usuário e a empresa.
- O movimento de caixa usa atualmente o ID fixo `31` para "Pagamento de Contas".
- Uma parcela é considerada paga com status `pago` ou `finalizado`.
- A conta é marcada como paga quando não restam parcelas fora desses estados.
- O cálculo de próximo mês usa `DateTime::modify('+1 month')`: datas no fim do
  mês podem transbordar e pular um mês (por exemplo, 31/05/2026 resulta em
  01/07/2026). Vencimentos que caem no fim de semana avançam para segunda-feira.

### Conta a receber

- Aceita recebimentos parciais.
- Impede que o valor acumulado ultrapasse o valor da conta.
- Registra cada recebimento em `conta_pagamentos`.
- Cria uma entrada em `fluxo_caixas` usando o movimento derivado da forma de
  pagamento.
- Exige algum caixa aberto da empresa no dia; atualmente não restringe o caixa ao
  usuário que está recebendo.

### Pagamento de reserva

- `transacoes` armazena pagamentos e descontos vinculados à reserva.
- Uma heurística de 10 segundos evita a repetição de uma transação com os mesmos
  campos.
- Comprovantes aceitam PDF, JPG e PNG de até 2 MB.
- Crediário não cria movimento imediato de caixa.
- As demais formas procuram movimentos pelo padrão `venda-{forma}`.
- O plano de contas é Hospedagem ou Motorhome, conforme a categoria.
- Se não houver caixa ou mapeamento de movimento, a transação ainda pode ser
  confirmada sem uma entrada correspondente em `fluxo_caixas`, pois a falha é
  apenas registrada em log.
- Exclusão no mesmo dia cria movimento de cancelamento. Exclusão de dia anterior
  cria uma conta a pagar de estorno e remove a transação original.

## Empresa ativa

Foram encontrados três padrões concorrentes:

1. `Auth::user()->empresa_id`;
2. `session('empresa_id')` para Master;
3. `empresa_id` recebido na requisição.

Há operações que localizam registros apenas pelo ID e só depois utilizam a
empresa do usuário. A camada da API deverá resolver a empresa uma única vez e
consultar os registros já dentro desse escopo.

Pontos específicos encontrados:

- projeções de transações e reservas não aplicam filtro explícito por empresa;
- criação de contas a pagar e a receber usa a empresa do usuário mesmo quando o
  Master está com outra empresa selecionada;
- pagamentos localizam conta/parcela por ID sem validar previamente a empresa;
- `contas_correntes` não possui `empresa_id` na migration inicial, embora o model
  declare uma relação com Empresa;
- há consultas de Master que ficam sem filtro quando nenhuma empresa foi
  selecionada.

## Integridade e concorrência

Há uso de `DB::transaction()` nos principais pagamentos e recebimentos. No
entanto:

- contas a pagar e a receber não usam `lockForUpdate()`;
- a prevenção de duplicidade em reservas é temporal, sem chave idempotente;
- conta a pagar não impede explicitamente pagamento superior ao saldo;
- conta única é marcada como paga mesmo quando o valor informado é parcial;
- atualização de transação de reserva não sincroniza o movimento de caixa;
- algumas rotinas capturam falhas de movimentação e mantêm o registro principal;
- existem exclusões físicas de registros financeiros.

Esses comportamentos devem permanecer documentados, mas não devem ser copiados
para a API. A extração para serviços deverá tratá-los como riscos a corrigir com
testes de regressão.

## Autenticação e autorização

As rotas financeiras estão em `web.php`. Parte delas usa middleware de permissão,
parte usa somente autenticação indireta e parte não declara proteção. O controller
base não adiciona `auth` globalmente.

Antes de expor operações ao aplicativo, será necessário:

- autenticação por token;
- usuário ativo;
- permissão de leitura ou escrita específica;
- escopo obrigatório de empresa;
- bloqueio de escrita para o perfil da dona;
- auditoria da origem da operação.

## Linha de base dos testes

Comando executado:

```text
php artisan test
```

Resultado inicial: 13 testes passaram e 29 falharam, com 42 assertions.

As causas identificadas foram:

- `pdo_sqlite` e `sqlite3` não estão instalados no PHP usado pelos testes; isso
  impede os testes Feature e um teste Unit que persiste models;
- o teste de contrato de `RecebimentoExcursao` está desatualizado após a inclusão
  de `fluxo_caixa_id` e `fluxo_cancelamento_id`;
- o teste de exemplo espera HTTP 200 para `/`, mas a rota redireciona para login e
  retorna HTTP 302.

Os testes puros de `ExcursaoFinanceiroService` passam. Foram adicionadas
caracterizações sem banco para o calendário de contas, estados de parcela e
contrato básico de transações. Os testes de dashboard, projeção, contas a pagar,
contas a receber e pagamentos de reserva ainda não existem e deverão ser criados
junto à extração de cada serviço.

Depois da atualização dos dois contratos obsoletos e da inclusão das novas
caracterizações, a suíte registra 17 testes passando e 28 falhando, com 54
assertions. As 28 falhas restantes dependem da conexão SQLite ausente; portanto,
o resultado ainda não representa falhas funcionais confirmadas nesses cenários.

## Ordem indicada para a próxima etapa

1. Tornar o ambiente de testes capaz de usar SQLite ou definir um banco MySQL
   isolado de testes.
2. Atualizar os dois testes obsoletos sem mascarar falhas reais.
3. Criar um resolvedor único e testável de empresa ativa.
4. Extrair primeiro a projeção e o dashboard para serviços de consulta.
5. Extrair operações monetárias com bloqueio, idempotência e auditoria.
