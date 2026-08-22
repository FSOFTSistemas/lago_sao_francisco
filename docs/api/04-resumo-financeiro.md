# Resumo financeiro compartilhado

O resumo do aplicativo foi separado do domínio de hotel. Ele não consulta nem
altera reservas, quartos, hóspedes, tarifas ou transações de hospedagem.

## Fontes incluídas

- `contas_a_receber`, sempre filtrada por `empresa_id`;
- `contas_a_pagar`, sempre filtrada por `empresa_id`;
- `parcelas_contas_a_pagar`, acessadas por uma conta já filtrada;
- `fluxo_caixas`, sempre filtrado por `empresa_id`.

## Contrato do cálculo

Contas a receber apresentam valor recebido, pendente, atrasado e saldo aberto.
Contas a pagar apresentam valor pago, pendente, atrasado e saldo aberto. Como
contas a pagar não possuem status `atrasado`, o atraso é determinado pela data de
vencimento.

O cálculo também considera uma conta a receber pendente como atrasada quando sua
data já passou, mesmo que o comando diário de atualização ainda não tenha rodado.

Para contas parceladas, cada parcela é considerada individualmente e a conta-pai
não é somada novamente.

O fluxo realizado considera:

```text
líquido = entradas - saídas - cancelamentos
```

Movimentos de abertura e fechamento não entram nesse total. A projeção líquida em
aberto é:

```text
contas a receber em aberto - contas a pagar em aberto
```

## Componentes

- `FinancialSummaryRepository`: realiza apenas consultas financeiras com empresa.
- `FinancialSummaryCalculator`: calcula totais sem acessar banco ou autenticação.
- `FinancialSummaryService`: obtém a empresa do `CompanyContext`, valida o período
  e coordena consulta e cálculo.

Nenhum controller web foi alterado nesta etapa. O serviço será consumido pelo
futuro endpoint de dashboard da API.
