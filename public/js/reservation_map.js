// Variáveis globais para estado da seleção
let selectedStartDate = null;
let selectedEndDate = null;
let selectedSpaceId = null;
let isSelecting = false; // Indica se o usuário está no processo de selecionar um intervalo

// Função auxiliar para formatar data (YYYY-MM-DD -> DD/MM/YYYY)
function formatDateBR(dateString) {
    if (!dateString) return '';
    const cleanDate = dateString.split(' ')[0]; // Pega só a parte da data antes do espaço
    const [year, month, day] = cleanDate.split('-');
    return `${day}/${month}/${year}`;
}


// Função para exibir/limpar feedback visual
function updateSelectionFeedback(message = '') {
    const feedbackElement = document.getElementById('selection_feedback');
    if (feedbackElement) {
        feedbackElement.innerHTML = message;
    }
}

// Função para buscar dados de disponibilidade do backend
async function fetchAvailability(startDate, endDate) {
    // Substitua pela URL correta do seu endpoint Laravel (sem /api/ se estiver em web.php)
    const apiUrl = `/espacos/disponibilidade?start_date=${startDate}&end_date=${endDate}`; 
    try {
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Se CSRF for necessário
            }
        });
        if (!response.ok) {
            console.error("Erro na resposta da API:", response.status, response.statusText);
            const errorText = await response.text(); // Pegar texto do erro
            Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: `Erro ao buscar disponibilidade: ${response.status} ${response.statusText}\n${errorText}`,
                    });
            return null;
        }
        return await response.json();
    } catch (error) {
        console.error("Erro ao buscar dados de disponibilidade:", error);
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: `Ocorreu um erro de rede ao buscar a disponibilidade. Verifique o console para mais detalhes.`,
            });
        return null;
    }
}


function addCellListeners() {
    const cells = document.querySelectorAll('.date-cell.available');
    cells.forEach(cell => {
        cell.addEventListener('click', handleDateClick);
        cell.addEventListener('mouseover', handleDateHover);
        cell.addEventListener('mouseout', handleDateHoverOut);
    });
}




// Função para renderizar o mapa de reservas
function renderMap(data) {
    const container = document.getElementById('reservation_map_container');
    if (!data || !data.spaces || data.spaces.length === 0) {
        container.innerHTML = '<p>Nenhum espaço encontrado ou erro ao carregar dados.</p>';
        return;
    }

    const startDate = new Date(data.startDate + 'T00:00:00');
    const endDate = new Date(data.endDate + 'T00:00:00');

    let tableHTML = '<table class="table table-bordered reservation-map-table"><thead><tr><th class="space-header">Espaço</th>';
    const dates = [];
    let currentDate = new Date(startDate);
    while (currentDate <= endDate) {
        const dateString = currentDate.toISOString().split('T')[0];
        dates.push(dateString);
        const day = String(currentDate.getDate()).padStart(2, '0');
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        tableHTML += `<th>${day}/${month}</th>`;
        currentDate.setDate(currentDate.getDate() + 1);
    }
    tableHTML += '</tr></thead><tbody>';

    data.spaces.forEach(space => {
        // Adiciona data-space-name ao TR para fácil acesso posterior
        tableHTML += `<tr data-space-id="${space.id}" data-space-name="${space.nome}"><td class="space-header">${space.nome}</td>`;
        const bookingsMap = new Map();
        space.bookings.forEach(booking => {
            let bookingStart = new Date(booking.start + 'T00:00:00');
            let bookingEnd = new Date(booking.end + 'T00:00:00');
            let currentBookingDate = new Date(bookingStart);
            while (currentBookingDate <= bookingEnd) {
                const dateStr = currentBookingDate.toISOString().split('T')[0];
                if (currentBookingDate >= startDate && currentBookingDate <= endDate) {
                    bookingsMap.set(dateStr, 'booked');
                }
                currentBookingDate.setDate(currentBookingDate.getDate() + 1);
            }
        });

        dates.forEach(dateStr => {
    const status = bookingsMap.get(dateStr) || 'available';
    let cellClass = `date-cell ${status}`;
    let dataAttributes = `data-date="${dateStr}" data-space-id="${space.id}"`;

    // Se a data for "booked", adicione o reserva-id
     const reservaIdAtual = document.getElementById('reserva_id_atual')?.value;
    if (status === 'booked') {
    const reservaId = space.bookings.find(b => {
        const bookingStart = new Date(b.start + 'T00:00:00');
        const bookingEnd = new Date(b.end + 'T00:00:00');
        const dateObj = new Date(dateStr + 'T00:00:00');
        return dateObj >= bookingStart && dateObj <= bookingEnd;
    })?.aluguel_id;

    if (reservaId) {
        // Só aplica class "booked" se NÃO for da reserva atual
        if (reservaId != reservaIdAtual) {
            dataAttributes += ` data-reserva-id="${reservaId}"`;
        } else {
            // Se for a própria reserva, marca como disponível (libera)
            cellClass = 'date-cell available';
        }
    }
}

    tableHTML += `<td class="${cellClass}" ${dataAttributes}>`;
    if (status === 'booked') {
        tableHTML += `<span>X</span>`;
    }
    tableHTML += `</td>`;
});

        tableHTML += '</tr>';
    });

    tableHTML += '</tbody></table>';
    container.innerHTML = tableHTML;
    addCellListeners();
    
   // Se for uma edição de reserva, pré-seleciona as datas
   const dataInicioAtual = document.getElementById('data_inicio_atual')?.value;
    const dataFimAtual = document.getElementById('data_fim_atual')?.value;
    const espacoIdAtual = document.getElementById('espaco_id_atual')?.value;
    const reservaIdAtual = document.getElementById('reserva_id_atual')?.value;
        
    if (dataInicioAtual && dataFimAtual && espacoIdAtual) {
        // Liberar as datas da própria reserva (remover classe booked)
        if (reservaIdAtual) {
            document.querySelectorAll(`.date-cell.booked[data-reserva-id="${reservaIdAtual}"]`).forEach(cell => {
                cell.classList.remove('booked');
                cell.classList.add('available');
            });
            addCellListeners();
        }

        // Marcar as datas da reserva atual como selecionadas
        document.querySelectorAll(`.date-cell[data-space-id="${espacoIdAtual}"]`).forEach(cell => {
            const cellDate = cell.getAttribute('data-date');
            if (cellDate >= dataInicioAtual && cellDate <= dataFimAtual) {
                cell.classList.add('selected');
            }
        });

        // Atualiza variáveis globais
        selectedStartDate = dataInicioAtual;
        selectedEndDate = dataFimAtual;
        selectedSpaceId = espacoIdAtual;
        isSelecting = false;
        updateCellStyles();

        // Atualiza o feedback visual
        const spaceRow = document.querySelector(`tr[data-space-id="${espacoIdAtual}"]`);
        const spaceName = spaceRow ? spaceRow.dataset.spaceName : `Espaço ID ${espacoIdAtual}`;
        updateSelectionFeedback(`Período selecionado: ${formatDateBR(dataInicioAtual)} a ${formatDateBR(dataFimAtual)} para ${spaceName}.`);
    }

}

// Função para lidar com o clique na célula de data
function handleDateClick(event) {
    const cell = event.target.closest('.date-cell');
    if (!cell || !cell.classList.contains('available')) return;

    const clickedDate = cell.dataset.date;
    const clickedSpaceId = cell.dataset.spaceId;

    if (selectedSpaceId !== null && selectedSpaceId !== clickedSpaceId) {
        resetSelection();
    }

    selectedSpaceId = clickedSpaceId; // Atualiza o ID do espaço selecionado

    if (!isSelecting) {
        selectedStartDate = clickedDate;
        selectedEndDate = null;
        isSelecting = true;
        updateCellStyles();
        cell.classList.add('selected');
        updateSelectionFeedback('Clique na data final para completar a seleção.'); // Feedback inicial
        console.log(`Início selecionado: ${selectedStartDate} para Espaço ID: ${selectedSpaceId}`);
    } else {
        const startDateObj = new Date(selectedStartDate + 'T00:00:00');
        const clickedDateObj = new Date(clickedDate + 'T00:00:00');

        if (clickedDateObj < startDateObj) {
            selectedStartDate = clickedDate;
            selectedEndDate = null;
            updateCellStyles();
            cell.classList.add('selected');
            updateSelectionFeedback('Seleção reiniciada. Clique na data final.'); // Feedback de reinício
            console.log(`Seleção reiniciada. Novo início: ${selectedStartDate}`);
        } else {
            selectedEndDate = clickedDate;
            isSelecting = false;

            if (isRangeBooked(selectedStartDate, selectedEndDate, selectedSpaceId)) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: `O intervalo selecionado inclui dias já reservados. Por favor, escolha um período diferente.`,
                    });
                resetSelection();
            } else {
                updateCellStyles();
                // **ATUALIZADO: Passa selectedSpaceId para updateHiddenFields**
                updateHiddenFields(selectedStartDate, selectedEndDate, selectedSpaceId);

                // Adiciona feedback visual
                const spaceRow = document.querySelector(`tr[data-space-id="${selectedSpaceId}"]`);
                const spaceName = spaceRow ? spaceRow.dataset.spaceName : `Espaço ID ${selectedSpaceId}`;
                const feedbackMsg = `Período selecionado: ${formatDateBR(selectedStartDate)} a ${formatDateBR(selectedEndDate)} para ${spaceName}.`;
                updateSelectionFeedback(feedbackMsg);

                console.log(`Intervalo selecionado: ${selectedStartDate} a ${selectedEndDate} para Espaço ID: ${selectedSpaceId}`);
            }
        }
    }
}

// Função para verificar se há reservas no intervalo selecionado
function isRangeBooked(start, end, spaceId) {
    const startDateObj = new Date(start + 'T00:00:00');
    const endDateObj = new Date(end + 'T00:00:00');
    let currentCheckDate = new Date(startDateObj);
    while (currentCheckDate <= endDateObj) {
        const dateStr = currentCheckDate.toISOString().split('T')[0];
        const cell = document.querySelector(`.date-cell[data-date="${dateStr}"][data-space-id="${spaceId}"]`);
       if (cell && cell.classList.contains('booked')) {
            // Se for a própria reserva, ignora
            const reservaIdAtual = document.getElementById('reserva_id_atual')?.value;
            const cellReservaId = cell.getAttribute('data-reserva-id');

            if (reservaIdAtual && cellReservaId == reservaIdAtual) {
                return false; // Permite a seleção sobre a própria reserva
            }

            return true;
        }

        currentCheckDate.setDate(currentCheckDate.getDate() + 1);
    }
    return false;
}

// Função para lidar com hover sobre células (feedback visual)
function handleDateHover(event) {
    if (!isSelecting || !selectedStartDate) return;
    const cell = event.target.closest('.date-cell');
    if (!cell || !cell.classList.contains('available') || cell.dataset.spaceId !== selectedSpaceId) return;
    const hoverDate = cell.dataset.date;
    highlightRange(selectedStartDate, hoverDate, selectedSpaceId, true);
}

// Função para remover highlight do hover
function handleDateHoverOut(event) {
    if (!isSelecting) return;
    document.querySelectorAll(`.date-cell[data-space-id="${selectedSpaceId}"].selecting`).forEach(c => {
        c.classList.remove('selecting');
    });
}

// Função para atualizar estilos das células (selecionado, intervalo)
function updateCellStyles() {
    document.querySelectorAll('.date-cell.selected, .date-cell.selecting').forEach(cell => {
        cell.classList.remove('selected', 'selecting');
    });
    if (!selectedStartDate || !selectedSpaceId) return;
    const startCell = document.querySelector(`.date-cell[data-date="${selectedStartDate}"][data-space-id="${selectedSpaceId}"]`);
    if (startCell) {
        startCell.classList.add('selected');
    }
    if (selectedEndDate) {
        highlightRange(selectedStartDate, selectedEndDate, selectedSpaceId, false);
    }
}

// Função para destacar um intervalo de datas
function highlightRange(start, end, spaceId, isHover) {
    const startDateObj = new Date(start + 'T00:00:00');
    const endDateObj = new Date(end + 'T00:00:00');
    const loopStart = startDateObj <= endDateObj ? startDateObj : endDateObj;
    const loopEnd = startDateObj <= endDateObj ? endDateObj : startDateObj;
    let currentDate = new Date(loopStart);
    while (currentDate <= loopEnd) {
        const dateStr = currentDate.toISOString().split('T')[0];
        const cell = document.querySelector(`.date-cell[data-date="${dateStr}"][data-space-id="${spaceId}"]`);
        if (cell && cell.classList.contains('available')) {
            cell.classList.remove('selecting');
            if (isHover) {
                cell.classList.add('selecting');
            } else {
                cell.classList.add('selected');
            }
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
}

// Função para resetar a seleção
function resetSelection() {
    const valorTotal = document.getElementById('valor_total')
    valorTotal.value = ''
    selectedStartDate = null;
    selectedEndDate = null;
    selectedSpaceId = null;
    isSelecting = false;
    // **ATUALIZADO: Passa null para spaceId ao resetar**
    updateHiddenFields('', '', null);
    updateCellStyles();
    updateSelectionFeedback(); // Limpa feedback visual
    console.log("Seleção resetada.");
}

function updateHiddenFields(startDate, endDate, spaceId) {
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    const spaceIdInput = document.getElementById('espaco_id_hidden');

    dataInicio.value = startDate;
    dataFim.value = endDate;

    if (spaceIdInput) {
        spaceIdInput.value = spaceId !== null ? spaceId : '';
    }

    // Dispara os eventos para acionar o cálculo
    dataInicio.dispatchEvent(new Event('change'));
    dataFim.dispatchEvent(new Event('change'));
    if (spaceIdInput) {
        spaceIdInput.dispatchEvent(new Event('change'));
    }
}

// Função de inicialização
async function initMap() {
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    const initialStartDate = firstDayOfMonth.toISOString().split('T')[0];
    const initialEndDate = lastDayOfMonth.toISOString().split('T')[0];

    document.getElementById('map_start_date').value = initialStartDate;
    document.getElementById('map_end_date').value = initialEndDate;

    const data = await fetchAvailability(initialStartDate, initialEndDate);
    renderMap(data);

    document.getElementById('filter_button').addEventListener('click', async () => {
        const startDate = document.getElementById('map_start_date').value;
        const endDate = document.getElementById('map_end_date').value;
        if (!startDate || !endDate) {
             Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: `Por favor, selecione as datas de início e fim.`,
                    });
            return;
        }
        if (new Date(startDate) > new Date(endDate)) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: `A data de início não pode ser posterior à data de fim.`,
                });
            return;
        }
        resetSelection(); // Reseta seleção ao atualizar o mapa
        updateSelectionFeedback(); // Limpa feedback ao atualizar
        const container = document.getElementById('reservation_map_container');
        container.innerHTML = '<p>Atualizando mapa...</p>';
        const newData = await fetchAvailability(startDate, endDate);
        renderMap(newData);
    });

    document.addEventListener('click', (event) => {
        const mapContainer = document.getElementById('reservation_map_container');
        if (!mapContainer.contains(event.target) && !event.target.closest('#filter_button')) {
            // reseta ao clicar fora durante a seleção
            if (isSelecting) {
                resetSelection(); 
            }
        }
    });


    const dataInicio = document.getElementById('data_inicio')?.value;
    const dataFim = document.getElementById('data_fim')?.value;
    const spaceId = document.getElementById('espaco_id_hidden')?.value;

    if (dataInicio && dataFim && spaceId) {
        updateHiddenFields(dataInicio, dataFim, spaceId);
    }
}

document.addEventListener('DOMContentLoaded', initMap);

