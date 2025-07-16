@extends('adminlte::page')

@section('title', 'Mapa de Reservas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Mapa de Reservas</h1>
        <div class="d-flex align-items-center">
            <div class="form-group mb-0 mr-3">
                <label for="data_inicio" class="sr-only">Data Início</label>
                <input type="date" id="data_inicio" class="form-control form-control-sm" value="{{ $dataInicio }}">
            </div>
            <div class="form-group mb-0 mr-3">
                <label for="data_fim" class="sr-only">Data Fim</label>
                <input type="date" id="data_fim" class="form-control form-control-sm" value="{{ $dataFim }}">
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="carregarMapa()">
                <i class="fas fa-search"></i> Atualizar
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="loading" class="text-center p-4">
                <i class="fas fa-spinner fa-spin"></i> Carregando mapa...
            </div>
            
            <div id="mapa-container" style="display: none;">
                <!-- Cabeçalho com datas -->
                <div class="mapa-header">
                    <div class="row no-gutters">
                        <div class="col-2 header-quartos">
                            <div class="header-cell">Quartos / Mês</div>
                        </div>
                        <div class="col-10">
                            <div class="row no-gutters" id="header-datas">
                                <!-- Datas serão inseridas aqui via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Corpo do mapa -->
                <div id="mapa-body">
                    <!-- Categorias e quartos serão inseridos aqui via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Legenda -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="mr-3"><strong>Legenda:</strong></span>
                        <span class="badge badge-warning mr-2">pré-reservado</span>
                        <span class="badge badge-info mr-2">reservado</span>
                        <span class="badge badge-success mr-2">hospedado</span>
                        {{-- <span class="badge badge-secondary mr-2">em limpeza</span> --}}
                        <span class="badge badge-dark mr-2">finalizado</span>
                        <span class="badge badge-primary mr-2">bloqueado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ações -->
    <div class="modal fade" id="modalAcoes" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">O que deseja fazer?</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-block" onclick="abrirModalReserva()">
                            <i class="fas fa-calendar-plus"></i> Fazer uma reserva
                        </button>
                        <button type="button" class="btn btn-warning btn-block" onclick="criarBloqueio()">
                            <i class="fas fa-ban"></i> Bloquear data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para criar reserva -->
    <div class="modal fade" id="modalReserva" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formReserva">
                    <div class="modal-body">

                        <div class="form-group row">
                            <label for="hospede_id">Hóspede</label>
                            <div class="col-sm-6">
                                <select class='form-control select2' name='hospede_id' id='hospede_id'>
                                                <option value="">Selecione um hóspede</option>
                                                @foreach ($hospedes as $hospede)
                                                    @if ($hospede->nome !== 'Bloqueado')
                                                        <option value="{{ $hospede->id }}"
                                                            {{ old('hospede_id') == $hospede->id ? 'selected' : '' }}>
                                                            {{ $hospede->nome }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                            </div>
                            <div class="col-sm-2">
                                    <button type="button" id="btn-addhospede" class="btn btn-primary" data-toggle="modal"
                                        data-target="#modalCadastrarHospede">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                        </div>

                        <div class="form-group">
                            <label>Quarto</label>
                            <input type="text" id="quarto_selecionado" class="form-control" readonly>
                            <input type="hidden" id="quarto_id" name="quarto_id">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check-in</label>
                                    <input type="date" id="data_checkin" name="data_checkin" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check-out</label>
                                    <input type="date" id="data_checkout" name="data_checkout" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Situação</label>
                            <select id="situacao" name="situacao" class="form-control" required>
                                <option value="pre-reserva">Pré-reserva</option>
                                <option value="reserva">Reserva</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nº Adultos</label>
                                    <input type="number" name="n_adultos" class="form-control" value="1" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nº Crianças</label>
                                    <input type="number" name="n_criancas" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Valor da Diária</label>
                            <input type="text" name="valor_diaria" class="form-control money" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <!-- Modal de Cadastro de Hóspede -->
    <div class="modal fade" id="modalCadastrarHospede" tabindex="-1" role="dialog"
        aria-labelledby="modalHospedeLabel" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <form method="POST" action="{{ route('hospede.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHospedeLabel">Cadastrar Hóspede</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-md-2 label-control" for="nome">* Nome completo:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" required="required" type="text" name="nome"
                                        id="nome" autocomplete="off"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2 label-control" for="email">Email:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="email" name="email" id="email"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-2 label-control" for="telefone">Telefone:</label>
                            <div class="col-md-6">
                                <div><input class="form-control" type="tel" name="telefone" id="telefone"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('hospede.create') }}" class="btn btn-secondary">Cadastro Completo</a>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<link href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' rel='stylesheet' />
<style>
.mapa-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-cell {
    padding: 10px 8px;
    font-weight: bold;
    text-align: center;
    border-right: 1px solid #dee2e6;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.header-quartos {
    background-color: #e9ecef;
    border-right: 2px solid #dee2e6;
}

.data-header {
    padding: 5px 4px;
    text-align: center;
    border-right: 1px solid #dee2e6;
    font-size: 10px;
    line-height: 1.2;
    min-height: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.data-header .dia {
    font-weight: bold;
    font-size: 11px;
}

.data-header .data {
    font-size: 9px;
    color: #666;
}

.data-header .ocupacao {
    font-size: 8px;
    color: #28a745;
    font-weight: bold;
}

.categoria-row {
    background-color: #f1f3f4;
    border-bottom: 1px solid #dee2e6;
}

.categoria-header {
    padding: 8px;
    font-weight: bold;
    background-color: #e9ecef;
    border-right: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    font-size: 13px;
}

.categoria-info {
    padding: 4px;
    text-align: center;
    border-right: 1px solid #dee2e6;
    font-size: 9px;
    min-height: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.categoria-info .tarifa {
    font-weight: bold;
    color: #007bff;
}

.quarto-row {
    border-bottom: 1px solid #dee2e6;
}

.quarto-header {
    padding: 8px;
    background-color: #fff;
    border-right: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    font-size: 12px;
    min-height: 40px;
}

.quarto-cell {
    padding: 2px;
    border-right: 1px solid #dee2e6;
    min-height: 40px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s;
}

.quarto-cell:hover {
    background-color: #f8f9fa;
}

.quarto-cell.ocupado {
    background-color: #fff3cd;
}

.reserva-block {
    position: absolute;
    top: 2px;
    left: 2px;
    right: 2px;
    bottom: 2px;
    border-radius: 3px;
    padding: 2px 4px;
    font-size: 9px;
    font-weight: bold;
    color: white;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Cores por situação */
.situacao-pre-reserva { background-color: #ffc107; color: #212529; }
.situacao-reserva { background-color: #17a2b8; }
.situacao-hospedado { background-color: #28a745; }
.situacao-bloqueado { background-color: #007bff; }
.situacao-finalizada { background-color: #6c757d; }
.situacao-cancelado { background-color: #dc3545; }

.mapa-container {
    overflow-x: auto;
    max-height: 70vh;
    overflow-y: auto;
}

.row.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}

@media (max-width: 768px) {
    .header-cell, .data-header, .categoria-info, .quarto-cell {
        font-size: 8px;
        padding: 4px 2px;
    }
    
    .reserva-block {
        font-size: 7px;
        padding: 1px 2px;
    }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'></script>
<script>
let dadosMapa = {};
let quartoSelecionado = null;
let dataSelecionada = null;

$(document).ready(function() {
    // Máscara para valores monetários
    $('.money').mask('#.##0,00', {reverse: true});
    
    carregarMapa();
});

function carregarMapa() {
    $('#loading').show();
    $('#mapa-container').hide();
    
    const dataInicio = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();
    
    $.ajax({
        url: '{{ route("mapa.dados") }}',
        method: 'GET',
        data: {
            data_inicio: dataInicio,
            data_fim: dataFim
        },
        success: function(response) {
            if (response.success) {
                dadosMapa = response;
                renderizarMapa(response);
                $('#loading').hide();
                $('#mapa-container').show();
            } else {
                alert('Erro ao carregar mapa: ' + response.message);
            }
        },
        error: function() {
            alert('Erro ao carregar dados do mapa');
            $('#loading').hide();
        }
    });
}

function renderizarMapa(dados) {
    // Renderizar cabeçalho com datas
    let headerDatas = '';
    dados.datas.forEach(data => {
        const dataObj = new Date(data + 'T00:00:00');
        const dia = dataObj.toLocaleDateString('pt-BR', { weekday: 'short' }).toUpperCase();
        const dataFormatada = dataObj.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        const ocupacao = dados.ocupacao[data];
        
        headerDatas += `
            <div class="col data-header">
                <div class="dia">${dia}</div>
                <div class="data">${dataFormatada}</div>
                <div class="ocupacao">${ocupacao.percentual}%</div>
            </div>
        `;
    });
    $('#header-datas').html(headerDatas);
    
    // Renderizar corpo do mapa
    let mapaBody = '';
    dados.categorias.forEach(categoria => {
        // Linha da categoria
        mapaBody += `
            <div class="categoria-row">
                <div class="row no-gutters">
                    <div class="col-2 categoria-header">
                        <i class="fas fa-chevron-down"></i> ${categoria.titulo}
                    </div>
                    <div class="col-10">
                        <div class="row no-gutters">
        `;
        
        dados.datas.forEach(data => {
            const tarifa = categoria.tarifas[data] || 0;
            mapaBody += `
                <div class="col categoria-info">
                    <div>${categoria.total_quartos}</div>
                    <div class="tarifa">R$ ${tarifa.toFixed(2).replace('.', ',')}</div>
                </div>
            `;
        });
        
        mapaBody += `
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Linhas dos quartos
        categoria.quartos.forEach(quarto => {
            mapaBody += `
                <div class="quarto-row">
                    <div class="row no-gutters">
                        <div class="col-2 quarto-header">
                            ${quarto.nome}
                        </div>
                        <div class="col-10">
                            <div class="row no-gutters">
            `;
            
            dados.datas.forEach(data => {
                const reserva = encontrarReservaNaData(quarto.reservas, data);
                const cellClass = reserva ? 'ocupado' : '';
                
                mapaBody += `
                    <div class="col quarto-cell ${cellClass}" 
                         onclick="selecionarCelula(${quarto.id}, '${data}', '${quarto.nome}')"
                         data-quarto-id="${quarto.id}" 
                         data-data="${data}">
                `;
                
                if (reserva) {
                    mapaBody += `
                        <div class="reserva-block situacao-${reserva.situacao}">
                            ${reserva.hospede_nome}
                        </div>
                    `;
                }
                
                mapaBody += '</div>';
            });
            
            mapaBody += `
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    });
    
    $('#mapa-body').html(mapaBody);
}

function encontrarReservaNaData(reservas, data) {
    return reservas.find(reserva => {
        return reserva.data_checkin <= data && reserva.data_checkout > data;
    });
}

function selecionarCelula(quartoId, data, quartoNome) {
    quartoSelecionado = quartoId;
    dataSelecionada = data;
    
    // Verificar se já existe reserva nesta célula
    const reserva = encontrarReservaNaCelula(quartoId, data);
    
    if (reserva) {
        // Se já existe reserva, redirecionar para edição
        window.location.href = `/reserva/${reserva.id}/edit`;
        return;
    }
    
    // Se não existe reserva, abrir modal de ações
    $('#modalAcoes').modal('show');
}

function encontrarReservaNaCelula(quartoId, data) {
    for (let categoria of dadosMapa.categorias) {
        for (let quarto of categoria.quartos) {
            if (quarto.id === quartoId) {
                return encontrarReservaNaData(quarto.reservas, data);
            }
        }
    }
    return null;
}

function abrirModalReserva() {
    $('#modalAcoes').modal('hide');
    
    // Preencher dados do modal
    $('#quarto_id').val(quartoSelecionado);
    $('#quarto_selecionado').val(obterNomeQuarto(quartoSelecionado));
    $('#data_checkin').val(dataSelecionada);
    
    // Calcular data de checkout (próximo dia)
    const checkout = new Date(dataSelecionada + 'T00:00:00');
    checkout.setDate(checkout.getDate() + 1);
    $('#data_checkout').val(checkout.toISOString().split('T')[0]);
    
    $('#modalReserva').modal('show');
}

function criarBloqueio() {
    $('#modalAcoes').modal('hide');
    
    // Calcular data de checkout (próximo dia)
    const checkout = new Date(dataSelecionada + 'T00:00:00');
    checkout.setDate(checkout.getDate() + 1);
    
    $.ajax({
        url: '{{ route("mapa.criar-reserva") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            quarto_id: quartoSelecionado,
            data_checkin: dataSelecionada,
            data_checkout: checkout.toISOString().split('T')[0],
            tipo: 'bloqueio'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                carregarMapa(); // Recarregar mapa
            } else {
                alert('Erro: ' + response.message);
            }
        },
        error: function() {
            alert('Erro ao criar bloqueio');
        }
    });
}

function obterNomeQuarto(quartoId) {
    for (let categoria of dadosMapa.categorias) {
        for (let quarto of categoria.quartos) {
            if (quarto.id === quartoId) {
                return quarto.nome;
            }
        }
    }
    return '';
}

// Submissão do formulário de reserva
$('#formReserva').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('tipo', 'reserva');
    
    // Converter valor da diária
    const valorDiaria = $('input[name="valor_diaria"]').val().replace(/\./g, '').replace(',', '.');
    console.log('[INFO] Valor da diária bruto:', $('input[name="valor_diaria"]').val());
console.log('[INFO] Valor da diária convertido:', valorDiaria);
    formData.set('valor_diaria', valorDiaria);
    console.log('[INFO] Conteúdo do FormData:');
for (let pair of formData.entries()) {
    console.log(`  ${pair[0]}: ${pair[1]}`);
}
    
    $.ajax({
        url: '{{ route("mapa.criar-reserva") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('[RESPOSTA COMPLETA]', response);

            $('#modalReserva').modal('hide');
            
            if (response.success || response.redirect) {
                alert('Reserva criada com sucesso!');
                carregarMapa(); // Recarregar mapa
            } else {
                console.warn('[ERRO AJAX] Resposta com success: false');
        console.warn('[ERRO AJAX] Mensagem:', response.message);
        alert(response.message || 'Erro ao criar reserva');

            }
        },
        error: function(xhr, status, error) {
    console.error('[ERRO] Status da requisição:', status);
    console.error('[ERRO] Código HTTP:', xhr.status);
    console.error('[ERRO] Mensagem:', error);
    console.error('[ERRO] Resposta completa:', xhr.responseText);
    alert('Erro ao criar reserva. Veja o console para mais detalhes.');
}
    });
});
</script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'selecione...',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@stop

