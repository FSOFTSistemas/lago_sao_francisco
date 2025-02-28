$(document).ready(function () {
    console.log("🚀 Script carregado e jQuery funcionando!");

    $("#cep").on("blur", function () {
        let cep = $(this).val().replace(/\D/g, "");
        console.log("CEP digitado:", cep);

        if (cep.length === 8) {
            $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function (data) {
                if (!("erro" in data)) {
                    console.log("✅ CEP encontrado:", data);
                    $("#logradouro").val(data.logradouro);
                    $("#bairro").val(data.bairro);
                    $("#cidade").val(data.localidade);
                    $("#uf").val(data.uf);
                    $("#ibge").val(data.ibge);
                } else {
                    alert("CEP não encontrado!");
                }
            });
        } else {
            alert("Formato de CEP inválido!");
        }
    });
});
