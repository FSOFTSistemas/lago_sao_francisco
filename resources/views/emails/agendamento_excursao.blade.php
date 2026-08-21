<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agendamento de excursão</title>
</head>
<body style="font-family: Arial, sans-serif; color: #26332a; line-height: 1.5;">
    <h2 style="color: #3e7222;">Informações do agendamento</h2>

    <p>Olá, {{ $excursao->responsavel }}!</p>
    <p>Confira abaixo as informações da excursão agendada:</p>

    <table cellpadding="7" cellspacing="0" style="width: 100%; max-width: 600px; border-collapse: collapse;">
        <tr><td style="border: 1px solid #dce6d7;"><strong>Código</strong></td><td style="border: 1px solid #dce6d7;">#{{ $excursao->id }}</td></tr>
        <tr><td style="border: 1px solid #dce6d7;"><strong>Data agendada</strong></td><td style="border: 1px solid #dce6d7;">{{ $excursao->data->format('d/m/Y') }}</td></tr>
        <tr><td style="border: 1px solid #dce6d7;"><strong>Quantidade de pessoas</strong></td><td style="border: 1px solid #dce6d7;">{{ $excursao->qtd_pessoas }}</td></tr>
        <tr><td style="border: 1px solid #dce6d7;"><strong>Descrição</strong></td><td style="border: 1px solid #dce6d7;">{{ $excursao->descricao }}</td></tr>
        @if ($excursao->almoco)
            <tr><td style="border: 1px solid #dce6d7;"><strong>Almoço</strong></td><td style="border: 1px solid #dce6d7;">{{ $excursao->almoco->nome_cardapio }} ({{ $excursao->almoco->quantidade }} pessoas)</td></tr>
        @endif
        <tr><td style="border: 1px solid #dce6d7;"><strong>Valor total</strong></td><td style="border: 1px solid #dce6d7;">R$ {{ number_format((float) $excursao->total, 2, ',', '.') }}</td></tr>
        <tr><td style="border: 1px solid #dce6d7;"><strong>Valor pago</strong></td><td style="border: 1px solid #dce6d7;">R$ {{ number_format((float) $excursao->valor_pago, 2, ',', '.') }}</td></tr>
        <tr><td style="border: 1px solid #dce6d7;"><strong>Valor restante</strong></td><td style="border: 1px solid #dce6d7;">R$ {{ number_format((float) $excursao->valor_restante, 2, ',', '.') }}</td></tr>
    </table>

    <p>Este e-mail é apenas informativo e não exige confirmação. Não responda esse email.</p>
    <p>Atenciosamente,<br>{{ config('app.name') }}</p>
</body>
</html>
