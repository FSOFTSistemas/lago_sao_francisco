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
            .fail(onError);
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

    // Lógica de busca de endereço pelo botão "Buscar CEP"
    // document.getElementById("buscarCep").addEventListener("click", function () {
    //     const cep = document.getElementById("cep").value.trim();

    //     if (!cep) {
    //         alert("Por favor, insira um CEP.");
    //         return;
    //     }

    //     console.log("Buscando informações do CEP via botão:", cep);

    //     fetch(`/endereco/${cep}`)
    //         .then(response => {
    //             if (!response.ok) {
    //                 throw new Error(`Erro ao buscar endereço. Status: ${response.status}`);
    //             }
    //             return response.json();
    //         })
    //         .then(data => {
    //             if (!data.erro) {
    //                 console.log("✅ Informações recebidas do servidor:", data);
    //                 $("#logradouro").val(data.logradouro);
    //                 $("#cidade").val(data.localidade);
    //                 $("#uf").val(data.uf);
    //                 $("#bairro").val(data.bairro);
    //                 $("#ibge").val(data.ibge);
    //             } else {
    //                 alert("CEP não encontrado.");
    //             }
    //         })
    //         .catch(error => {
    //             console.error("❌ Erro ao buscar endereço:", error);
    //             alert("Erro ao buscar o endereço. Tente novamente.");
    //         });
    // });
});
