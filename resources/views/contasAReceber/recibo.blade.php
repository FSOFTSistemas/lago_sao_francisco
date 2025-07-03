<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .titulo { text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 20px; }
        .linha { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="titulo">Recibo de Pagamento</div>

    <div class="linha"><strong>Cliente:</strong> {{ $cliente }}</div>
    <div class="linha"><strong>Descrição:</strong> {{ $descricao }}</div>
    <div class="linha"><strong>Parcela:</strong> {{ $parcela }}</div>
    <div class="linha"><strong>Valor Pago:</strong> R$ {{ $valor }}</div>
    <div class="linha"><strong>Forma de Pagamento:</strong> {{ ucfirst($forma_pagamento) }}</div>
    <div class="linha"><strong>Data de Vencimento:</strong> {{ $data_vencimento }}</div>
    <div class="linha"><strong>Data de Pagamento:</strong> {{ $data_pagamento }}</div>

    <p style="margin-top: 30px;">Assinatura: ___________________________________________</p>
</body>
</html>
