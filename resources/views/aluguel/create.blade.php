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
            </ul>

            <div class="tab-content mt-3" id="aluguelTabsContent">
                {{-- Aba 1: Informações da Reserva --}}
                 <!-- Campo do período -->
                {{-- <div class="form-group row" id="campoPeriodo">
                  <label class="col-md-3 label-control" for="periodo">* Data</label>
                  <div class="col-md-4">
                      <input type="text" class="form-control" id="periodo" name="periodo"
                          value="{{ old('periodo', isset($reserva) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '') }}" />
                  </div>
                </div>

                <input type="hidden" name="data_checkin" id="data_checkin" value="{{ old('data_checkin', $reserva->data_checkin ?? '') }}">
                <input type="hidden" name="data_checkout" id="data_checkout" value="{{ old('data_checkout', $reserva->data_checkout ?? '') }}"> --}}

                <!-- Campo de cliente -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
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
                              <div><input class="form-control" required="required" type="text" name="nome_razao_social" id="nomeRazaoSocial" autocomplete="off"></div>
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
                                    <input type="text" class="form-control" id="cpfCnpj" name="cpf_cnpj" required>
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

   
                <!-- Campo de itens extras-->
                <div class="form-group row mb-3">
                    <label for="itens" class="col-md-3 label-control">Itens Adicionais:</label>
                    <div class="col-md-6">
                        <select class="form-control select2" id="itens" name="itens[]" multiple="multiple">
                            @php
                                $selecteditens = isset($aluguel) ? $aluguel->adicionais->pluck('id')->toArray() : [];
                            @endphp
                            @foreach ($itens as $adicional)
                                <option value="{{ $adicional->id }}"
                                    {{ in_array($adicional->id, $selecteditens) ? 'selected' : '' }}>
                                    {{ $adicional->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                    <div class="form-group row">
                        <label for="observacoes" class="col-md-3 label-control">Observações extras:</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $tarifa->observacoes ?? '') }}</textarea>
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
                        <label class="col-md-3 label-control">Itens de Buffet:</label>
                        <div class="col">
                            @foreach ($buffetItens as $item)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="buffet_itens[]" value="{{ $item->id }}"
                                            class="form-check-input buffet-item"
                                            data-valor="{{ $item->valor_unitario }}"
                                            {{ isset($aluguel) && $aluguel->buffetItens->contains($item->id) ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            {{ $item->nome }} - R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 label-control">Total Buffet Estimado:</label>
                        <div class="col-md-3">
                            <input type="text" id="total_buffet" class="form-control" readonly>
                        </div>
                    </div>
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
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('ativoSwitch');
        const label = document.getElementById('ativoLabel');

        switchInput.addEventListener('change', function () {
            label.textContent = this.checked ? 'Sim' : 'Não';
        });
    });
</script>

<script>
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
    function calcularBuffet() {
        const numeroPessoas = parseInt(document.getElementById('numero_pessoas_buffet').value) || 0;
        let total = 0;
        document.querySelectorAll('.buffet-item:checked').forEach(item => {
            const valor = parseFloat(item.dataset.valor);
            total += valor * numeroPessoas;
        });
        document.getElementById('total_buffet').value = "R$ " + total.toFixed(2).replace('.', ',');
    }

    document.getElementById('numero_pessoas_buffet').addEventListener('input', calcularBuffet);
    document.querySelectorAll('.buffet-item').forEach(item => item.addEventListener('change', calcularBuffet));

    window.addEventListener('DOMContentLoaded', calcularBuffet);
</script>
@endsection