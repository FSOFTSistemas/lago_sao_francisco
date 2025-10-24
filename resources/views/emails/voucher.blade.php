<!DOCTYPE html>
<html>
<head>
    <title>Voucher de Reserva</title>
</head>
<body>
    <p>Olá, {{ $reserva->hospede->nome ?? 'Cliente' }}!</p>
    
    <p>Obrigado por reservar conosco.</p>

    <p>Em anexo, você encontra o voucher da sua reserva (Nº {{ $reserva->id }}).</p>

    <p>Estamos ansiosos pela sua visita!</p>
    <br>
    <p>Atenciosamente,</p>
    <p>Hotel Estação Chico</p>
</body>
</html>