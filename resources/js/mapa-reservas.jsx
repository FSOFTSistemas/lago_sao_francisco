import './bootstrap';
import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import axios from 'axios';
import Select from 'react-select';
import Swal from 'sweetalert2';

// --- COMPONENTE MODAL SIMPLES ---
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

// --- FUNÇÕES AUXILIARES ---
const formatMoney = (value) => parseFloat(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
};

// --- COMPONENTE PRINCIPAL ---
export default function MapaReservas({ hospedesIniciais, dataInicioInicial, dataFimInicial }) {
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

    // Forms
    const [formReserva, setFormReserva] = useState({
        hospede_id: '', 
        data_checkin: '', 
        data_checkout: '', 
        situacao: 'pre-reserva',
        n_adultos: 1, 
        n_criancas: 0, 
        valor_diaria: '',
        nomes_hospedes_secundarios: '' 
    });

    const [formBloqueio, setFormBloqueio] = useState({
        quarto_ids: [], selectedOptions: [], data_checkin: '', data_checkout: '', observacoes: 'Manutenção / Bloqueio'
    });

    useEffect(() => { fetchMapa(); }, []);

    // --- BUSCA DADOS ---
    const fetchMapa = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/mapa/dados', {
                params: { data_inicio: filtroDataInicio, data_fim: filtroDataFim, _: new Date().getTime() }
            });
            if (response.data.success) {
                const dados = response.data;
                if (dados.quartos && Array.isArray(dados.quartos)) {
                    dados.quartos.sort((a, b) => {
                        const posA = parseInt(a.posicao, 10) || 0;
                        const posB = parseInt(b.posicao, 10) || 0;
                        if (posA === posB) return a.nome.localeCompare(b.nome, undefined, { numeric: true });
                        return posA - posB;
                    });
                }
                setDadosMapa(dados);
            } else { 
                Swal.fire('Erro', response.data.message, 'error');
            }
        } catch (error) { 
            console.error(error); 
            Swal.fire('Erro', 'Erro de conexão com o servidor.', 'error');
        } finally { 
            setLoading(false); 
        }
    };

    const getOptionsQuartos = () => {
        if (!dadosMapa || !dadosMapa.quartos) return [];
        return dadosMapa.quartos.map(q => ({ value: q.id, label: `${q.nome} (${q.categoria_nome})` }));
    };

    const podeFazerCheckin = (reserva) => {
        if (!reserva || reserva.situacao !== 'reserva') return false;
        const hoje = new Date().toISOString().split('T')[0];
        return reserva.data_checkin <= hoje;
    };

    // --- RENDERIZAÇÃO VISUAL ---
// --- RENDERIZAÇÃO VISUAL ---
    const renderLinhaQuarto = (quarto) => {
        const celulas = [];
        const datas = dadosMapa.datas;
        const totalDias = datas.length;
        const larguraDia = 60; 

        // Função auxiliar para comparar datas (apenas YYYY-MM-DD)
        const checkData = (d1, d2) => (d1 && d2 && d1.substring(0, 10) === d2.substring(0, 10));

        for (let i = 0; i < totalDias; i++) {
            const dataAtual = datas[i];

            // 1. Verifica se há uma reserva começando EXATAMENTE hoje
            let reservaInicio = quarto.reservas.find(r => checkData(r.data_checkin, dataAtual));

            // --- CORREÇÃO DO BUG (Reservas contínuas) ---
            // Se estamos na primeira coluna do mapa (i === 0) e não encontramos um check-in hoje,
            // procuramos por uma reserva que começou ANTES e termina DEPOIS de hoje.
            if (i === 0 && !reservaInicio) {
                reservaInicio = quarto.reservas.find(r => 
                    r.data_checkin < dataAtual && 
                    r.data_checkout > dataAtual
                );
            }
            // ---------------------------------------------

            // Verifica se alguma outra reserva termina hoje (para ajustar margem visual)
            const reservaFim = quarto.reservas.find(r => checkData(r.data_checkout, dataAtual));

            if (reservaInicio) {
                // Calcula quantos dias visíveis essa reserva vai ocupar
                let slotsOcupados = 0;
                for (let j = i; j < totalDias; j++) {
                    const dFutura = datas[j];
                    // Para o contador se chegarmos na data de checkout
                    if (checkData(dFutura, reservaInicio.data_checkout)) break; 
                    slotsOcupados++;
                }
                
                // Correção de segurança: Se a reserva termina hoje mas caiu aqui, garante largura mínima
                if (slotsOcupados === 0 && checkData(reservaInicio.data_checkout, dataAtual)) {
                    slotsOcupados = 1;
                }

                // Se a reserva vai além do final do calendário visível, ela ocupa tudo até o fim
                if (slotsOcupados === 0 && reservaInicio.data_checkout > datas[totalDias - 1]) {
                    slotsOcupados = totalDias - i;
                }

                // Fallback final para evitar largura 0
                if (slotsOcupados === 0) slotsOcupados = 1;

                const larguraGrid = slotsOcupados * larguraDia;
                const larguraBarra = larguraGrid + 25; // +25px para dar o efeito de continuidade visual
                
                // Se houver um checkout no mesmo dia (troca de hóspede), empurra a barra nova para direita
                const margemEsquerda = reservaFim ? 30 : 0;
                
                celulas.push(
                    <div 
                        key={`${quarto.id}-${dataAtual}-inicio`}
                        className="quarto-cell"
                        style={{ 
                            minWidth: `${larguraGrid}px`, 
                            width: `${larguraGrid}px`, 
                            flex: `0 0 ${larguraGrid}px`,
                            position: 'relative',
                            zIndex: 10
                        }}
                    >
                        <div 
                            className={`reserva-block situacao-${reservaInicio.situacao}`}
                            style={{ 
                                width: `${larguraBarra}px`, 
                                marginLeft: `${margemEsquerda}px`, 
                                borderRadius: '4px',
                                zIndex: 20
                            }}
                            onClick={(e) => { e.stopPropagation(); handleCellClick(quarto, dataAtual, reservaInicio); }}
                            title={`Reserva: ${reservaInicio.hospede_nome}`}
                        >
                            <span style={{ paddingLeft: '10px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                {reservaInicio.hospede_nome}
                            </span>
                        </div>
                    </div>
                );

                // Avança o loop principal (i) para pular os dias que essa reserva já ocupou visualmente
                if (slotsOcupados > 0) i += (slotsOcupados - 1);
                continue;
            }

            // Renderiza célula vazia se não houver reserva
            celulas.push(
                <div 
                    key={`${quarto.id}-${dataAtual}-vazio`}
                    className="quarto-cell"
                    style={{ minWidth: `${larguraDia}px`, width: `${larguraDia}px`, flex: `0 0 ${larguraDia}px` }}
                    onClick={() => handleCellClick(quarto, dataAtual)}
                >
                </div>
            );
        }
        return celulas;
    };

    // --- ACTIONS ---
    const handleCellClick = async (quarto, data, reserva = null) => {
        if (reserva) {
            setReservaDetalhes(reserva);
            setFinanceiroDetalhes(null);
            setShowModalDetalhes(true);
            try {
                const response = await axios.get(`/transacoes/resumo/${reserva.id}`);
                if (response.data.success) setFinanceiroDetalhes(response.data.resumo);
            } catch (error) { console.error(error); }
        } else {
            setCelulaSelecionada({ quartoId: quarto.id, data: data, quartoNome: quarto.nome });
            setShowModalAcoes(true);
        }
    };

    const handleRealizarCheckin = async () => {
        if (!reservaDetalhes) return;
        
        const result = await Swal.fire({
            title: 'Confirmar Check-in',
            text: `Deseja realizar o check-in para ${reservaDetalhes.hospede_nome}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, confirmar!',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        setLoadingAction(true);
        try {
            const response = await axios.put(`/reserva/${reservaDetalhes.id}/hospedar`);
            if (response.data.success) {
                Swal.fire('Sucesso!', 'Check-in realizado com sucesso.', 'success');
                setShowModalDetalhes(false); 
                fetchMapa();
            } else { 
                Swal.fire('Atenção', response.data.message || 'Erro ao realizar check-in.', 'warning');
            }
        } catch (error) { 
            const msg = error.response?.data?.message || error.message;
            Swal.fire('Erro', `Não foi possível processar: ${msg}`, 'error');
        } finally { 
            setLoadingAction(false); 
        }
    };

    const handleSalvarReserva = async (e) => {
        e.preventDefault();
        
        const quarto = dadosMapa.quartos.find(q => q.id === celulaSelecionada.quartoId);
        let capacidadeMaxima = 999; 

        if (quarto) {
            if (quarto.ocupantes) {
                capacidadeMaxima = parseInt(quarto.ocupantes);
            } else if (quarto.categoria && quarto.categoria.ocupantes) {
                capacidadeMaxima = parseInt(quarto.categoria.ocupantes);
            } else if (quarto.categoria_ocupantes) {
                capacidadeMaxima = parseInt(quarto.categoria_ocupantes);
            }
        }

        const nAdultos = parseInt(formReserva.n_adultos || 0);
        const nCriancas = parseInt(formReserva.n_criancas || 0);
        const totalPessoas = nAdultos + nCriancas;

        if (totalPessoas > (capacidadeMaxima + 10)) {
            Swal.fire({
                icon: 'warning',
                title: 'Capacidade Excedida',
                html: `O quarto <b>${quarto?.nome}</b> comporta no máximo <b>${capacidadeMaxima}</b> pessoas.<br>Você selecionou ${totalPessoas} (Adultos + Crianças).`
            });
            return; 
        }

        const vDiaria = parseFloat(formReserva.valor_diaria.replace(/\./g, '').replace(',', '.')) || 0;
        let start = new Date(formReserva.data_checkin);
        let end = new Date(formReserva.data_checkout);
        
        end.setDate(end.getDate() + 1);
        if (start.getTime() >= end.getTime()) {
             end = new Date(start);
             end.setDate(end.getDate() + 1);
        }
        const checkoutString = end.toISOString().split('T')[0];
        const diff = Math.ceil(Math.abs(end - start) / (864e5));

        // Validação Hóspede
        if (!formReserva.hospede_id) {
            Swal.fire('Atenção', 'Selecione um hóspede.', 'warning');
            return;
        }

        try {
            const res = await axios.post('/mapa/criar-reserva', { 
                ...formReserva, 
                data_checkout: checkoutString,
                quarto_id: celulaSelecionada.quartoId, 
                valor_diaria: vDiaria, 
                valor_total: vDiaria * diff, 
                tipo: 'reserva' 
            });
            if (res.data.success) { 
                Swal.fire('Sucesso', 'Reserva criada com sucesso!', 'success');
                setShowModalReserva(false); 
                fetchMapa(); 
            } else { 
                Swal.fire('Erro', res.data.message, 'error');
            }
        } catch (e) { 
            Swal.fire('Erro', 'Ocorreu um erro ao tentar salvar.', 'error');
        }
    };

    const handleSalvarBloqueio = async (e) => {
        e.preventDefault();
        if (formBloqueio.quarto_ids.length === 0) return Swal.fire('Atenção', 'Selecione pelo menos um quarto.', 'warning');
        
        let end = new Date(formBloqueio.data_checkout);
        end.setDate(end.getDate() + 1); 
        const checkoutString = end.toISOString().split('T')[0];

        const requests = formBloqueio.quarto_ids.map(qid => axios.post('/mapa/criar-reserva', {
            quarto_id: qid, 
            data_checkin: formBloqueio.data_checkin, 
            data_checkout: checkoutString,
            observacoes: formBloqueio.observacoes, situacao: 'bloqueado', n_adultos: 1, n_criancas: 0, valor_diaria: 0, valor_total: 0, tipo: 'bloqueio'
        }));
        try { 
            await Promise.all(requests); 
            Swal.fire('Sucesso', 'Bloqueio(s) criado(s) com sucesso!', 'success');
            setShowModalBloqueio(false); 
            fetchMapa(); 
        } catch (e) { 
            Swal.fire('Erro', 'Ocorreu um erro ao criar o bloqueio.', 'error');
        }
    };

    const abrirFormularioReserva = () => {
        setShowModalAcoes(false);
        const checkin = celulaSelecionada.data;
        // Reseta o form, incluindo os hóspedes secundários
        setFormReserva({ 
            ...formReserva, 
            hospede_id: '', 
            data_checkin: checkin, 
            data_checkout: checkin, 
            valor_diaria: '', 
            n_adultos: 1,
            n_criancas: 0,
            nomes_hospedes_secundarios: '' 
        });
        setShowModalReserva(true);
    };

    const abrirFormularioBloqueio = () => {
        setShowModalAcoes(false);
        const checkin = celulaSelecionada.data;
        const opt = getOptionsQuartos().find(op => op.value === celulaSelecionada.quartoId);
        setFormBloqueio({ quarto_ids: [celulaSelecionada.quartoId], selectedOptions: opt ? [opt] : [], data_checkin: checkin, data_checkout: checkin, observacoes: 'Bloqueio' });
        setShowModalBloqueio(true);
    };

    return (
        <div className="w-100">
            {/* CSS */}
            <style>{`
                .quarto-cell {
                    border-right: 1px solid #e0e0e0; 
                    padding: 0 !important;
                    box-sizing: border-box;
                    position: relative;
                }
                .reserva-block {
                    height: 80%; top: 10%;
                    display: flex; align-items: center; justify-content: flex-start;
                    color: #fff; font-weight: bold; font-size: 11px; cursor: pointer;
                    overflow: hidden; white-space: nowrap; position: absolute;
                    box-shadow: 2px 2px 4px rgba(0,0,0,0.2);
                }
                .reserva-block:hover { opacity: 0.9; z-index: 100 !important; }
                .situacao-pre-reserva { background-color: #ffc107; color: #000; }
                .situacao-reserva { background-color: #007bff; }
                .situacao-hospedado { background-color: #dc3545; }
                .situacao-finalizada { background-color: #17a2b8; }
                .situacao-bloqueado { background-color: #343a40; }
                .situacao-noshow { background-color: #e83e8c; }
            `}</style>

            {/* HEADER */}
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h1>Mapa de Reservas</h1>
                <div className="d-flex align-items-center">
                    <input type="date" className="form-control form-control-sm mr-2" value={filtroDataInicio} onChange={e => setFiltroDataInicio(e.target.value)} />
                    <input type="date" className="form-control form-control-sm mr-2" value={filtroDataFim} onChange={e => setFiltroDataFim(e.target.value)} />
                    <button className="btn btn-primary btn-sm" onClick={fetchMapa}><i className="fas fa-search"></i> Atualizar</button>
                </div>
            </div>

            {loading && <div className="text-center p-4"><i className="fas fa-spinner fa-spin"></i> Carregando...</div>}

            {/* MAPA */}
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
                                            const d = new Date(data + 'T00:00:00');
                                            return (
                                                <div key={data} className="data-header" style={{minWidth: '60px', width: '60px', flex: '0 0 60px'}}>
                                                    <div className="dia">{d.toLocaleDateString('pt-BR', { weekday: 'short' }).toUpperCase()}</div>
                                                    <div className="data">{d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })}</div>
                                                    <div className="ocupacao">{dadosMapa.ocupacao[data]?.percentual || 0}%</div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                            <div id="mapa-body">
                                {dadosMapa.quartos.map(q => (
                                    <div key={q.id} className="quarto-row" style={{ minWidth: 'fit-content' }}>
                                        <div className="row no-gutters flex-nowrap" style={{ width: 'fit-content', minWidth: '100%' }}>
                                            <div className="col-2 quarto-header sticky-left" style={{minWidth: '150px', position: 'sticky', left: 0, zIndex: 101}}>{q.nome}</div>
                                            <div className="col-10 d-flex">{renderLinhaQuarto(q)}</div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}
            
            {/* MODAL DETALHES */}
            <SimpleModal show={showModalDetalhes} onClose={() => setShowModalDetalhes(false)} title="Detalhes da Reserva">
                {reservaDetalhes && (
                    <div>
                        <div className="alert alert-info d-flex justify-content-between align-items-center">
                            <strong>Reserva #{reservaDetalhes.id}</strong>
                            <span className={`badge situacao-${reservaDetalhes.situacao} text-uppercase px-3 py-2`}>{reservaDetalhes.situacao}</span>
                        </div>
                        <h5 className="font-weight-bold mb-3">{reservaDetalhes.hospede_nome}</h5>
                        <div className="row mb-3">
                            <div className="col-6"><small className="text-muted">Check-in</small><div>{formatDate(reservaDetalhes.data_checkin)}</div></div>
                            <div className="col-6"><small className="text-muted">Check-out</small><div>{formatDate(reservaDetalhes.data_checkout)}</div></div>
                        </div>

                        {/* HÓSPEDES */}
                        <div className="row mb-3">
                             <div className="col-12">
                                 <label className="text-muted mb-0 small">Hóspedes</label>
                                 <div className="font-weight-bold">
                                     {reservaDetalhes.n_adultos} Adulto(s)
                                     {reservaDetalhes.n_criancas > 0 ? ` e ${reservaDetalhes.n_criancas} Criança(s)` : ''}
                                 </div>
                             </div>
                        </div>

                        {/* HÓSPEDES SECUNDÁRIOS - NOVO */}
                        {reservaDetalhes.nomes_hospedes_secundarios && (
                            <div className="row mb-3">
                                <div className="col-12">
                                    <label className="text-muted mb-0 small">Hóspedes Secundários</label>
                                    <div className="font-weight-bold text-dark">
                                        {reservaDetalhes.nomes_hospedes_secundarios}
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* OBSERVAÇÕES */}
                        {reservaDetalhes.observacoes && (
                            <div className="row mb-3">
                                <div className="col-12">
                                    <label className="text-muted mb-0 small">Observações</label>
                                    <div className="p-2 rounded border bg-white" style={{ fontSize: '0.9rem', fontStyle: 'italic', whiteSpace: 'pre-wrap' }}>
                                        {reservaDetalhes.observacoes}
                                    </div>
                                </div>
                            </div>
                        )}

                        {reservaDetalhes.situacao !== 'bloqueado' && (
                            <div className="bg-light p-3 rounded mb-3 border">
                                <div className="row">
                                    <div className="col-6 mb-2"><label className="text-muted mb-0 small">Nº Diárias</label><div className="font-weight-bold">{financeiroDetalhes ? financeiroDetalhes.num_diarias : '...'}</div></div>
                                    <div className="col-6 mb-2"><label className="text-muted mb-0 small">Total Geral</label><div className="text-primary font-weight-bold">{financeiroDetalhes ? formatMoney(financeiroDetalhes.total_geral) : '...'}</div></div>
                                    <div className="col-6"><label className="text-muted mb-0 small">Recebido</label><div className="text-success font-weight-bold">{financeiroDetalhes ? formatMoney(financeiroDetalhes.total_recebido) : '...'}</div></div>
                                    <div className="col-6"><label className="text-muted mb-0 small">Falta Receber</label><div className="text-danger font-weight-bold h5 mb-0">{financeiroDetalhes ? formatMoney(financeiroDetalhes.falta_lancar) : '...'}</div></div>
                                </div>
                            </div>
                        )}
                        <div className="border-top pt-3 mt-2 text-right">
                             {podeFazerCheckin(reservaDetalhes) && (
                                <button type="button" className="btn btn-success mr-2" onClick={handleRealizarCheckin} disabled={loadingAction}>
                                    {loadingAction ? <i className="fas fa-spinner fa-spin"></i> : <i className="fas fa-check"></i>} Fazer Check-in
                                </button>
                             )}
                             <button className="btn btn-secondary mr-2" onClick={() => setShowModalDetalhes(false)}>Fechar</button>
                             <a href={`/reserva/${reservaDetalhes.id}/edit`} className="btn btn-primary"><i className="fas fa-edit"></i> Editar</a>
                        </div>
                    </div>
                )}
            </SimpleModal>

            <SimpleModal show={showModalAcoes} onClose={() => setShowModalAcoes(false)} title="Ação" size="sm">
                <button className="btn btn-primary btn-block mb-2" onClick={abrirFormularioReserva}>Reserva</button>
                <button className="btn btn-dark btn-block" onClick={abrirFormularioBloqueio}>Bloqueio</button>
            </SimpleModal>
            
            <SimpleModal show={showModalBloqueio} onClose={() => setShowModalBloqueio(false)} title="Bloquear">
                <form onSubmit={handleSalvarBloqueio}>
                     <div className="form-group"><label>Quartos</label><Select isMulti options={getOptionsQuartos()} value={formBloqueio.selectedOptions} onChange={s => setFormBloqueio({...formBloqueio, selectedOptions: s, quarto_ids: s?.map(i=>i.value)||[]})} /></div>
                     <div className="row"><div className="col-6"><input type="date" className="form-control" value={formBloqueio.data_checkin} onChange={e=>setFormBloqueio({...formBloqueio, data_checkin: e.target.value})}/></div><div className="col-6"><input type="date" className="form-control" value={formBloqueio.data_checkout} onChange={e=>setFormBloqueio({...formBloqueio, data_checkout: e.target.value})}/></div></div>
                     <textarea className="form-control mt-2" value={formBloqueio.observacoes} onChange={e=>setFormBloqueio({...formBloqueio, observacoes: e.target.value})}></textarea>
                     <div className="text-right mt-3"><button type="submit" className="btn btn-dark">Salvar</button></div>
                </form>
            </SimpleModal>

            <SimpleModal show={showModalReserva} onClose={() => setShowModalReserva(false)} title="Nova Reserva">
                <form onSubmit={handleSalvarReserva}>
                    <select className="form-control mb-2" value={formReserva.hospede_id} onChange={e=>setFormReserva({...formReserva, hospede_id: e.target.value})}>
                        <option value="">Hóspede</option>{hospedes.map(h => h.nome!=='Bloqueado' && <option key={h.id} value={h.id}>{h.nome}</option>)}
                    </select>
                    <div className="row mb-2"><div className="col-6"><input type="date" className="form-control" value={formReserva.data_checkin} onChange={e=>setFormReserva({...formReserva, data_checkin: e.target.value})}/></div><div className="col-6"><input type="date" className="form-control" value={formReserva.data_checkout} onChange={e=>setFormReserva({...formReserva, data_checkout: e.target.value})}/></div></div>
                    <select className="form-control mb-2" value={formReserva.situacao} onChange={e=>setFormReserva({...formReserva, situacao: e.target.value})}><option value="pre-reserva">Pré-reserva</option><option value="reserva">Reserva</option></select>
                    
                    <div className="row mb-2">
                        <div className="col-6"><label className="small mb-1">Adultos</label><input type="number" className="form-control" min="1" value={formReserva.n_adultos} onChange={e => setFormReserva({...formReserva, n_adultos: e.target.value})} /></div>
                        <div className="col-6"><label className="small mb-1">Crianças</label><input type="number" className="form-control" min="0" value={formReserva.n_criancas} onChange={e => setFormReserva({...formReserva, n_criancas: e.target.value})} /></div>
                    </div>

                    {/* NOVO CAMPO: Hóspedes Secundários (Textarea) */}
                    <div className="form-group">
                        <label className="small mb-1">Hóspedes Secundários (Nomes)</label>
                        <textarea 
                            className="form-control" 
                            rows="2" 
                            placeholder="Separe os nomes por vírgula..."
                            value={formReserva.nomes_hospedes_secundarios} 
                            onChange={e => setFormReserva({...formReserva, nomes_hospedes_secundarios: e.target.value})}
                        ></textarea>
                    </div>
                    
                    <input type="text" className="form-control" placeholder="Valor" value={formReserva.valor_diaria} onChange={e=>setFormReserva({...formReserva, valor_diaria: e.target.value.replace(/\D/g, "").replace(/(\d)(\d{2})$/, "$1,$2")})} />
                    <div className="text-right mt-3"><button type="submit" className="btn btn-primary">Salvar</button></div>
                </form>
            </SimpleModal>
        </div>
    );
}

const rootElement = document.getElementById('react-mapa-reservas-root');
if (rootElement) {
    if (!rootElement._reactRootContainer) {
        const root = ReactDOM.createRoot(rootElement);
        let hospedes = [];
        try { hospedes = JSON.parse(rootElement.dataset.hospedes || '[]'); } catch (e) { }
        const dataInicio = rootElement.dataset.dataInicio;
        const dataFim = rootElement.dataset.dataFim;
        root.render(<React.StrictMode><MapaReservas hospedesIniciais={hospedes} dataInicioInicial={dataInicio} dataFimInicial={dataFim} /></React.StrictMode>);
        rootElement._reactRootContainer = root;
    }
}