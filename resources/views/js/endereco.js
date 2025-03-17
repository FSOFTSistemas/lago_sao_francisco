$(document).ready(function() {
    console.log('Script endereco.js carregado');

    // Lógica de envio do formulário
    $('#formEndereco').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                console.log(response);
                if (response.success) {
                    $('#endereco_id').append(
                        new Option(
                            `${response.endereco.logradouro}, ${response.endereco.numero} - ${response.endereco.bairro}`,
                            response.endereco.id,
                            true,
                            true
                        )
                    );

                    alert(response.message || 'Endereço salvo com sucesso!');
                
                    $('#enderecoModal').modal('hide'); 
                    $('.modal-backdrop').remove(); 
                    form[0].reset();
                } else {
                    alert(response.message || 'Erro ao salvar o endereço.');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let message = xhr.responseJSON?.message || 'Erro ao salvar o endereço.';
                console.log(errors);
                console.log(xhr);

                if (errors) {
                    message += '\n' + Object.values(errors).map((err) => `- ${err}`).join('\n');
                }

                alert(message);
                console.error(message);
            }
        });
    });

    // Lógica de busca de endereço pelo CEP
    document.getElementById('buscarCep').addEventListener('click', function() {
        var cep = document.getElementById('cep').value;

        if (!cep) {
            alert('Por favor, insira um CEP.');
            return;
        }
        console.log(cep);
        fetch(`/endereco/${cep}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao buscar endereço. Código de status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!data.erro) {
                    console.log(data);
                    $('#logradouro').val(data.logradouro);
                    $('#cidade').val(data.localidade);
                    $('#uf').val(data.uf);
                    $('#bairro').val(data.bairro);
                    $('#ibge').val(data.ibge);
                } else {
                    alert('CEP não encontrado.');
                }
            })
            .catch(error => {
                console.log();
                console.error('Erro ao buscar o endereço:', error);
                alert('Erro ao buscar o endereço. Tente novamente.');
            });
    });
});
