# Contexto seguro de empresa

Toda rota de negócio da futura API deverá executar, nesta ordem:

```text
auth:sanctum -> usuário ativo -> empresa.ativa -> permission
```

O middleware `empresa.ativa` resolve uma Empresa existente e a disponibiliza no
`CompanyContext`. Controllers e serviços não devem ler `session('empresa_id')`
nem aceitar `empresa_id` do corpo da requisição.

## Contrato do aplicativo

- Cabeçalho opcional: `X-Empresa-Id: {id}`.
- Sem cabeçalho, é usada a `empresa_id` vinculada ao usuário.
- Financeiro só pode usar sua própria empresa, mesmo alterando o cabeçalho.
- Master pode selecionar outra empresa existente.
- ID inválido retorna erro de validação.
- Empresa inexistente retorna 404.
- Empresa não autorizada retorna 403.
- Usuário não autenticado retorna 401.
- A empresa não pode mudar no meio da mesma requisição.

Após a resolução, ficam disponíveis:

```php
$company = app(CompanyContext::class)->company();
$companyId = app(CompanyContext::class)->id();
```

O middleware também adiciona `empresa` e `empresa_id` aos atributos da Request.
Isso é apenas conveniência; a fonte de verdade é o `CompanyContext`.

## Limitação atual

O schema associa cada usuário a uma única empresa. O acesso da dona a outras
empresas continua baseado no papel Master, como já ocorre no sistema web. Se no
futuro outros usuários precisarem acessar várias empresas, deverá ser criada uma
tabela de associação explícita, por exemplo `empresa_user`, e o resolvedor passará
a consultá-la. Não se deve ampliar o papel Master para atender a esse caso.

## Uso obrigatório nas consultas

Resolver a empresa não filtra Eloquent automaticamente. Cada serviço deverá
iniciar suas consultas com o ID do contexto:

```php
ContasAPagar::query()
    ->where('empresa_id', $companyContext->id());
```

Registros recebidos por route model binding também deverão ser buscados dentro da
empresa ativa; encontrar um ID globalmente e validar depois não é suficiente.
