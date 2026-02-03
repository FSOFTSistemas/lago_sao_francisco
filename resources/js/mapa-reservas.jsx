import "./bootstrap";
import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom/client";
import axios from "axios";
import Select from "react-select";
import Swal from "sweetalert2";

// --- COMPONENTE MODAL SIMPLES ---
const SimpleModal = ({ show, onClose, title, children, size = "md" }) => {
    if (!show) return null;
    return (
        <div className="react-modal-backdrop" onClick={onClose}>
            <div
                className="react-modal-dialog"
                style={{ maxWidth: size === "sm" ? "300px" : "600px" }}
                onClick={(e) => e.stopPropagation()}
            >
                <div className="modal-header">
                    <h5 className="modal-title">{title}</h5>
                    <button type="button" className="close" onClick={onClose}>
                        <span>&times;</span>
                    </button>
                </div>
                <div className="modal-body">{children}</div>
            </div>
        </div>
    );
};

// --- FUNÇÕES AUXILIARES ---
const formatMoney = (value) =>
    parseFloat(value || 0).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
    });
const formatDate = (dateString) => {
    if (!dateString) return "-";
    const [year, month, day] = dateString.split("-");
    return `${day}/${month}/${year}`;
};

// --- COMPONENTE PRINCIPAL ---
export default function MapaReservas({
    hospedesIniciais,
    dataInicioInicial,
    dataFimInicial,
}) {
    // --- ESTADOS ---
    const [hospedes, setHospedes] = useState(hospedesIniciais || []);
    const [dadosMapa, setDadosMapa] = useState(null);
    const [loading, setLoading] = useState(true);

    // Filtros
    const [filtroDataInicio, setFiltroDataInicio] = useState(dataInicioInicial);
    const [filtroDataFim, setFiltroDataFim] = useState(dataFimInicial);

    // Modais e Seleções
    const [celulaSelecionada, setCelulaSelecionada] = useState(null);
    const [showModalAcoes, setShowModalAcoes] = useState(false);
    const [showModalReserva, setShowModalReserva] = useState(false);
    const [showModalBloqueio, setShowModalBloqueio] = useState(false);

    const [showModalDetalhes, setShowModalDetalhes] = useState(false);
    const [reservaDetalhes, setReservaDetalhes] = useState(null);
    const [financeiroDetalhes, setFinanceiroDetalhes] = useState(null);
    const [loadingAction, setLoadingAction] = useState(false);

    const [loadingNovoHospede, setLoadingNovoHospede] = useState(false);

    // Estado local para controle do desbloqueio de valor
    const [isDiariaUnlocked, setIsDiariaUnlocked] = useState(false);

    // Estado para cadastro rápido de hóspede
    const [showModalNovoHospede, setShowModalNovoHospede] = useState(false);
    const [novoHospedeNome, setNovoHospedeNome] = useState("");

    // Forms
    const [formReserva, setFormReserva] = useState({
        hospede_id: "",
        data_checkin: "",
        data_checkout: "",
        situacao: "pre-reserva",
        n_adultos: 1,
        n_criancas: 0,
        n_criancas_nao_pagantes: 0,
        qtd_pet_pequeno: 0,
        qtd_pet_medio: 0,
        qtd_pet_grande: 0,
        nomes_hospedes_secundarios: "",
        valor_diaria: "",
        supervisor_id_autorizacao: "",
    });

    const [formBloqueio, setFormBloqueio] = useState({
        quarto_ids: [],
        selectedOptions: [],
        data_checkin: "",
        data_checkout: "",
        observacoes: "Manutenção / Bloqueio",
    });

    useEffect(() => {
        fetchMapa();
    }, []);

    // --- BUSCA DADOS ---
    const fetchMapa = async () => {
        setLoading(true);
        try {
            const response = await axios.get("/mapa/dados", {
                params: {
                    data_inicio: filtroDataInicio,
                    data_fim: filtroDataFim,
                    _: new Date().getTime(),
                },
            });
            if (response.data.success) {
                const dados = response.data;
                if (dados.quartos && Array.isArray(dados.quartos)) {
                    dados.quartos.sort((a, b) => {
                        const posA = parseInt(a.posicao, 10) || 0;
                        const posB = parseInt(b.posicao, 10) || 0;
                        if (posA === posB)
                            return a.nome.localeCompare(b.nome, undefined, {
                                numeric: true,
                            });
                        return posA - posB;
                    });
                }
                setDadosMapa(dados);
            } else {
                Swal.fire("Erro", response.data.message, "error");
            }
        } catch (error) {
            console.error(error);
            Swal.fire("Erro", "Erro de conexão com o servidor.", "error");
        } finally {
            setLoading(false);
        }
    };

    // --- DRAG AND DROP HANDLERS ---
    const handleDragStart = (e, reserva) => {
        e.dataTransfer.setData("reservaId", reserva.id);
        e.dataTransfer.setData("hospedeNome", reserva.hospede_nome);
        e.dataTransfer.effectAllowed = "move";
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
    };

    const handleDrop = async (e, quartoDestino, dataCheckinDestino) => {
        e.preventDefault();
        const reservaId = e.dataTransfer.getData("reservaId");
        const hospedeNome = e.dataTransfer.getData("hospedeNome");

        if (!reservaId) return;

        const result = await Swal.fire({
            title: "Mover Reserva?",
            html: `Deseja mover a reserva de <b>${hospedeNome}</b> para o quarto <b>${quartoDestino.nome}</b> iniciando em <b>${formatDate(dataCheckinDestino)}</b>?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, mover!",
            cancelButtonText: "Cancelar",
        });

        if (result.isConfirmed) {
            try {
                const response = await axios.post("/mapa/mover-reserva", {
                    reserva_id: reservaId,
                    novo_quarto_id: quartoDestino.id,
                    nova_data_checkin: dataCheckinDestino,
                });

                if (response.data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Sucesso!",
                        text: response.data.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    fetchMapa();
                } else {
                    Swal.fire(
                        "Erro",
                        response.data.message || "Erro ao mover reserva.",
                        "error",
                    );
                }
            } catch (error) {
                const msg = error.response?.data?.message || error.message;
                Swal.fire("Erro", `Não foi possível mover: ${msg}`, "error");
            }
        }
    };

    // --- OPÇÕES PARA SELECTS ---
    const getOptionsHospedes = () => {
        return hospedes
            .filter((h) => h.nome !== "Bloqueado")
            .map((h) => ({
                value: h.id,
                label: h.nome,
            }));
    };

    const getOptionsQuartos = () => {
        if (!dadosMapa || !dadosMapa.quartos) return [];
        return dadosMapa.quartos.map((q) => ({
            value: q.id,
            label: `${q.nome} (${q.categoria_nome})`,
        }));
    };

    const podeFazerCheckin = (reserva) => {
        if (!reserva || reserva.situacao !== "reserva") return false;
        const hoje = new Date().toISOString().split("T")[0];
        return reserva.data_checkin <= hoje;
    };

    const handleSalvarNovoHospede = async (e) => {
        e.preventDefault();

        // Evita execução se já estiver carregando
        if (loadingNovoHospede) return;

        if (!novoHospedeNome.trim()) {
            Swal.fire("Atenção", "Informe o nome do hóspede.", "warning");
            return;
        }

        setLoadingNovoHospede(true); // BLOQUEIA O BOTÃO

        try {
            const response = await axios.post("/mapa/hospede-rapido", {
                nome: novoHospedeNome,
            });

            if (response.data.success) {
                const novoHospede = response.data.hospede;
                setHospedes((prev) => [...prev, novoHospede]);
                setFormReserva((prev) => ({
                    ...prev,
                    hospede_id: novoHospede.id,
                }));
                setNovoHospedeNome("");
                setShowModalNovoHospede(false);
                Swal.fire({
                    icon: "success",
                    title: "Cadastrado!",
                    text: "Hóspede cadastrado e selecionado.",
                    timer: 1500,
                    showConfirmButton: false,
                });
            } else {
                Swal.fire("Erro", response.data.message, "error");
            }
        } catch (error) {
            console.error(error);
            Swal.fire("Erro", "Erro ao cadastrar hóspede.", "error");
        } finally {
            setLoadingNovoHospede(false); // DESBLOQUEIA O BOTÃO (se o modal não fechar)
        }
    };

    const handleUnlockDiaria = async () => {
        const { value: password } = await Swal.fire({
            title: "Autorização de Supervisor",
            text: "Digite a senha para alterar o valor manualmente",
            input: "password",
            inputPlaceholder: "Senha do supervisor",
            inputAttributes: {
                autocapitalize: "off",
                autocomplete: "new-password",
                name: "pwd_sup_mapa_" + Math.random(),
            },
            showCancelButton: true,
            confirmButtonText: "Liberar",
            cancelButtonText: "Cancelar",
        });

        if (password) {
            try {
                const response = await axios.post("/validar-supervisor", {
                    senha: password,
                });

                if (response.data.success) {
                    setIsDiariaUnlocked(true);
                    setFormReserva((prev) => ({
                        ...prev,
                        supervisor_id_autorizacao: response.data.supervisor_id,
                    }));

                    Swal.fire({
                        icon: "success",
                        title: "Liberado",
                        text: "Você pode editar o valor da diária agora.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                } else {
                    Swal.fire("Erro", "Senha incorreta.", "error");
                }
            } catch (error) {
                console.error(error);
                Swal.fire("Erro", "Erro ao validar senha.", "error");
            }
        }
    };

    // --- RENDERIZAÇÃO VISUAL (Grid e Células) ---
    const renderLinhaQuarto = (quarto) => {
        const celulas = [];
        const datas = dadosMapa.datas;
        const totalDias = datas.length;
        const larguraDia = 60;

        const checkData = (d1, d2) =>
            d1 && d2 && d1.substring(0, 10) === d2.substring(0, 10);

        for (let i = 0; i < totalDias; i++) {
            const dataAtual = datas[i];
            const mesDiaAtual = dataAtual.substring(5);
            const isFeriadoCel = dadosMapa.feriados?.includes(mesDiaAtual);

            let reservaInicio = quarto.reservas.find((r) =>
                checkData(r.data_checkin, dataAtual),
            );

            // Lógica para pegar reserva contínua que começou antes da visualização atual
            if (i === 0 && !reservaInicio) {
                reservaInicio = quarto.reservas.find(
                    (r) =>
                        r.data_checkin < dataAtual &&
                        r.data_checkout > dataAtual,
                );
            }

            // Verifica se tem alguém saindo hoje neste quarto (para definir a margem)
            const reservaFim = quarto.reservas.find((r) =>
                checkData(r.data_checkout, dataAtual),
            );

            if (reservaInicio) {
                let slotsOcupados = 0;
                for (let j = i; j < totalDias; j++) {
                    const dFutura = datas[j];
                    if (checkData(dFutura, reservaInicio.data_checkout)) break;
                    slotsOcupados++;
                }

                // Tratamentos de borda
                if (
                    slotsOcupados === 0 &&
                    checkData(reservaInicio.data_checkout, dataAtual)
                )
                    slotsOcupados = 1;
                if (
                    slotsOcupados === 0 &&
                    reservaInicio.data_checkout > datas[totalDias - 1]
                )
                    slotsOcupados = totalDias - i;
                if (slotsOcupados === 0) slotsOcupados = 1;

                // --- INÍCIO DA CORREÇÃO ---
                // 1. Se tem reserva terminando hoje, empurramos a nova 30px (metade do slot)
                const margemEsquerda = reservaFim ? 30 : 0;

                const larguraGrid = slotsOcupados * larguraDia;

                // 2. Subtraímos a margem da largura total.
                // Se a barra começou 30px depois, ela precisa acabar 30px antes para manter a proporção correta.
                const larguraBarra = larguraGrid + 25 - margemEsquerda;
                // --- FIM DA CORREÇÃO ---

                celulas.push(
                    <div
                        key={`${quarto.id}-${dataAtual}-inicio`}
                        className={`quarto-cell ${isFeriadoCel ? "cell-feriado" : ""}`}
                        onDragOver={handleDragOver}
                        onDrop={(e) => handleDrop(e, quarto, dataAtual)}
                        style={{
                            minWidth: `${larguraGrid}px`,
                            width: `${larguraGrid}px`,
                            flex: `0 0 ${larguraGrid}px`,
                            position: "relative",
                            zIndex: 10,
                        }}
                    >
                        <div
                            className={`reserva-block situacao-${reservaInicio.situacao}`}
                            draggable={true}
                            onDragStart={(e) =>
                                handleDragStart(e, reservaInicio)
                            }
                            style={{
                                width: `${larguraBarra}px`,
                                marginLeft: `${margemEsquerda}px`, // Aplica o recuo visual
                                borderRadius: "4px",
                                zIndex: 20,
                                cursor: "grab",
                            }}
                            onClick={(e) => {
                                e.stopPropagation();
                                handleCellClick(
                                    quarto,
                                    dataAtual,
                                    reservaInicio,
                                );
                            }}
                            title={`Reserva: ${reservaInicio.hospede_nome}`}
                        >
                            <span
                                style={{
                                    paddingLeft: "10px",
                                    whiteSpace: "nowrap",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                }}
                            >
                                {reservaInicio.hospede_nome}
                            </span>
                        </div>
                    </div>,
                );

                if (slotsOcupados > 0) i += slotsOcupados - 1;
                continue;
            }

            celulas.push(
                <div
                    key={`${quarto.id}-${dataAtual}-vazio`}
                    className="quarto-cell"
                    onDragOver={handleDragOver}
                    onDrop={(e) => handleDrop(e, quarto, dataAtual)}
                    style={{
                        minWidth: `${larguraDia}px`,
                        width: `${larguraDia}px`,
                        flex: `0 0 ${larguraDia}px`,
                    }}
                    onClick={() => handleCellClick(quarto, dataAtual)}
                ></div>,
            );
        }
        return celulas;
    };

    const handleCellClick = async (quarto, data, reserva = null) => {
        if (reserva) {
            setReservaDetalhes(reserva);
            setFinanceiroDetalhes(null);
            setShowModalDetalhes(true);
            try {
                const response = await axios.get(
                    `/transacoes/resumo/${reserva.id}`,
                );
                if (response.data.success)
                    setFinanceiroDetalhes(response.data.resumo);
            } catch (error) {
                console.error(error);
            }
        } else {
            setCelulaSelecionada({
                quartoId: quarto.id,
                data: data,
                quartoNome: quarto.nome,
            });
            setShowModalAcoes(true);
        }
    };

    // --- FUNÇÃO RESTAURADA ---
    const handleRealizarCheckin = async () => {
        if (!reservaDetalhes) return;

        const result = await Swal.fire({
            title: "Confirmar Check-in",
            text: `Deseja realizar o check-in para ${reservaDetalhes.hospede_nome}?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sim, confirmar!",
            cancelButtonText: "Cancelar",
        });

        if (!result.isConfirmed) return;

        setLoadingAction(true);
        try {
            const response = await axios.put(
                `/reserva/${reservaDetalhes.id}/hospedar`,
            );
            if (response.data.success) {
                Swal.fire(
                    "Sucesso!",
                    "Check-in realizado com sucesso.",
                    "success",
                );
                setShowModalDetalhes(false);
                fetchMapa();
            } else {
                Swal.fire(
                    "Atenção",
                    response.data.message || "Erro ao realizar check-in.",
                    "warning",
                );
            }
        } catch (error) {
            const msg = error.response?.data?.message || error.message;
            Swal.fire("Erro", `Não foi possível processar: ${msg}`, "error");
        } finally {
            setLoadingAction(false);
        }
    };

    const handleSalvarReserva = async (e) => {
        e.preventDefault();

        const quarto = dadosMapa.quartos.find(
            (q) => q.id === celulaSelecionada.quartoId,
        );
        let capacidadeMaxima = 999;

        if (quarto) {
            if (quarto.ocupantes) {
                capacidadeMaxima = parseInt(quarto.ocupantes);
            } else if (quarto.categoria && quarto.categoria.ocupantes) {
                capacidadeMaxima = parseInt(quarto.categoria.ocupantes);
            }
        }

        const nAdultos = parseInt(formReserva.n_adultos || 0);
        const nCriancas = parseInt(formReserva.n_criancas || 0);
        const totalPessoas = nAdultos + nCriancas;

        if (totalPessoas > capacidadeMaxima + 10) {
            Swal.fire({
                icon: "warning",
                title: "Capacidade Excedida",
                html: `O quarto <b>${quarto?.nome}</b> comporta no máximo <b>${capacidadeMaxima}</b> pessoas.<br>Você selecionou ${totalPessoas} (Adultos + Crianças).`,
            });
            return;
        }

        let start = new Date(formReserva.data_checkin);
        let end = new Date(formReserva.data_checkout);

        if (start.getTime() >= end.getTime()) {
            end = new Date(start);
            end.setDate(end.getDate() + 1);
        }

        const checkoutString = end.toISOString().split("T")[0];

        if (!formReserva.hospede_id) {
            Swal.fire("Atenção", "Selecione um hóspede.", "warning");
            return;
        }

        try {
            let valorDiariaFormatado = null;
            if (formReserva.valor_diaria) {
                valorDiariaFormatado = formReserva.valor_diaria
                    .replace(/\./g, "")
                    .replace(",", ".");
            }

            const res = await axios.post("/mapa/criar-reserva", {
                ...formReserva,
                data_checkout: checkoutString,
                quarto_id: celulaSelecionada.quartoId,
                tipo: "reserva",
                valor_diaria: valorDiariaFormatado,
                supervisor_id_autorizacao:
                    formReserva.supervisor_id_autorizacao,
            });
            if (res.data.success) {
                Swal.fire("Sucesso", "Reserva criada com sucesso!", "success");
                setShowModalReserva(false);
                fetchMapa();
            } else {
                Swal.fire("Erro", res.data.message, "error");
            }
        } catch (e) {
            Swal.fire(
                "Erro",
                e.response?.data?.message ||
                    "Ocorreu um erro ao tentar salvar.",
                "error",
            );
        }
    };

    const handleSalvarBloqueio = async (e) => {
        e.preventDefault();
        if (formBloqueio.quarto_ids.length === 0)
            return Swal.fire(
                "Atenção",
                "Selecione pelo menos um quarto.",
                "warning",
            );

        let start = new Date(formBloqueio.data_checkin);
        let end = new Date(formBloqueio.data_checkout);

        if (start.getTime() >= end.getTime()) {
            end = new Date(start);
            end.setDate(end.getDate() + 1);
        }

        const checkoutString = end.toISOString().split("T")[0];

        const requests = formBloqueio.quarto_ids.map((qid) =>
            axios.post("/mapa/criar-reserva", {
                quarto_id: qid,
                data_checkin: formBloqueio.data_checkin,
                data_checkout: checkoutString,
                observacoes: formBloqueio.observacoes,
                situacao: "bloqueado",
                n_adultos: 1,
                n_criancas: 0,
                valor_diaria: 0,
                valor_total: 0,
                tipo: "bloqueio",
            }),
        );
        try {
            await Promise.all(requests);
            Swal.fire(
                "Sucesso",
                "Bloqueio(s) criado(s) com sucesso!",
                "success",
            );
            setShowModalBloqueio(false);
            fetchMapa();
        } catch (e) {
            Swal.fire("Erro", "Ocorreu um erro ao criar o bloqueio.", "error");
        }
    };

    const abrirFormularioReserva = () => {
        setShowModalAcoes(false);
        const checkin = celulaSelecionada.data;
        setFormReserva({
            ...formReserva,
            hospede_id: "",
            data_checkin: checkin,
            data_checkout: checkin,
            n_adultos: 1,
            n_criancas: 0,
            n_criancas_nao_pagantes: 0,
            qtd_pet_pequeno: 0,
            qtd_pet_medio: 0,
            qtd_pet_grande: 0,
            nomes_hospedes_secundarios: "",
            valor_diaria: "",
            supervisor_id_autorizacao: "",
        });
        setIsDiariaUnlocked(false);
        setShowModalReserva(true);
    };

    const abrirFormularioBloqueio = () => {
        setShowModalAcoes(false);
        const checkin = celulaSelecionada.data;
        const opt = getOptionsQuartos().find(
            (op) => op.value === celulaSelecionada.quartoId,
        );
        setFormBloqueio({
            quarto_ids: [celulaSelecionada.quartoId],
            selectedOptions: opt ? [opt] : [],
            data_checkin: checkin,
            data_checkout: checkin,
            observacoes: "Bloqueio",
        });
        setShowModalBloqueio(true);
    };

    return (
        <div className="w-100">
            {/* CSS */}
            <style>{`
                .quarto-cell { border-right: 1px solid #e0e0e0; padding: 0 !important; box-sizing: border-box; position: relative; }
                .reserva-block { height: 80%; top: 10%; display: flex; align-items: center; justify-content: flex-start; color: #fff; font-weight: bold; font-size: 11px; cursor: pointer; overflow: hidden; white-space: nowrap; position: absolute; box-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
                .reserva-block:hover { opacity: 0.9; z-index: 100 !important; }
                .situacao-pre-reserva { background-color: #ffc107; color: #000; }
                .situacao-reserva { background-color: #007bff; }
                .situacao-hospedado { background-color: #dc3545; }
                .situacao-finalizada { background-color: #17a2b8; }
                .situacao-bloqueado { background-color: #343a40; }
                .situacao-noshow { background-color: #e83e8c; }
                .mapa-container { height: 90vh; overflow: auto; position: relative; }
                .mapa-header { position: sticky; top: 0; z-index: 103; background-color: #fff; }
                /* Cor do cabeçalho do dia (mais forte) */
.bg-feriado { 
    background-color: #6b089c !important; 
    border-bottom: 3px solid #6b089c !important;
}
.bg-feriado .dia, .bg-feriado .data {
    color: #ffffff !important;
    font-weight: bold;
}

/* Cor das colunas no grid (mais suave) */
.cell-feriado { 
    background-color: #f6e4fd !important; /* Um tom de roxo bem clarinho */
}

/* Ajuste para não sumir com a borda direita */
.quarto-cell.cell-feriado {
    border-right: 1px solid #f8d7da !important;
}
            `}</style>

            {/* CABEÇALHO UNIFICADO: TÍTULO + LEGENDA + CONTROLES */}
            <div className="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                {/* Lado Esquerdo: Título e Legenda juntos */}
                <div className="d-flex align-items-center flex-wrap">
                    <h1 className="mb-0 mr-4" style={{ whiteSpace: "nowrap" }}>
                        Mapa de Reservas
                    </h1>

                    <div className="d-flex flex-wrap align-items-center gap-2">
                        {/* Removi o texto "Legenda:" para economizar espaço, mas pode manter se preferir */}
                        <span className="badge badge-warning text-dark mr-1">
                            Pré-reserva
                        </span>
                        <span className="badge badge-primary mr-1">
                            Reserva
                        </span>
                        <span className="badge badge-danger mr-1">
                            Hospedado
                        </span>
                        <span className="badge badge-info mr-1">
                            Finalizada
                        </span>
                        <span className="badge badge-dark mr-1">Bloqueado</span>
                        <span
                            className="badge"
                            style={{
                                backgroundColor: "#e83e8c",
                                color: "white",
                            }}
                        >
                            No Show
                        </span>
                    </div>
                </div>

                {/* Lado Direito: Inputs e Botão (Mantido igual) */}
                <div className="d-flex align-items-center mt-2 mt-md-0">
                    <input
                        type="date"
                        className="form-control form-control-sm mr-2"
                        value={filtroDataInicio}
                        onChange={(e) => setFiltroDataInicio(e.target.value)}
                    />
                    <input
                        type="date"
                        className="form-control form-control-sm mr-2"
                        value={filtroDataFim}
                        onChange={(e) => setFiltroDataFim(e.target.value)}
                    />
                    <button
                        className="btn btn-primary btn-sm"
                        onClick={fetchMapa}
                    >
                        <i className="fas fa-search"></i> Atualizar
                    </button>
                </div>
            </div>

            {loading && (
                <div className="text-center p-4">
                    <i className="fas fa-spinner fa-spin"></i> Carregando...
                </div>
            )}

            {!loading && dadosMapa && (
                <div className="card">
                    <div className="card-body p-0">
 <div className="mapa-container">
    <div className="mapa-header" style={{ minWidth: "fit-content" }}>
        <div className="row no-gutters flex-nowrap" style={{ width: "fit-content", minWidth: "100%" }}>
            <div className="col-2 header-quartos sticky-left" style={{ minWidth: "150px", position: "sticky", left: 0, zIndex: 102 }}>
                <div className="header-cell">Quartos / Mês</div>
            </div>
            <div className="col-10 d-flex">
                {dadosMapa.datas.map((data) => {
                    const d = new Date(data + "T00:00:00");
                    const mesDia = data.substring(5); // Extrai 'MM-DD'
                    const isFeriado = dadosMapa.feriados?.includes(mesDia);
                    return (
                        <div
                            key={data}
                            className={`data-header ${isFeriado ? 'bg-feriado' : ''}`}
                            style={{ minWidth: "60px", width: "60px", flex: "0 0 60px" }}
                        >
                            <div className="dia">{d.toLocaleDateString("pt-BR", { weekday: "short" }).toUpperCase()}</div>
                            <div className="data">{d.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit" })}</div>
                            <div className="ocupacao">{dadosMapa.ocupacao[data]?.percentual || 0}%</div>
                        </div>
                    );
                })}
            </div>
        </div>
    </div>
    <div id="mapa-body">
        {dadosMapa.quartos.map((q) => (
            <div key={q.id} className="quarto-row" style={{ minWidth: "fit-content" }}>
                <div className="row no-gutters flex-nowrap" style={{ width: "fit-content", minWidth: "100%" }}>
                    <div className="col-2 quarto-header sticky-left" style={{ minWidth: "150px", position: "sticky", left: 0, zIndex: 101, paddingLeft: 10 }}>
                        {q.nome}
                    </div>
                    <div className="col-10 d-flex">
                        {/* Abaixo está a lógica da função renderLinhaQuarto corrigida 
                           para pintar as células vazias:
                        */}
                        {(() => {
                            const celulas = [];
                            const datas = dadosMapa.datas;
                            const totalDias = datas.length;
                            const larguraDia = 60;

                            const checkData = (d1, d2) => d1 && d2 && d1.substring(0, 10) === d2.substring(0, 10);

                            for (let i = 0; i < totalDias; i++) {
                                const dataAtual = datas[i];
                                const mesDia = dataAtual.substring(5);
                                const isFeriado = dadosMapa.feriados?.includes(mesDia);

                                let reservaInicio = q.reservas.find((r) => checkData(r.data_checkin, dataAtual));

                                // Lógica para reserva contínua que começou antes
                                if (i === 0 && !reservaInicio) {
                                    reservaInicio = q.reservas.find((r) => r.data_checkin < dataAtual && r.data_checkout > dataAtual);
                                }

                                const reservaFim = q.reservas.find((r) => checkData(r.data_checkout, dataAtual));

                                if (reservaInicio) {
                                    let slotsOcupados = 0;
                                    for (let j = i; j < totalDias; j++) {
                                        if (checkData(datas[j], reservaInicio.data_checkout)) break;
                                        slotsOcupados++;
                                    }
                                    if (slotsOcupados === 0) slotsOcupados = 1;

                                    const margemEsquerda = reservaFim ? 30 : 0;
                                    const larguraGrid = slotsOcupados * larguraDia;
                                    const larguraBarra = larguraGrid + 25 - margemEsquerda;

                                    celulas.push(
                                        <div
                                            key={`${q.id}-${dataAtual}-inicio`}
                                            className={`quarto-cell ${isFeriado ? 'cell-feriado' : ''}`}
                                            style={{ minWidth: `${larguraGrid}px`, width: `${larguraGrid}px`, flex: `0 0 ${larguraGrid}px`, position: "relative", zIndex: 10 }}
                                            onDragOver={handleDragOver}
                                            onDrop={(e) => handleDrop(e, q, dataAtual)}
                                        >
                                            <div
                                                className={`reserva-block situacao-${reservaInicio.situacao}`}
                                                draggable={true}
                                                onDragStart={(e) => handleDragStart(e, reservaInicio)}
                                                style={{ width: `${larguraBarra}px`, marginLeft: `${margemEsquerda}px`, borderRadius: "4px", zIndex: 20, cursor: "grab" }}
                                                onClick={(e) => { e.stopPropagation(); handleCellClick(q, dataAtual, reservaInicio); }}
                                            >
                                                <span style={{ paddingLeft: "10px", whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
                                                    {reservaInicio.hospede_nome}
                                                </span>
                                            </div>
                                        </div>
                                    );
                                    if (slotsOcupados > 0) i += slotsOcupados - 1;
                                    continue;
                                }

                                // CÉLULA VAZIA: Aqui aplicamos a classe cell-feriado
                                celulas.push(
                                    <div
                                        key={`${q.id}-${dataAtual}-vazio`}
                                        className={`quarto-cell ${isFeriado ? 'cell-feriado' : ''}`}
                                        onDragOver={handleDragOver}
                                        onDrop={(e) => handleDrop(e, q, dataAtual)}
                                        style={{ minWidth: `${larguraDia}px`, width: `${larguraDia}px`, flex: `0 0 ${larguraDia}px` }}
                                        onClick={() => handleCellClick(q, dataAtual)}
                                    ></div>
                                );
                            }
                            return celulas;
                        })()}
                    </div>
                </div>
            </div>
        ))}
    </div>
</div>
                    </div>
                </div>
            )}

            {/* Modal Detalhes */}
            <SimpleModal
                show={showModalDetalhes}
                onClose={() => setShowModalDetalhes(false)}
                title="Detalhes da Reserva"
            >
                {reservaDetalhes && (
                    <div>
                        <div className="alert alert-info d-flex justify-content-between align-items-center">
                            <strong>Reserva #{reservaDetalhes.id}</strong>
                            <span
                                className={`badge situacao-${reservaDetalhes.situacao} text-uppercase px-3 py-2`}
                            >
                                {reservaDetalhes.situacao}
                            </span>
                        </div>
                        <h5 className="font-weight-bold mb-1">
                            {reservaDetalhes.hospede_nome}
                        </h5>
                        {reservaDetalhes.hospede_telefone && (
                            <div className="mb-2">
                                <a
                                    href={`https://wa.me/55${reservaDetalhes.hospede_telefone.replace(/\D/g, "")}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-success font-weight-bold"
                                    style={{ textDecoration: "none" }}
                                >
                                    <i className="fab fa-whatsapp mr-1"></i>{" "}
                                    {reservaDetalhes.hospede_telefone}
                                </a>
                            </div>
                        )}
                        {reservaDetalhes.vendedor_nome && (
                            <div className="mb-3 text-muted small">
                                <i className="fas fa-user-tag mr-1"></i>{" "}
                                Vendedor:{" "}
                                <strong>{reservaDetalhes.vendedor_nome}</strong>
                            </div>
                        )}
                        <div className="row mb-3 mt-3">
                            <div className="col-6">
                                <small className="text-muted">Check-in</small>
                                <div>
                                    {formatDate(reservaDetalhes.data_checkin)}
                                </div>
                            </div>
                            <div className="col-6">
                                <small className="text-muted">Check-out</small>
                                <div>
                                    {formatDate(reservaDetalhes.data_checkout)}
                                </div>
                            </div>
                        </div>
                        <div className="row mb-3">
                            <div className="col-12">
                                <label className="text-muted mb-0 small">
                                    Hóspedes
                                </label>
                                <div className="font-weight-bold">
                                    {reservaDetalhes.n_adultos} Adulto(s){" "}
                                    {reservaDetalhes.n_criancas > 0
                                        ? ` e ${reservaDetalhes.n_criancas} Criança(s)`
                                        : ""}
                                </div>
                            </div>
                        </div>
                        {reservaDetalhes.nomes_hospedes_secundarios && (
                            <div className="row mb-3">
                                <div className="col-12">
                                    <label className="text-muted mb-0 small">
                                        Hóspedes Secundários
                                    </label>
                                    <div className="font-weight-bold text-dark">
                                        {
                                            reservaDetalhes.nomes_hospedes_secundarios
                                        }
                                    </div>
                                </div>
                            </div>
                        )}
                        {reservaDetalhes.observacoes && (
                            <div className="row mb-3">
                                <div className="col-12">
                                    <label className="text-muted mb-0 small">
                                        Observações
                                    </label>
                                    <div
                                        className="p-2 rounded border bg-white"
                                        style={{
                                            fontSize: "0.9rem",
                                            fontStyle: "italic",
                                            whiteSpace: "pre-wrap",
                                        }}
                                    >
                                        {reservaDetalhes.observacoes}
                                    </div>
                                </div>
                            </div>
                        )}
                        {reservaDetalhes.situacao !== "bloqueado" && (
                            <div className="bg-light p-3 rounded mb-3 border">
                                <div className="row">
                                    <div className="col-6 mb-2">
                                        <label className="text-muted mb-0 small">
                                            Nº Diárias
                                        </label>
                                        <div className="font-weight-bold">
                                            {financeiroDetalhes
                                                ? financeiroDetalhes.num_diarias
                                                : "..."}
                                        </div>
                                    </div>
                                    <div className="col-6 mb-2">
                                        <label className="text-muted mb-0 small">
                                            Total Geral
                                        </label>
                                        <div className="text-primary font-weight-bold">
                                            {financeiroDetalhes
                                                ? formatMoney(
                                                      (parseFloat(
                                                          reservaDetalhes.valor_total,
                                                      ) || 0) +
                                                          (parseFloat(
                                                              financeiroDetalhes.total_produtos,
                                                          ) || 0),
                                                  )
                                                : "..."}
                                        </div>
                                    </div>
                                    <div className="col-6">
                                        <label className="text-muted mb-0 small">
                                            Recebido
                                        </label>
                                        <div className="text-success font-weight-bold">
                                            {financeiroDetalhes
                                                ? formatMoney(
                                                      financeiroDetalhes.total_recebido,
                                                  )
                                                : "..."}
                                        </div>
                                    </div>
                                    <div className="col-6">
                                        <label className="text-muted mb-0 small">
                                            Falta Receber
                                        </label>
                                        <div className="text-danger font-weight-bold h5 mb-0">
                                            {financeiroDetalhes
                                                ? formatMoney(
                                                      (parseFloat(
                                                          reservaDetalhes.valor_total,
                                                      ) || 0) +
                                                          (parseFloat(
                                                              financeiroDetalhes.total_produtos,
                                                          ) || 0) -
                                                          parseFloat(
                                                              financeiroDetalhes.total_recebido,
                                                          ) -
                                                          parseFloat(
                                                              financeiroDetalhes.total_descontos ||
                                                                  0,
                                                          ),
                                                  )
                                                : "..."}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                        <div className="border-top pt-3 mt-2 text-right">
                            {podeFazerCheckin(reservaDetalhes) && (
                                <button
                                    type="button"
                                    className="btn btn-success mr-2"
                                    onClick={handleRealizarCheckin}
                                    disabled={loadingAction}
                                >
                                    {loadingAction ? (
                                        <i className="fas fa-spinner fa-spin"></i>
                                    ) : (
                                        <i className="fas fa-check"></i>
                                    )}{" "}
                                    Fazer Check-in
                                </button>
                            )}
                            <button
                                className="btn btn-secondary mr-2"
                                onClick={() => setShowModalDetalhes(false)}
                            >
                                Fechar
                            </button>
                            <a
                                href={`/reserva/${reservaDetalhes.id}/edit`}
                                className="btn btn-primary"
                            >
                                <i className="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                )}
            </SimpleModal>

            <SimpleModal
                show={showModalAcoes}
                onClose={() => setShowModalAcoes(false)}
                title="Ação"
                size="sm"
            >
                <button
                    className="btn btn-primary btn-block mb-2"
                    onClick={abrirFormularioReserva}
                >
                    Reserva
                </button>
                <button
                    className="btn btn-dark btn-block"
                    onClick={abrirFormularioBloqueio}
                >
                    Bloqueio
                </button>
            </SimpleModal>

            <SimpleModal
                show={showModalBloqueio}
                onClose={() => setShowModalBloqueio(false)}
                title="Bloquear"
            >
                <form onSubmit={handleSalvarBloqueio}>
                    <div className="form-group">
                        <label>Quartos</label>
                        <Select
                            isMulti
                            options={getOptionsQuartos()}
                            value={formBloqueio.selectedOptions}
                            onChange={(s) =>
                                setFormBloqueio({
                                    ...formBloqueio,
                                    selectedOptions: s,
                                    quarto_ids: s?.map((i) => i.value) || [],
                                })
                            }
                        />
                    </div>
                    <div className="row">
                        <div className="col-6">
                            <input
                                type="date"
                                className="form-control"
                                value={formBloqueio.data_checkin}
                                onChange={(e) =>
                                    setFormBloqueio({
                                        ...formBloqueio,
                                        data_checkin: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="col-6">
                            <input
                                type="date"
                                className="form-control"
                                value={formBloqueio.data_checkout}
                                onChange={(e) =>
                                    setFormBloqueio({
                                        ...formBloqueio,
                                        data_checkout: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>
                    <textarea
                        className="form-control mt-2"
                        value={formBloqueio.observacoes}
                        onChange={(e) =>
                            setFormBloqueio({
                                ...formBloqueio,
                                observacoes: e.target.value,
                            })
                        }
                    ></textarea>
                    <div className="text-right mt-3">
                        <button type="submit" className="btn btn-dark">
                            Salvar
                        </button>
                    </div>
                </form>
            </SimpleModal>

            <SimpleModal
                show={showModalReserva}
                onClose={() => setShowModalReserva(false)}
                title="Nova Reserva"
            >
                <form onSubmit={handleSalvarReserva}>
                    <div className="form-group mb-2">
                        <label className="small mb-1 font-weight-bold">
                            Hóspede
                        </label>
                        <div className="d-flex">
                            <div className="flex-grow-1">
                                <Select
                                    options={getOptionsHospedes()}
                                    placeholder="Buscar hóspede..."
                                    value={getOptionsHospedes().find(
                                        (op) =>
                                            op.value === formReserva.hospede_id,
                                    )}
                                    onChange={(op) =>
                                        setFormReserva({
                                            ...formReserva,
                                            hospede_id: op ? op.value : "",
                                        })
                                    }
                                    isClearable
                                    isSearchable
                                    noOptionsMessage={() =>
                                        "Nenhum hóspede encontrado"
                                    }
                                    styles={{
                                        control: (base) => ({
                                            ...base,
                                            minHeight: "38px",
                                            borderTopRightRadius: 0,
                                            borderBottomRightRadius: 0,
                                            borderColor: "#ced4da",
                                        }),
                                    }}
                                />
                            </div>
                            <button
                                type="button"
                                className="btn btn-primary"
                                onClick={() => setShowModalNovoHospede(true)}
                                style={{
                                    borderTopLeftRadius: 0,
                                    borderBottomLeftRadius: 0,
                                }}
                                title="Cadastrar Novo Hóspede"
                            >
                                <i className="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div className="row mb-2">
                        <div className="col-6">
                            <label className="small mb-1">Check-in</label>
                            <input
                                type="date"
                                className="form-control"
                                value={formReserva.data_checkin}
                                onChange={(e) =>
                                    setFormReserva({
                                        ...formReserva,
                                        data_checkin: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="col-6">
                            <label className="small mb-1">Check-out</label>
                            <input
                                type="date"
                                className="form-control"
                                value={formReserva.data_checkout}
                                onChange={(e) =>
                                    setFormReserva({
                                        ...formReserva,
                                        data_checkout: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>

                    <div className="form-group mb-2">
                        <label className="small mb-1">Situação</label>
                        <select
                            className="form-control"
                            value={formReserva.situacao}
                            onChange={(e) =>
                                setFormReserva({
                                    ...formReserva,
                                    situacao: e.target.value,
                                })
                            }
                        >
                            <option value="pre-reserva">Pré-reserva</option>
                            <option value="reserva">Reserva</option>
                        </select>
                    </div>

                    <div className="row mb-2">
                        <div className="col-4">
                            <label className="small mb-1">Adultos</label>
                            <input
                                type="number"
                                className="form-control"
                                min="1"
                                value={formReserva.n_adultos}
                                onChange={(e) =>
                                    setFormReserva({
                                        ...formReserva,
                                        n_adultos: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="col-4">
                            <label className="small mb-1">Crianças (Pg)</label>
                            <input
                                type="number"
                                className="form-control"
                                min="0"
                                value={formReserva.n_criancas}
                                onChange={(e) =>
                                    setFormReserva({
                                        ...formReserva,
                                        n_criancas: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="col-4">
                            <label className="small mb-1">
                                Crianças (Ñ Pg)
                            </label>
                            <input
                                type="number"
                                className="form-control"
                                min="0"
                                value={formReserva.n_criancas_nao_pagantes}
                                onChange={(e) =>
                                    setFormReserva({
                                        ...formReserva,
                                        n_criancas_nao_pagantes: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </div>

                    <div className="form-group mb-2 border p-2 rounded">
                        <label className="small mb-1 font-weight-bold">
                            Pets (Qtd)
                        </label>
                        <div className="row">
                            <div className="col-4">
                                <label className="small mb-0">Pequeno</label>
                                <input
                                    type="number"
                                    className="form-control form-control-sm"
                                    min="0"
                                    value={formReserva.qtd_pet_pequeno}
                                    onChange={(e) =>
                                        setFormReserva({
                                            ...formReserva,
                                            qtd_pet_pequeno: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            <div className="col-4">
                                <label className="small mb-0">Médio</label>
                                <input
                                    type="number"
                                    className="form-control form-control-sm"
                                    min="0"
                                    value={formReserva.qtd_pet_medio}
                                    onChange={(e) =>
                                        setFormReserva({
                                            ...formReserva,
                                            qtd_pet_medio: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            <div className="col-4">
                                <label className="small mb-0">Grande</label>
                                <input
                                    type="number"
                                    className="form-control form-control-sm"
                                    min="0"
                                    value={formReserva.qtd_pet_grande}
                                    onChange={(e) =>
                                        setFormReserva({
                                            ...formReserva,
                                            qtd_pet_grande: e.target.value,
                                        })
                                    }
                                />
                            </div>
                        </div>
                    </div>

                    <div className="form-group">
                        <label className="small mb-1">
                            Valor da Diária (Manual)
                        </label>
                        <div className="input-group">
                            <div className="input-group-prepend">
                                <span className="input-group-text">R$</span>
                            </div>
                            <input
                                type="text"
                                className="form-control"
                                placeholder="Automático"
                                readOnly={!isDiariaUnlocked}
                                value={formReserva.valor_diaria}
                                onChange={(e) =>
                                    setFormReserva({
                                        ...formReserva,
                                        valor_diaria: e.target.value,
                                    })
                                }
                            />
                            <div className="input-group-append">
                                <button
                                    type="button"
                                    className={`btn ${isDiariaUnlocked ? "btn-success" : "btn-warning"}`}
                                    onClick={handleUnlockDiaria}
                                    disabled={isDiariaUnlocked}
                                    title="Desbloquear valor manual"
                                >
                                    <i
                                        className={`fas ${isDiariaUnlocked ? "fa-lock-open" : "fa-lock"}`}
                                    ></i>
                                </button>
                            </div>
                        </div>
                        <small
                            className="text-muted"
                            style={{ fontSize: "0.75rem" }}
                        >
                            Se vazio, o cálculo será automático.
                        </small>
                    </div>

                    <div className="form-group">
                        <label className="small mb-1">
                            Hóspedes Secundários (Nomes)
                        </label>
                        <textarea
                            className="form-control"
                            rows="2"
                            placeholder="Separe os nomes por vírgula..."
                            value={formReserva.nomes_hospedes_secundarios}
                            onChange={(e) =>
                                setFormReserva({
                                    ...formReserva,
                                    nomes_hospedes_secundarios: e.target.value,
                                })
                            }
                        ></textarea>
                    </div>

                    <div className="text-right mt-3">
                        <button type="submit" className="btn btn-primary">
                            Salvar
                        </button>
                    </div>
                </form>
            </SimpleModal>

            <SimpleModal
                show={showModalNovoHospede}
                onClose={() => setShowModalNovoHospede(false)}
                title="Novo Hóspede (Rápido)"
                size="sm"
            >
                <form onSubmit={handleSalvarNovoHospede}>
                    <div className="form-group">
                        <label className="font-weight-bold">
                            Nome Completo
                        </label>
                        <input
                            type="text"
                            className="form-control"
                            value={novoHospedeNome}
                            onChange={(e) => setNovoHospedeNome(e.target.value)}
                            placeholder="Digite o nome..."
                            autoFocus
                            disabled={loadingNovoHospede}
                        />
                    </div>
                    <div className="d-flex justify-content-end gap-2 mt-3">
                        <button
                            type="button"
                            className="btn btn-secondary mr-2"
                            onClick={() => setShowModalNovoHospede(false)}
                            disabled={loadingNovoHospede}
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            className="btn btn-success"
                            disabled={loadingNovoHospede}
                        >
                            {loadingNovoHospede ? (
                                <>
                                    <i className="fas fa-spinner fa-spin"></i>{" "}
                                    Salvando...
                                </>
                            ) : (
                                "Salvar"
                            )}
                        </button>
                    </div>
                </form>
            </SimpleModal>
        </div>
    );
}

const rootElement = document.getElementById("react-mapa-reservas-root");
if (rootElement) {
    if (!rootElement._reactRootContainer) {
        const root = ReactDOM.createRoot(rootElement);
        let hospedes = [];
        try {
            hospedes = JSON.parse(rootElement.dataset.hospedes || "[]");
        } catch (e) {}
        const dataInicio = rootElement.dataset.dataInicio;
        const dataFim = rootElement.dataset.dataFim;
        root.render(
            <React.StrictMode>
                <MapaReservas
                    hospedesIniciais={hospedes}
                    dataInicioInicial={dataInicio}
                    dataFimInicial={dataFim}
                />
            </React.StrictMode>,
        );
        rootElement._reactRootContainer = root;
    }
}
