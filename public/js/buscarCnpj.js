$(document).ready(function () {
    console.log("🚀 Script de cnpj carregado e jQuery funcionando!");

     //Função para buscar informações do Cnpj
     $('#btnBuscarCnpj').on('click', function () {
        const cnpj = $('#cnpj').val().replace(/\D/g, '');
        console.log(cnpj)
         if (cnpj.length !== 14) {
             alert('CNPJ inválido. Deve conter 14 dígitos.');
             return;
         }

         fetch(`https://open.cnpja.com/office/${cnpj}`)
             .then(response => {
                 return response.json();
             })
             .then(data => {
                 console.log(data);
                 const razao = data.company?.name || 'Não encontrado';
                 $('#nomeRazaoSocial').val(razao);
                 const fantasia = data.alias || 'Não encontrado';
                 $('#apelidoNomeFantasia').val(fantasia);
                 const inscricaoEstadual = data.registrations[0]?.number || "Não encontrado";
                 $('#inscricaoEstadual').val(inscricaoEstadual);
                 const numero = data.phones[0] ? `(${data.phones[0].area}) ${data.phones[0].number}` : "Não encontrado";
                 $('#whatsapp').val(numero);
                 $('#telefone').val(numero);
             })
             .catch(error => {
                 console.error(error);
                 alert('Erro ao buscar CNPJ. Verifique o console.');
             });
     })
});

