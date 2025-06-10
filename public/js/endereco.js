$(document).ready(function () {
    console.log("🚀 Script carregado e jQuery funcionando!");

    // Função para buscar informações do CEP
    const buscarCEP = (cep, onSuccess, onError) => {
        if (!cep || cep.length !== 8 || /\D/.test(cep)) {
            alert("Formato de CEP inválido!");
            return;
        }

        console.log("Buscando informações do CEP:", cep);
        $.getJSON(`https://viacep.com.br/ws/${cep}/json/`)
            .done(function (data) {
                if (!("erro" in data)) {
                    console.log("✅ CEP encontrado:", data);
                    onSuccess(data);
                } else {
                    alert("CEP não encontrado!");
                }
            })
            .fail(function () {
                console.error("❌ Erro na requisição do CEP.");
                if (onError) onError();
            });
    };

    // Preenchimento automático ao perder o foco do campo CEP
    $("#cep").on("blur", function () {
        const cep = $(this).val().replace(/\D/g, "");

        buscarCEP(
            cep,
            function (data) {
                $("#logradouro").val(data.logradouro);
                $("#bairro").val(data.bairro);
                $("#cidade").val(data.localidade);
                $("#uf").val(data.uf);
                $("#ibge").val(data.ibge);
            },
            function () {
                alert("Erro ao buscar informações do CEP. Tente novamente.");
            }
        );
    });

    // Lógica de envio do formulário
    $('#formEndereco').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);

        console.log("Submetendo formulário...");

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    console.log("✅ Endereço salvo com sucesso:", response);

                    // Adiciona o novo endereço ao select
                    $('#endereco_id').append(
                        new Option(
                            `${response.endereco.logradouro}, ${response.endereco.numero} - ${response.endereco.bairro}`,
                            response.endereco.id,
                            true,
                            true
                        )
                    );

                    alert(response.message || "Endereço salvo com sucesso!");
                    $('#enderecoModal').modal('hide');
                    form[0].reset(); // Limpa o formulário
                } else {
                    alert(response.message || "Erro ao salvar o endereço.");
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                let message = xhr.responseJSON?.message || "Erro ao salvar o endereço.";
                console.error("❌ Erro no envio do formulário:", xhr);

                if (errors) {
                    message += '\n' + Object.values(errors).map(err => `- ${err}`).join('\n');
                }

                alert(message);
            }
        });
    });
});