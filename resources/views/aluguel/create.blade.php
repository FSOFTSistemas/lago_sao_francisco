@extends('adminlte::page')

@section('title', isset($aluguel) ? 'Atualizar Aluguel' : 'Novo Aluguel')

@section('content_header')
    <h5>{{ isset($aluguel) ? 'Atualizar Aluguel' : 'Novo Aluguel' }}</h5>
    <hr>
@endsection

@section('content')
<form action="{{ isset($aluguel) ? route('aluguel.update', $aluguel->id) : route('aluguel.store') }}" method="POST">
    @csrf
    @if(isset($aluguel))
        @method('PUT')
    @endif
 
    <div class="card">
        <div class="card-body mt-3">
            <ul class="nav nav-tabs" id="aluguelTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active editlink" id="info-tab" data-toggle="tab" href="#info" role="tab">Reserva</a>
                </li>
                <li class="nav-item" id="buffetAba" style="display: none">
                    <a class="nav-link editlink" id="buffet-tab" data-toggle="tab" href="#tab-buffet" role="tab">Buffet</a>
                </li>
                <li class="nav-item" id="pagamentoTab">
                    <a class="nav-link editlink" id="buffet-tab" data-toggle="tab" href="#tab-pagamento" role="tab">Pagamento</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="aluguelTabsContent">
                
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    
                    
                          <!-- Campo de cliente -->

                    <div class="form-group row">
                  <label for="cliente_id" class="col-md-3 label-control">* Cliente</label>
                  <div class="col-sm-4">
                    @php
                        $clienteSelecionado = old('cliente_id', $aluguel->cliente_id ?? '');
                    @endphp
                    @if ($clienteSelecionado)
                        <select class="form-control select2" name="cliente_id_disabled" id="cliente_id" disabled>
                            <option value="">Selecione um cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" 
                                    {{ $clienteSelecionado == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome_razao_social }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="cliente_id" value="{{ $clienteSelecionado }}">
                    @else
                        <select class="form-control select2" name="cliente_id" id="cliente_id">
                            <option value="">Selecione um Cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" 
                                    {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome_razao_social }}
                                </option>   
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-sm-2">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCadastrarCliente">
                    <i class="fas fa-user-plus"></i>
                  </button>
                </div>
            </div>

             <div class="form-group row">
                        <label class="col-md-3 label-control form-lab d-block">* Buffet?</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="ativo" value="0">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="ativoSwitch" 
                                name="ativo" 
                                value="1"
                                {{ old('ativo', $tarifa->ativo ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                                {{ old('ativo', $tarifa->ativo ?? false) ? 'Sim' : 'Não' }}
                            </label>
                        </div>
                    </div>

                         

                   {{-- Aba 1: Informações da Reserva --}}
                    {{-- ====================================================== --}}
                       {{-- INÍCIO: Mapa de Reservas Integrado --}}
                       {{-- ====================================================== --}}
                       <div class="card card-primary card-outline mb-4">
                           <div class="card-header">
                               <h3 class="card-title">Selecionar Período e Espaço</h3>
                           </div>
                           <div class="card-body">
                               <!-- Filtros de Data -->
                               <div class="row mb-3">
                                   <div class="col-md-4">
                                       <label for="map_start_date">Data Início:</label>
                                       <input type="date" id="map_start_date" class="form-control">
                                   </div>
                                   <div class="col-md-4">
                                       <label for="map_end_date">Data Fim:</label>
                                       <input type="date" id="map_end_date" class="form-control">
                                   </div>
                                   <div class="col-md-4 align-self-end">
                                       <button type="button" id="filter_button" class="btn btn-primary">Atualizar Mapa</button> 
                                   </div>
                               </div>
    
                               <!-- Mapa de Reservas -->
                               <div id="reservation_map_container" class="table-responsive">
                                   <p>Carregando mapa...</p>
                               </div>
    
                               <div id="selection_feedback" class="mt-2 text-success font-weight-bold"></div>
    
                               <input type="hidden" id="data_inicio" name="data_inicio" value="{{ old('data_inicio', $aluguel->data_inicio ?? '') }}">
                               <input type="hidden" id="data_fim" name="data_fim" value="{{ old('data_fim', $aluguel->data_fim ?? '') }}">
                               {{-- Adicione um campo hidden para espaco_id se necessário --}}
                                <input type="hidden" id="espaco_id_hidden" name="espaco_id" value="{{ old("espaco_id", $aluguel->espaco_id ?? "") }}">

                                <input type="text" id="valor_total" name="valor_total" readonly>

    
                           </div>
                           <!-- /.card-body -->
                       </div>
                       {{-- ====================================================== --}}
                       {{-- FIM: Mapa de Reservas Integrado --}}
                       {{-- ====================================================== --}}

                       <div class="alert alert-secondary">
                           <strong>DICA:</strong> Para selecionar a data da reserva/aluguel, basta clicar na data referente ao espaço desejado. <br>
                           <em>Para selecionar apenas 1 dia, basta clicar na data escolhida 2 vezes.</em>
                       </div>
                       <hr>
                       <div class="form-group row">
                            <label for="observacoes" class="col-md-3 label-control">Observações extras:</label>
                            <div class="col-md-6">
                                <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $tarifa->observacoes ?? '') }}</textarea>
                            </div>
                        </div>           
                                  
                </div>


                {{-- Aba 2: Buffet --}}
                <div class="tab-pane fade" id="tab-buffet">
                    <div class="form-group row">
                        <label for="numero_pessoas_buffet" class="col-md-3 label-control">* Número de Pessoas:</label>
                        <div class="col-md-3">
                            <input type="number" name="numero_pessoas_buffet" id="numero_pessoas_buffet"
                                class="form-control" value="{{ old('numero_pessoas_buffet', $aluguel->numero_pessoas_buffet ?? '') }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="cardapio_id" class="col-md-3 label-control">* Cardápio:</label>
                        <div class="col-md-6">
                            <select name="cardapio_id" id="cardapio_id" class="form-control">
                                <option value="">Selecione um cardápio</option>
                                @foreach ($cardapios as $cardapio)
                                    <option value="{{ $cardapio->id }}">{{ $cardapio->NomeCardapio }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Container principal do buffet com responsividade melhorada -->
                    <div class="row">
                        <div class="col-12">
                            <div id="categoriasContainer" class="buffet-container">
                                <!-- As categorias e itens serão carregados aqui via JavaScript -->
                            </div>
        
                            <hr>
        
                            <div id="opcoesContainer" class="buffet-container">
                                <!-- As opções de jantar e itens serão carregados aqui via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control">Total Buffet Estimado:</label>
                        <div class="col-md-3">
                            <input type="text" id="total_buffet" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Campos hidden para armazenar as escolhas do buffet -->
                    <input type="hidden" id="buffet_categorias_escolhidas" name="buffet_categorias_escolhidas" value="">
                    <input type="hidden" id="buffet_opcao_escolhida" name="buffet_opcao_escolhida" value="">
                </div>
                
              
                {{--Aba 3: Pagamento--}}
                <div class="tab-pane fade" id="tab-pagamento">

                </div>
            </div>
        </div>
            {{-- Infos finais --}}
            @if(isset($aluguel))
                <p class="text-muted mt-3">
                    Criado em: {{ $aluguel->created_at->format('d/m/Y H:i:s') }}<br>
                    Alterado em: {{ $aluguel->updated_at->format('d/m/Y H:i:s') }}<br>
                    Alterado por: {{ Auth::user()->name }}
                </p>
            @endif

        
        <div class="card-footer text-end">
            <button type="submit" class="btn new btn-{{ isset($aluguel) ? 'info' : 'success' }}">
                {{ isset($aluguel) ? 'Atualizar Aluguel' : 'Criar Aluguel' }}
            </button>
        </div>
    </div>
</form>

  <!-- Modal de Cadastro de Cliente -->
          <div class="modal fade" id="modalCadastrarCliente" tabindex="-1" role="dialog" aria-labelledby="modalClienteLabel" aria-hidden="true">
            <div class="modal-dialog " role="document">
                <form method="POST" action="{{ route('cliente.store') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalClienteLabel">Cadastrar Cliente</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
      
                        <div class="modal-body">
                          <div class="form-group row">
                            <label class="col-md-4 label-control" for="nomeRazaoSocial">* Nome/Razão Social:</label>
                            <div class="col-md-6">
                              <div><input class="form-control" type="text" name="nome_razao_social" id="nomeRazaoSocial" autocomplete="off"></div>
                            </div>
                        </div>
          
                        <div class="form-group row">
                            <label class="col-md-4 label-control"  for="apelidoNomeFantasia">* Apelido/Nome fantasia:</label>
                            <div class="col-md-6">
                              <div><input class="form-control" type="text" name="apelido_nome_fantasia" id="apelidoNomeFantasia"></div>
                            </div>
                          </div>
      
                          <div class="form-group row">
                            <label class="col-md-4 label-control" for="dataNascimento">* Data de Nascimento:</label>
                            <div class="col-md-6">
                              <div><input class="form-control"  type="date" name="data_nascimento" id="dataNascimento"></div>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label class="col-md-4 label-control" for="cpfCnpj">* Documentos:</label>
                            <div class=" row col-md-8">
                                <div class="col-md-4">
                                    <label for="cpfCnpj">CPF/CNPJ</label>
                                    <input type="text" class="form-control" id="cpfCnpj" name="cpf_cnpj" >
                                </div>
                                <div class="col-md-8">
                                    <label for="rgIe">RG/Inscrição Estadual:</label>
                                    <input type="text" class="form-control" id="rgIe" name="rg_ie">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 label-control" for="tipo">* Tipo:</label>
                            <div class="col-md-4">
                                <select class="form-control select2" id="tipo" name="tipo" required>
                                    <option value="PF">Pessoa Física</option>
                                    <option value="PJ">Pessoa Jurídica</option>
                                </select>
                            </div>
                        </div>
                        </div>
      
      
                        <div class="modal-footer">
                            <a href="{{route('cliente.create')}}">Cadastro Completo</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>
          </div>

@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/reservation_map.js') }}"></script>
<script> // script para o checkbox buffet
    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('ativoSwitch');
        const label = document.getElementById('ativoLabel');

        switchInput.addEventListener('change', function () {
            label.textContent = this.checked ? 'Sim' : 'Não';
        });
    });
</script>

<script> //habilita a aba buffet pelo checkbox
    document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById("ativoSwitch");
    const abaBuffet = document.getElementById("buffetAba");

    function atualizarEstilo() {
        if (checkbox.checked) {
            abaBuffet.removeAttribute("style"); 
        } else {
            abaBuffet.style.display = "none"; 
        }
    }
    // Atualiza o estilo ao carregar a página
    atualizarEstilo();

    // Monitorando mudanças no checkbox em tempo real
    checkbox.addEventListener("change", atualizarEstilo);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cardapioSelect = document.getElementById('cardapio_id');
    const categoriasContainer = document.getElementById('categoriasContainer');
    const opcoesContainer = document.getElementById('opcoesContainer');
    const numeroPessoasInput = document.getElementById('numero_pessoas_buffet');
    const totalFinalInput = document.getElementById('total_buffet');
    
    // Campos hidden para armazenar as escolhas
    const buffetCategoriasEscolhidas = document.getElementById('buffet_categorias_escolhidas');
    const buffetOpcaoEscolhida = document.getElementById('buffet_opcao_escolhida');

    let opcoesData = [];

    cardapioSelect.addEventListener('change', function () {
        const cardapioId = this.value;
        categoriasContainer.innerHTML = '';
        opcoesContainer.innerHTML = '';
        totalFinalInput.value = '';
        
        // Limpar campos hidden
        buffetCategoriasEscolhidas.value = '';
        buffetOpcaoEscolhida.value = '';

        if (cardapioId) {
            fetch(`/cardapios/${cardapioId}/dados`)
                .then(response => response.json())
                .then(data => {
                    console.log(data)
                    opcoesData = data.opcoes;  // Armazena para uso no cálculo depois

                    // ---- CATEGORIAS DAS SEÇÕES ----
                    data.secoes.forEach(secao => {
                        secao.categorias.forEach(categoria => {
                            const categoriaDiv = document.createElement('div');
                            categoriaDiv.classList.add('card', 'mb-3', 'border');

                            const header = document.createElement('div');
                            header.classList.add('card-header', 'green', 'pb-0');
                            header.innerHTML = `<strong>${categoria.nome_categoria_item}</strong> (${secao.nome_secao_cardapio})<br><small>Máximo de ${categoria.numero_escolhas_permitidas} escolha(s)</small>`;

                            const body = document.createElement('div');
                            body.classList.add('card-body');
                            const itensDiv = document.createElement('div');
                            itensDiv.classList.add('row');

                            
                            categoria.itens.forEach(disp => {
                                const item = disp.item;
                                const checkboxDiv = document.createElement('div');
                                checkboxDiv.classList.add('form-check', 'col-md-6', 'col-lg-4', 'mb-2');

                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.name = `categorias[${categoria.id}][itens][]`;
                                checkbox.value = item.id;
                                checkbox.classList.add('form-check-input');
                                checkbox.dataset.categoriaId = categoria.id;
                                checkbox.id = `checkbox-${categoria.id}-${item.id}`;

                                const label = document.createElement('label');
                                label.classList.add('form-check-label', 'ms-1');
                                label.textContent = item.nome_item;
                                label.setAttribute('for', checkbox.id);

                                checkboxDiv.appendChild(checkbox);
                                checkboxDiv.appendChild(label);
                                itensDiv.appendChild(checkboxDiv);
                            });

                            body.appendChild(itensDiv);
                            categoriaDiv.appendChild(header);
                            categoriaDiv.appendChild(body);
                            categoriasContainer.appendChild(categoriaDiv);
                        });
                    });

                    // Limitar número de seleções por categoria e atualizar campos hidden
                    const checkboxes = categoriasContainer.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function () {
                            const categoriaId = this.dataset.categoriaId;
                            const categoriaCheckboxes = categoriasContainer.querySelectorAll(`input[data-categoria-id="${categoriaId}"]`);
                            
                            // Buscar dados da categoria na estrutura aninhada
                            let categoriaData = null;
                            data.secoes.forEach(secao => {
                                const categoria = secao.categorias.find(cat => cat.id == categoriaId);
                                if (categoria) {
                                    categoriaData = categoria;
                                }
                            });
                            
                            const selecionados = Array.from(categoriaCheckboxes).filter(cb => cb.checked);

                            if (categoriaData && selecionados.length > categoriaData.numero_escolhas_permitidas) {
                                this.checked = false;
                                Swal.fire({
                                    icon: "error",
                                    title: "Limite excedido",
                                    text: `Você só pode escolher até ${categoriaData.numero_escolhas_permitidas} item(ns) para a categoria "${categoriaData.nome_categoria_item}".`,
                                });
                            } else {
                                // Atualizar campo hidden com as escolhas das categorias
                                atualizarCategoriasEscolhidas();
                            }
                        });
                    });

                    // ---- OPÇÕES DE REFEIÇÃO (COM ITENS INTERNOS) ----
                    if (data.opcoes.length > 0) {
                        const opcoesTitle = document.createElement('h5');
                        opcoesTitle.textContent = "Escolha uma Opção de Refeição:";
                        opcoesContainer.appendChild(opcoesTitle);

                        data.opcoes.forEach(opcao => {
                        const card = document.createElement('div');
                        card.classList.add('card', 'mb-3');

                        const cardHeader = document.createElement('div');
                        cardHeader.classList.add('card-header', 'green', 'pb-0');

                        const inputRadio = document.createElement('input');
                        inputRadio.classList.add('form-check-input');
                        inputRadio.type = 'radio';
                        inputRadio.name = 'opcao_escolhida';
                        inputRadio.value = opcao.id;
                        inputRadio.dataset.preco = opcao.PrecoPorPessoa;
                        inputRadio.id = `radio-${opcao.id}`; // ID único

                        const label = document.createElement('label');
                        label.classList.add('form-check-label');
                        label.setAttribute('for', inputRadio.id); // Vinculando ao input
                        label.textContent = `${opcao.NomeOpcaoRefeicao} - R$ ${parseFloat(opcao.PrecoPorPessoa).toFixed(2)}`;

                        // Criando a div do form-check e adicionando os elementos
                        const formCheckDiv = document.createElement('div');
                        formCheckDiv.classList.add('form-check');
                        formCheckDiv.appendChild(inputRadio);
                        formCheckDiv.appendChild(label);

                        cardHeader.appendChild(formCheckDiv);
                        card.appendChild(cardHeader);

                        const cardBody = document.createElement('div');
                        cardBody.classList.add('card-body', 'p-2', 'h6');

                        opcao.categorias.forEach(categoria => {
                            const catTitle = document.createElement('strong');
                            catTitle.textContent = categoria.nome_categoria_item
;
                            cardBody.appendChild(catTitle);

                            categoria.itens.forEach(disp => {
                                const item = disp.item
                                const itemDiv = document.createElement('div');
                                itemDiv.classList.add('ms-3');
                                itemDiv.textContent = `- ${item.nome_item}`;
                                cardBody.appendChild(itemDiv);
                            });

                            cardBody.appendChild(document.createElement('hr'));
                        });

                        card.appendChild(cardBody);
                        opcoesContainer.appendChild(card);
                    });

                        // Evento de mudança para recalcular valor ao escolher uma opção
                        opcoesContainer.querySelectorAll('input[name="opcao_escolhida"]').forEach(radio => {
                            radio.addEventListener('change', function() {
                                calcularValorFinal();
                                // Atualizar campo hidden com a opção escolhida
                                buffetOpcaoEscolhida.value = this.value;
                            });
                        });
                    }

                })
                .catch(error => {
                    console.error('Erro ao carregar dados do cardápio:', error);
                });
        }
    });

    // Função para atualizar o campo hidden com as categorias escolhidas
    function atualizarCategoriasEscolhidas() {
        const categoriasEscolhidas = {};
        const checkboxes = categoriasContainer.querySelectorAll('input[type="checkbox"]:checked');
        
        checkboxes.forEach(checkbox => {
            const categoriaId = checkbox.dataset.categoriaId;
            const itemId = checkbox.value;
            
            if (!categoriasEscolhidas[categoriaId]) {
                categoriasEscolhidas[categoriaId] = [];
            }
            categoriasEscolhidas[categoriaId].push(itemId);
        });
        
        buffetCategoriasEscolhidas.value = JSON.stringify(categoriasEscolhidas);
    }

    // Calcular Total Buffet (Baseado no número de pessoas e na opção selecionada)
    function calcularValorFinal() {
        const numeroPessoas = parseInt(numeroPessoasInput.value) || 0;
        const opcaoSelecionada = document.querySelector('input[name="opcao_escolhida"]:checked');
        let total = 0;

        if (opcaoSelecionada) {
            const precoPorPessoa = parseFloat(opcaoSelecionada.dataset.preco);
            total = numeroPessoas * precoPorPessoa;
        }

        totalFinalInput.value = "R$ " + total.toFixed(2).replace('.', ',');
    }

    numeroPessoasInput.addEventListener('input', calcularValorFinal);
});
</script>


<script> // calcula o valor total do espaço
document.addEventListener('DOMContentLoaded', function () {
    const espacoSelect = document.getElementById('espaco_id_hidden');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    const valorTotal = document.getElementById('valor_total');

    function calcularValor() {
        const inicio = dataInicio.value;
        const fim = dataFim.value;
        const espaco = espacoSelect.value;
        console.log(inicio, fim, espaco);

        if (!inicio || !fim || !espaco) return;

        const valorSemana = espaco.getAttribute('data-valor-semana');
        const valorFim = espaco.getAttribute('data-valor-fim');

       fetch('/calcular-valor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                data_inicio: inicio,
                data_fim: fim,
                espaco_id: espaco
            })
        })

    
        .then(response => response.json())
        .then(data => {
            valorTotal.value = `R$ ${parseFloat(data.total).toFixed(2).replace('.', ',')}`;
        });
    }

    espacoSelect.addEventListener('change', calcularValor);
    dataInicio.addEventListener('change', calcularValor);
    dataFim.addEventListener('change', calcularValor);
});


</script>

<script>
            $(document).ready(function () {
        $('#cliente_id').select2({
            placeholder: 'Selecione um Cliente',
            minimumInputLength: 3,
            ajax: {
                url: '{{ route('clientes.search') }}', // rota pra fazer busca
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // o que o usuário digitou
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(cliente) {
                            return { id: cliente.id, text: cliente.nome_razao_social };
                        })
                    };
                },
                cache: true
            },
            width: '100%',
             language: {
        noResults: function() {
            return "Nenhum resultado encontrado";
        },
        searching: function() {
            return "Buscando...";
        },
        inputTooShort: function (args) {
        var remainingChars = args.minimum - args.input.length;
        return 'Digite mais ' + remainingChars + ' caractere' + (remainingChars !== 1 ? 's' : '') + ' para buscar';
    },
        loadingMore: function() {
            return "Carregando mais resultados...";
        }
    }
        });
    });
</script>
@endsection

@section('css')
<style>
/* Estilos para o container do mapa */
#reservation_map_container {
    overflow-x: auto; /* Permite rolagem horizontal */
    -webkit-overflow-scrolling: touch; /* Melhora scroll em iOS */
    margin-top: 20px;
    padding-bottom: 10px; /* Espaço para a barra de rolagem não cobrir conteúdo */
    border: 1px solid #dee2e6; /* Borda sutil no container */
    border-radius: 0.25rem; /* Cantos arredondados */
    max-width: 100%; /* Garante que não ultrapasse o container pai */
}

/* Estilos básicos para a tabela do mapa */
.reservation-map-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Colunas de data com largura igual, melhor performance */
    min-width: 700px; /* Ajuste conforme necessário, baseado no número de dias e padding */
    /* border: 1px solid #dee2e6; */ /* Borda externa movida para o container */
}

.reservation-map-table th,
.reservation-map-table td {
    border: 1px solid #dee2e6;
    padding: 0.6rem 0.4rem; /* Ajuste padding (vertical / horizontal) */
    text-align: center;
    vertical-align: middle;
    font-size: 0.8rem; /* Tamanho base da fonte */
    height: 45px; /* Altura base da célula */
    position: relative;
    box-sizing: border-box; /* Inclui padding e borda na largura/altura */
}

/* Cabeçalhos */
.reservation-map-table th {
    background-color: #f8f9fa;
    font-weight: 600; /* Um pouco mais forte */
    white-space: nowrap; /* Não quebrar cabeçalhos de data/espaço */
    font-size: 0.75rem; /* Fonte ligeiramente menor para cabecalhos */
    position: sticky; /* Fixar cabeçalho ao rolar verticalmente */
    top: 0;
    z-index: 10;
}

/* Cabeçalho da coluna de Espaços */
.reservation-map-table th.space-header {
    text-align: left;
    min-width: 130px; /* Largura mínima para nome do espaço */
    width: 130px; /* Largura fixa pode ajudar no layout fixo */
    padding-left: 0.8rem; /* Mais espaço à esquerda */
    position: sticky; /* Fixar coluna de espaços ao rolar horizontalmente */
    left: 0;
    background-color: #f8f9fa; /* Manter fundo igual ao header */
    z-index: 11; /* Acima do header de data */
    border-right: 2px solid #ced4da; /* Separador mais visível */
}

/* Células de dados (nome do espaço) */
.reservation-map-table td.space-header {
    text-align: left;
    font-weight: 500;
    background-color: #ffffff; /* Fundo branco para diferenciar do header */
    position: sticky;
    left: 0;
    z-index: 5; /* Abaixo dos headers, mas acima das células de data */
    border-right: 2px solid #ced4da; /* Separador mais visível */
    /* Herdar min-width e width do cabeçalho para consistência */
    min-width: 130px;
    width: 130px;
    padding-left: 0.8rem;
}


/* Estilos para as células de data */
.date-cell {
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
    width: 45px;
}

.date-cell.available:hover {
    background-color: #e9f5e9;
}

.date-cell.booked {
    background-color: #f8d7da;
    color: #721c24;
    cursor: not-allowed;
    font-style: italic;
}
/* Adicionar um estilo visual mais claro para ocupado */
.date-cell.booked span {
    font-weight: bold;
    color: #dc3545;
}


.date-cell.selected {
    background-color: var(--green-1);
    color: white;
    font-weight: bold;
    box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.1); /* Contorno sutil */
}

.date-cell.selecting {
    background-color: #b8ffb8;
}

/* Estilos para os filtros */
#filter_button {
    margin-top: 5px; /* Pequeno ajuste de alinhamento */
}

/* Placeholder de feedback */
#selection_feedback {
    min-height: 1.5em; /* Evita que o layout salte quando a mensagem aparece/desaparece */
}

/* Estilos para o container do buffet */
.buffet-container {
    max-width: 100%;
    overflow-x: hidden; /* Evita overflow horizontal */
}

/* Melhorias para os cards do buffet */
.buffet-container .card {
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.buffet-container .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

.buffet-container .card-body {
    padding: 1rem;
}

/* Responsividade para os checkboxes do buffet */
.buffet-container .form-check {
    margin-bottom: 0.5rem;
    word-wrap: break-word;
}

.buffet-container .form-check-label {
    font-size: 0.9rem;
    line-height: 1.4;
    margin-left: 0.25rem;
}

/* --- Media Queries para Responsividade --- */

/* Telas Médias (Tablets) */
@media (max-width: 992px) {
    .reservation-map-table {
        min-width: 600px; /* Reduzir largura mínima */
    }
    .reservation-map-table th,
    .reservation-map-table td {
        padding: 0.5rem 0.3rem;
        font-size: 0.75rem;
        height: 40px;
    }
    .reservation-map-table th.space-header,
    .reservation-map-table td.space-header {
        min-width: 110px;
        width: 110px;
    }
     .date-cell {
        min-width: 35px;
    }
    
    /* Ajustes para o buffet em tablets */
    .buffet-container .form-check {
        margin-bottom: 0.75rem;
    }
}

/* Telas Pequenas (Celulares) */
@media (max-width: 767px) {
     .reservation-map-table {
        min-width: 500px; /* Reduzir mais a largura mínima */
    }
    .reservation-map-table th,
    .reservation-map-table td {
        padding: 0.4rem 0.2rem; /* Menos padding */
        font-size: 0.7rem; /* Fonte ainda menor */
        height: 35px; /* Células mais baixas */
    }
     .reservation-map-table th {
         font-size: 0.65rem; /* Cabeçalhos ainda menores */
     }

    .reservation-map-table th.space-header,
    .reservation-map-table td.space-header {
        min-width: 90px; /* Coluna de espaço mais estreita */
        width: 90px;
        font-size: 0.7rem; /* Ajustar fonte do nome do espaço */
        padding-left: 0.5rem;
    }
     .date-cell {
        min-width: 30px; /* Células de data mais estreitas */
    }
    
    /* Ajustes para o buffet em celulares */
    .buffet-container .card-header {
        padding: 0.5rem 0.75rem;
    }
    
    .buffet-container .card-body {
        padding: 0.75rem;
    }
    
    .buffet-container .form-check {
        margin-bottom: 0.5rem;
    }
    
    .buffet-container .form-check-label {
        font-size: 0.85rem;
    }
    
    /* Opcional: Esconder o texto 'X' e usar só fundo em telas muito pequenas */
    /*
    .date-cell.booked span {
        display: none;
    }
    */
}

/* Ajustes finos para telas muito pequenas (opcional) */
@media (max-width: 480px) {
    .reservation-map-table {
        min-width: 400px;
    }
    .reservation-map-table th.space-header,
    .reservation-map-table td.space-header {
        min-width: 75px;
        width: 75px;
        font-size: 0.65rem;
    }
     .date-cell {
        min-width: 28px;
    }
     .reservation-map-table th,
    .reservation-map-table td {
         height: 30px;
         padding: 0.3rem 0.1rem;
     }
     
     /* Ajustes para o buffet em telas muito pequenas */
     .buffet-container .card-header {
         padding: 0.5rem;
     }
     
     .buffet-container .card-body {
         padding: 0.5rem;
     }
     
     .buffet-container .form-check-label {
         font-size: 0.8rem;
     }
}

</style>
@endsection

