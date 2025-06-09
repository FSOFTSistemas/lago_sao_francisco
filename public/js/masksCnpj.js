$(document).ready(function() {
    // Verifica se o plugin de máscara está carregado
    if (typeof $.fn.mask !== 'undefined') {
        console.log('jQuery Mask plugin carregado com sucesso');
        
        // Aplica máscaras
        $('#whatsapp').mask('(00)00000-0000');
        $('#telefone').mask('(00)0000-0000');
        $('#cpf').mask('000.000.000-00', {reverse: true});
        $('#cnpj').mask('00.000.000/0000-00', {reverse: true});
        $('#rg').mask('00.000.000-0', {reverse: true});
        
    } else {
        console.error('jQuery Mask plugin não está disponível');
    }
});