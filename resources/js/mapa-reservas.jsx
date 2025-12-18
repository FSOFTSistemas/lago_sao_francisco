import './bootstrap';
import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import axios from 'axios';
import Select from 'react-select'; // Importação do React-Select

// --- Componente de Modal Simples ---
const SimpleModal = ({ show, onClose, title, children, size = 'md' }) => {
    if (!show) return null;
    return (
        <div className="react-modal-backdrop" onClick={onClose}>
            <div className="react-modal-dialog" style={{ maxWidth: size === 'sm' ? '300px' : '600px' }} onClick={e => e.stopPropagation()}>
                <div className="modal-header">
                    <h5 className="modal-title">{title}</h5>
                    <button type="button" className="close" onClick={onClose}>
                        <span>&times;</span>
                    </button>
                </div>
                <div className="modal-body">
                    {children}
                </div>
            </div>
        </div>
    );
};

// --- Formatadores Auxiliares ---
const formatMoney = (value) => {
    return parseFloat(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
};

export default function MapaReservas({ hospedesIniciais, dataInicioInicial, dataFimInicial }) {
    // --- ESTADOS GERAIS ---
    const [hospedes, setHospedes] = useState(hospedesIniciais || []);
    const [dadosMapa, setDadosMapa] = useState(null);
    const [loading, setLoading] = useState(true);
    
    // --- ESTADOS DE FILTRO ---
    const [filtroDataInicio, setFiltroDataInicio] = useState(dataInicioInicial);
    const [filtroDataFim, setFiltroDataFim] = useState(dataFimInicial);

    // --- ESTADOS DE SELEÇÃO E MODAIS ---
    const [celulaSelecionada, setCelulaSelecionada] = useState(null); 
    const [showModalAcoes, setShowModalAcoes] = useState(false);
    const [showModalReserva, setShowModalReserva] = useState(false);
    const [showModalBloqueio, setShowModalBloqueio] = useState(false);
    
    // Estados para Modal de Detalhes
    const [showModalDetalhes, setShowModalDetalhes] = useState(false);
    const [reservaDetalhes, setReservaDetalhes] = useState(null);
    const [financeiroDetalhes, setFinanceiroDetalhes] = useState(null);

    // --- ESTADOS DOS FORMULÁRIOS ---
    const [formReserva, setFormReserva] = useState({
        hospede_id: '',
        data_checkin: '',
        data_checkout: '',
        situacao: 'pre-reserva',
        n_adultos: 1,
        n_criancas: 0,
        valor_diaria: ''
    });

    const [formBloqueio, setFormBloqueio] = useState({
        quarto_ids: [], 
        selectedOptions: [], 
        data_checkin: '',
        data_checkout: '',
        observacoes: 'Manutenção / Bloqueio'
    });

    useEffect(() => {
        fetchMapa();
    }, []);

    const fetchMapa = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/mapa/dados', {
                params: { data_inicio: filtroDataInicio, data_fim: filtroDataFim }
            });
            if (response.data.success) {
                const dados = response.data;
                
                // --- CORREÇÃO DE ORDENAÇÃO ---
                // Força a ordenação numérica baseada no campo 'posicao'
                if (dados.quartos && Array.isArray(dados.quartos)) {
                    dados.quartos.sort((a, b) => {
                        // Converte para Inteiro antes de comparar
                        const posA = parseInt(a.posicao, 10) || 0;
                        const posB = parseInt(b.posicao, 10) || 0;
                        
                        // Se as posições forem iguais (ou ambas 0), ordena pelo nome para desempatar
                        if (posA === posB) {
                            return a.nome.localeCompare(b.nome, undefined, { numeric: true });
                        }
                        
                        return posA - posB;
                    });
                }
                // -----------------------------

                setDadosMapa(dados);
            } else {
                alert('Erro ao carregar mapa: ' + response.data.message);
            }
        } catch (error) {
            console.error(error);
            if (error.response) alert('Erro ao conectar com o servidor.');
        } finally {
            setLoading(false);
        }
    };

    const getOptionsQuartos = () => {
        // Ajustado para a nova estrutura plana (dadosMapa.quartos)
        if (!dadosMapa || !dadosMapa.quartos) return [];
        let options = [];
        
        dadosMapa.quartos.forEach(q => {
            options.push({ 
                value: q.id, 
                label: `${q.nome} (${q.categoria_nome})` 
            });
        });
        
        return options;
    };

    const encontrarReserva = (reservas, data) => {
        return reservas.find(r => r.data_checkin <= data && r.data_checkout > data);
    };

    // --- CLIQUE NA CÉLULA ---
    const handleCellClick = async (quarto, data, reserva = null) => {
        if (reserva) {
            setReservaDetalhes(reserva);
            setFinanceiroDetalhes(null); 
            setShowModalDetalhes(true);

            try {
                const response = await axios.get(`/transacoes/resumo/${reserva.id}`);
                if (response.data.success) {
                    setFinanceiroDetalhes(response.data.resumo);
                }
            } catch (error) {
                console.error("Erro ao carregar financeiro:", error);
            }

        } else {
            setCelulaSelecionada({ quartoId: quarto.id, data: data, quartoNome: quarto.nome });
            setShowModalAcoes(true);
        }
    };

    const abrirFormularioReserva = () => {
        setShowModalAcoes(false);
        const checkinDate = new Date(celulaSelecionada.data + 'T00:00:00');
        const checkoutDate = new Date(checkinDate);
        checkoutDate.setDate(checkoutDate.getDate() + 1);
        const checkoutString = checkoutDate.toISOString().split('T')[0];

        setFormReserva({
            ...formReserva,
            hospede_id: '',
            data_checkin: celulaSelecionada.data,
            data_checkout: checkoutString,
            valor_diaria: ''
        });
        setShowModalReserva(true);
    };

    const abrirFormularioBloqueio = () => {
        setShowModalAcoes(false);
        
        const checkinDate = new Date(celulaSelecionada.data + 'T00:00:00');
        const checkoutDate = new Date(checkinDate);
        checkoutDate.setDate(checkoutDate.getDate() + 1);
        const checkoutString = checkoutDate.toISOString().split('T')[0];

        const options = getOptionsQuartos();
        const optionSelecionada = options.find(op => op.value === celulaSelecionada.quartoId);

        setFormBloqueio({
            quarto_ids: [celulaSelecionada.quartoId],
            selectedOptions: optionSelecionada ? [optionSelecionada] : [],
            data_checkin: celulaSelecionada.data,
            data_checkout: checkoutString,
            observacoes: 'Bloqueio administrativo'
        });

        setShowModalBloqueio(true);
    };

    const handleSalvarBloqueio = async (e) => {
        e.preventDefault();

        if (formBloqueio.quarto_ids.length === 0) {
            alert('Selecione pelo menos um quarto.');
            return;
        }

        const requests = formBloqueio.quarto_ids.map(quartoId => {
            const payload = {
                quarto_id: quartoId,
                data_checkin: formBloqueio.data_checkin,
                data_checkout: formBloqueio.data_checkout,
                observacoes: formBloqueio.observacoes,
                situacao: 'bloqueado',
                n_adultos: 1, 
                n_criancas: 0, 
                valor_diaria: 0,
                valor_total: 0,
                tipo: 'bloqueio' 
            };
            return axios.post('/mapa/criar-reserva', payload);
        });

        try {
            await Promise.all(requests);
            alert('Bloqueio(s) criado(s) com sucesso!');
            setShowModalBloqueio(false);
            fetchMapa();
        } catch (error) {
            console.error(error);
            const msg = error.response?.data?.message || 'Erro ao criar alguns bloqueios.';
            if (error.response?.data?.errors) {
                const erros = Object.values(error.response.data.errors).flat().join('\n');
                alert('Erro de validação:\n' + erros);
            } else {
                alert(msg);
            }
        }
    };

    const handleSalvarReserva = async (e) => {
        e.preventDefault();
        
        const valorDiariaFloat = parseFloat(formReserva.valor_diaria.replace(/\./g, '').replace(',', '.')) || 0;
        
        const start = new Date(formReserva.data_checkin);
        const end = new Date(formReserva.data_checkout);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1;
        
        const valorTotal = valorDiariaFloat * diffDays;

        const payload = {
            ...formReserva,
            quarto_id: celulaSelecionada.quartoId,
            valor_diaria: valorDiariaFloat,
            valor_total: valorTotal,
            tipo: 'reserva'
        };

        try {
            const response = await axios.post('/mapa/criar-reserva', payload);
            if (response.data.success || response.data.redirect) {
                alert('Reserva criada com sucesso!');
                setShowModalReserva(false);
                fetchMapa();
            } else {
                alert(response.data.message || 'Erro desconhecido');
            }
        } catch (error) {
            console.error(error);
            alert('Erro ao salvar reserva.');
        }
    };

    const handleValorChange = (e) => {
        let value = e.target.value;
        value = value.replace(/\D/g, "");
        value = value.replace(/(\d)(\d{2})$/, "$1,$2");
        value = value.replace(/(?=(\d{3})+(\D))\B/g, ".");
        setFormReserva({ ...formReserva, valor_diaria: value });
    };

    const renderLinhaQuarto = (quarto) => {
        const celulas = [];
        const datas = dadosMapa.datas;
        
        for (let i = 0; i < datas.length; i++) {
            const dataAtual = datas[i];
            const reserva = encontrarReserva(quarto.reservas, dataAtual);

            if (reserva) {
                const inicioReserva = reserva.data_checkin;
                const ehInicioVisual = (dataAtual === inicioReserva) || (i === 0 && dataAtual > inicioReserva);

                if (ehInicioVisual) {
                    let duracaoDias = 0;
                    for (let j = i; j < datas.length; j++) {
                        const dataFutura = datas[j];
                        if (dataFutura < reserva.data_checkout) {
                            duracaoDias++;
                        } else {
                            break;
                        }
                    }

                    celulas.push(
                        <div 
                            key={`${quarto.id}-${dataAtual}`}
                            className="quarto-cell ocupado"
                            style={{ 
                                minWidth: `${duracaoDias * 60}px`, 
                                width: `${duracaoDias * 60}px`,
                                flex: `0 0 ${duracaoDias * 60}px` 
                            }}
                            onClick={() => handleCellClick(quarto, dataAtual, reserva)}
                        >
                            <div className={`reserva-block situacao-${reserva.situacao}`} style={{ width: '100%', height: '100%' }}>
                                {reserva.hospede_nome}
                            </div>
                        </div>
                    );
                    i += (duracaoDias - 1); 
                }
            } else {
                celulas.push(
                    <div 
                        key={`${quarto.id}-${dataAtual}`}
                        className="quarto-cell"
                        style={{ minWidth: '60px', width: '60px', flex: '0 0 60px' }}
                        onClick={() => handleCellClick(quarto, dataAtual)}
                    >
                    </div>
                );
            }
        }
        return celulas;
    };

    return (
        <div className="w-100">
            {/* Header e Filtros */}
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h1>Mapa de Reservas</h1>
                <div className="d-flex align-items-center">
                     <div className="form-group mb-0 mr-3">
                        <label className="sr-only">Data Início</label>
                        <input type="date" className="form-control form-control-sm" value={filtroDataInicio} onChange={(e) => setFiltroDataInicio(e.target.value)} />
                    </div>
                    <div className="form-group mb-0 mr-3">
                        <label className="sr-only">Data Fim</label>
                        <input type="date" className="form-control form-control-sm" value={filtroDataFim} onChange={(e) => setFiltroDataFim(e.target.value)} />
                    </div>
                    <button type="button" className="btn btn-primary btn-sm" onClick={fetchMapa}>
                        <i className="fas fa-search"></i> Atualizar
                    </button>
                </div>
            </div>

            {loading && <div className="text-center p-4"><i className="fas fa-spinner fa-spin"></i> Carregando...</div>}

            {!loading && dadosMapa && (
                <div className="card">
                    <div className="card-body p-0">
                        <div className="mapa-container">
                            <div className="mapa-header" style={{ minWidth: 'fit-content' }}>
                                <div className="row no-gutters flex-nowrap" style={{ width: 'fit-content', minWidth: '100%' }}>
                                    <div className="col-2 header-quartos sticky-left" style={{minWidth: '150px', position: 'sticky', left: 0, zIndex: 102}}>
                                        <div className="header-cell">Quartos / Mês</div>
                                    </div>
                                    <div className="col-10 d-flex">
                                        {dadosMapa.datas.map(data => {
                                            const dataObj = new Date(data + 'T00:00:00');
                                            const dia = dataObj.toLocaleDateString('pt-BR', { weekday: 'short' }).toUpperCase();
                                            const dataFmt = dataObj.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
                                            const ocupacao = dadosMapa.ocupacao[data] || { percentual: 0 };
                                            return (
                                                <div key={data} className="data-header" style={{minWidth: '60px', width: '60px', flex: '0 0 60px'}}>
                                                    <div className="dia">{dia}</div>
                                                    <div className="data">{dataFmt}</div>
                                                    <div className="ocupacao">{ocupacao.percentual}%</div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>

                            <div id="mapa-body">
                                {/* Iteração direta nos quartos (lista plana) em vez de categorias */}
                                {dadosMapa.quartos.map(quarto => (
                                    <div key={quarto.id} className="quarto-row" style={{ minWidth: 'fit-content' }}>
                                        <div className="row no-gutters flex-nowrap" style={{ width: 'fit-content', minWidth: '100%' }}>
                                            <div className="col-2 quarto-header sticky-left" style={{minWidth: '150px', position: 'sticky', left: 0, zIndex: 101}}>
                                                {quarto.nome}
                                            </div>
                                            <div className="col-10 d-flex">
                                                {renderLinhaQuarto(quarto)}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}
            
            <div className="card mt-3">
                <div className="card-body">
                    <div className="d-flex flex-wrap align-items-center">
                        <span className="mr-3"><strong>Legenda:</strong></span>
                        <span className="badge badge-warning mr-2">pré-reservado</span>
                        <span className="badge badge-primary mr-2">reservado</span>
                        <span className="badge badge-danger mr-2">hospedado</span>
                        <span className="badge badge-info mr-2">finalizado</span>
                        <span className="badge badge-dark mr-2">bloqueado</span>
                        <span className="badge badge-noshow mr-2">no show</span>
                    </div>
                </div>
            </div>

            {/* Modal Ações Iniciais */}
            <SimpleModal show={showModalAcoes} onClose={() => setShowModalAcoes(false)} title="O que deseja fazer?" size="sm">
                <div className="d-grid gap-2">
                    <button type="button" className="btn btn-primary btn-block mb-2" onClick={abrirFormularioReserva}>
                        <i className="fas fa-calendar-plus"></i> Fazer uma reserva
                    </button>
                    <button type="button" className="btn btn-dark btn-block" onClick={abrirFormularioBloqueio}>
                        <i className="fas fa-ban"></i> Bloquear datas
                    </button>
                </div>
            </SimpleModal>

            {/* MODAL DETALHES COM FINANCEIRO */}
            <SimpleModal show={showModalDetalhes} onClose={() => setShowModalDetalhes(false)} title="Detalhes da Reserva">
                {reservaDetalhes && (
                    <div>
                        <div className="alert alert-info d-flex justify-content-between align-items-center">
                            <strong>Reserva #{reservaDetalhes.id}</strong>
                            <span className={`badge situacao-${reservaDetalhes.situacao} text-uppercase px-3 py-2`}>
                                {reservaDetalhes.situacao}
                            </span>
                        </div>
                        
                        <div className="row mb-3">
                            <div className="col-md-12">
                                <label className="text-muted mb-0">Hóspede</label>
                                <h5 className="font-weight-bold">{reservaDetalhes.hospede_nome}</h5>
                            </div>
                        </div>

                        <div className="row mb-3">
                            <div className="col-6">
                                <label className="text-muted mb-0">Check-in</label>
                                <div><i className="fas fa-calendar-check mr-1"></i> {formatDate(reservaDetalhes.data_checkin)}</div>
                            </div>
                            <div className="col-6">
                                <label className="text-muted mb-0">Check-out</label>
                                <div><i className="fas fa-calendar-times mr-1"></i> {formatDate(reservaDetalhes.data_checkout)}</div>
                            </div>
                        </div>

                        {reservaDetalhes.situacao !== 'bloqueado' && (
                            <div className="bg-light p-3 rounded mb-3 border">
                                <div className="row">
                                    <div className="col-6 mb-2">
                                        <label className="text-muted mb-0 small">Nº de Diárias</label>
                                        <div className="font-weight-bold">
                                            {financeiroDetalhes ? financeiroDetalhes.num_diarias : <i className="fas fa-spinner fa-spin"></i>}
                                        </div>
                                    </div>
                                    <div className="col-6 mb-2">
                                        <label className="text-muted mb-0 small">Total Geral</label>
                                        <div className="text-primary font-weight-bold">
                                            {financeiroDetalhes ? formatMoney(financeiroDetalhes.total_geral) : <i className="fas fa-spinner fa-spin"></i>}
                                        </div>
                                    </div>
                                    <div className="col-6">
                                        <label className="text-muted mb-0 small">Recebido</label>
                                        <div className="text-success font-weight-bold">
                                            {financeiroDetalhes ? formatMoney(financeiroDetalhes.total_recebido) : <i className="fas fa-spinner fa-spin"></i>}
                                        </div>
                                    </div>
                                    <div className="col-6">
                                        <label className="text-muted mb-0 small">Falta Receber</label>
                                        <div className="text-danger font-weight-bold h5 mb-0">
                                            {financeiroDetalhes ? formatMoney(financeiroDetalhes.falta_lancar) : <i className="fas fa-spinner fa-spin"></i>}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="row mb-3">
                            <div className="col-12">
                                <label className="text-muted mb-0">Hóspedes</label>
                                <div>{reservaDetalhes.n_adultos} Adulto(s) {reservaDetalhes.n_criancas > 0 ? `, ${reservaDetalhes.n_criancas} criança(s)` : ""}</div>
                            </div>
                        </div>
                        
                        {/* Rodapé com botão de Editar */}
                        <div className="border-top pt-3 mt-2 text-right">
                             <button type="button" className="btn btn-secondary mr-2" onClick={() => setShowModalDetalhes(false)}>
                                Fechar
                            </button>
                            <a href={`/reserva/${reservaDetalhes.id}/edit`} className="btn btn-primary">
                                <i className="fas fa-edit"></i> Ir para reserva
                            </a>
                        </div>
                    </div>
                )}
            </SimpleModal>

            {/* Modal de Bloqueio com React-Select */}
            <SimpleModal show={showModalBloqueio} onClose={() => setShowModalBloqueio(false)} title="Bloquear Datas">
                <form onSubmit={handleSalvarBloqueio}>
                    <div className="form-group">
                        <label>Quartos para bloquear</label>
                        <Select
                            isMulti
                            options={getOptionsQuartos()}
                            value={formBloqueio.selectedOptions}
                            onChange={(selected) => {
                                setFormBloqueio({ 
                                    ...formBloqueio, 
                                    selectedOptions: selected,
                                    quarto_ids: selected ? selected.map(item => item.value) : []
                                });
                            }}
                            placeholder="Selecione os quartos..."
                            noOptionsMessage={() => "Nenhum quarto encontrado"}
                        />
                        <small className="text-muted">Você pode selecionar múltiplos quartos.</small>
                    </div>

                    <div className="row">
                        <div className="col-md-6 form-group">
                            <label>Data Início</label>
                            <input 
                                type="date" 
                                className="form-control" 
                                required 
                                value={formBloqueio.data_checkin} 
                                onChange={e => setFormBloqueio({...formBloqueio, data_checkin: e.target.value})} 
                            />
                        </div>
                        <div className="col-md-6 form-group">
                            <label>Data Fim</label>
                            <input 
                                type="date" 
                                className="form-control" 
                                required 
                                value={formBloqueio.data_checkout} 
                                onChange={e => setFormBloqueio({...formBloqueio, data_checkout: e.target.value})} 
                            />
                        </div>
                    </div>

                    <div className="form-group">
                        <label>Observação</label>
                        <textarea 
                            className="form-control" 
                            rows="2"
                            value={formBloqueio.observacoes}
                            onChange={e => setFormBloqueio({...formBloqueio, observacoes: e.target.value})}
                        ></textarea>
                    </div>

                    <div className="modal-footer px-0 pb-0">
                        <button type="button" className="btn btn-secondary" onClick={() => setShowModalBloqueio(false)}>Cancelar</button>
                        <button type="submit" className="btn btn-dark">Confirmar Bloqueio(s)</button>
                    </div>
                </form>
            </SimpleModal>

            {/* Modal Reserva */}
             <SimpleModal show={showModalReserva} onClose={() => setShowModalReserva(false)} title="Nova Reserva">
                <form onSubmit={handleSalvarReserva}>
                    <div className="form-group row">
                        <label className="col-sm-12">Hóspede</label>
                        <div className="col-sm-12">
                            <select className="form-control" required value={formReserva.hospede_id} onChange={e => setFormReserva({...formReserva, hospede_id: e.target.value})}>
                                <option value="">Selecione um hóspede</option>
                                {hospedes.map(h => (h.nome !== 'Bloqueado' && (<option key={h.id} value={h.id}>{h.nome}</option>)))}
                            </select>
                        </div>
                    </div>
                    <div className="form-group"><label>Quarto: <strong>{celulaSelecionada?.quartoNome}</strong></label></div>
                    <div className="row">
                        <div className="col-md-6 form-group"><label>Check-in</label><input type="date" className="form-control" required value={formReserva.data_checkin} onChange={e => setFormReserva({...formReserva, data_checkin: e.target.value})} /></div>
                        <div className="col-md-6 form-group"><label>Check-out</label><input type="date" className="form-control" required value={formReserva.data_checkout} onChange={e => setFormReserva({...formReserva, data_checkout: e.target.value})} /></div>
                    </div>
                    <div className="form-group">
                        <label>Situação</label>
                        <select className="form-control" required value={formReserva.situacao} onChange={e => setFormReserva({...formReserva, situacao: e.target.value})}>
                            <option value="pre-reserva">Pré-reserva</option>
                            <option value="reserva">Reserva</option>
                        </select>
                    </div>
                    <div className="row">
                        <div className="col-md-6 form-group"><label>Nº Adultos</label><input type="number" className="form-control" min="1" required value={formReserva.n_adultos} onChange={e => setFormReserva({...formReserva, n_adultos: e.target.value})} /></div>
                        <div className="col-md-6 form-group"><label>Nº Crianças</label><input type="number" className="form-control" min="0" value={formReserva.n_criancas} onChange={e => setFormReserva({...formReserva, n_criancas: e.target.value})} /></div>
                    </div>
                    <div className="form-group"><label>Valor da Diária</label><input type="text" className="form-control" required placeholder="0,00" value={formReserva.valor_diaria} onChange={handleValorChange} /></div>
                    <div className="modal-footer px-0 pb-0">
                        <button type="button" className="btn btn-secondary" onClick={() => setShowModalReserva(false)}>Cancelar</button>
                        <button type="submit" className="btn btn-primary">Criar Reserva</button>
                    </div>
                </form>
            </SimpleModal>
        </div>
    );
}

const rootElement = document.getElementById('react-mapa-reservas-root');
if (rootElement) {
    const root = ReactDOM.createRoot(rootElement);
    let hospedes = [];
    try {
        hospedes = JSON.parse(rootElement.dataset.hospedes || '[]');
    } catch (e) {
        console.warn("Erro ao ler JSON de hóspedes. Usando array vazio.", e);
    }
    const dataInicio = rootElement.dataset.dataInicio;
    const dataFim = rootElement.dataset.dataFim;
    root.render(
        <React.StrictMode>
            <MapaReservas 
                hospedesIniciais={hospedes} 
                dataInicioInicial={dataInicio}
                dataFimInicial={dataFim}
            />
        </React.StrictMode>
    );
}