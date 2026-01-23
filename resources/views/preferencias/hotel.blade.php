@extends('adminlte::page')

@section('title', 'Preferências do Hotel')

@section('content_header')
    <h5>Configurações do Hotel</h5>
@stop

@section('content')
    {{-- ALERTAS DE SUCESSO/ERRO --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- CARD 1: PREFERÊNCIAS GERAIS --}}
    @php
        $checkin = optional(
            $preferencia?->checkin instanceof \Carbon\Carbon
                ? $preferencia->checkin
                : \Carbon\Carbon::parse($preferencia?->checkin),
        );
        $checkout = optional(
            $preferencia?->checkout instanceof \Carbon\Carbon
                ? $preferencia->checkout
                : \Carbon\Carbon::parse($preferencia?->checkout),
        );

        $checkinHora = (int) (old('checkin_hora') ?? $checkin->format('H'));
        $checkinMinuto = (int) (old('checkin_minuto') ?? $checkin->format('i'));

        $checkoutHora = (int) (old('checkout_hora') ?? $checkout->format('H'));
        $checkoutMinuto = (int) (old('checkout_minuto') ?? $checkout->format('i'));
    @endphp
    
    <div class="row mb-3">
      <div class="col">
          <a href="{{ route('preferencias') }}" class="btn btn-secondary">
              <i class="fas fa-sync"></i> Atualizar Página
          </a>
      </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h3 class="card-title"><i class="fas fa-cogs mr-2"></i> Preferências Gerais</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('preferencias.store') }}">
                @csrf

                {{-- ⌛ Checkout --}}
                <div class="form-group row align-items-center">
                    <label class="col-sm-2 col-form-label">Horário de Checkout</label>
                    <div class="col-sm-10 d-flex align-items-center">
                        <select name="checkout_hora" class="form-control w-auto mr-2">
                            @for ($h = 0; $h < 24; $h++)
                                <option value="{{ $h }}" {{ $checkoutHora === $h ? 'selected' : '' }}>
                                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        :
                        <select name="checkout_minuto" class="form-control w-auto ml-2">
                            @for ($m = 0; $m < 60; $m += 10)
                                <option value="{{ $m }}" {{ $checkoutMinuto === $m ? 'selected' : '' }}>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- ⏰ Check-in --}}
                <div class="form-group row align-items-center">
                    <label class="col-sm-2 col-form-label">Horário de Check-in</label>
                    <div class="col-sm-10 d-flex align-items-center">
                        <select name="checkin_hora" class="form-control w-auto mr-2">
                            @for ($h = 0; $h < 24; $h++)
                                <option value="{{ $h }}" {{ $checkinHora === $h ? 'selected' : '' }}>
                                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        :
                        <select name="checkin_minuto" class="form-control w-auto ml-2">
                            @for ($m = 0; $m < 60; $m += 10)
                                <option value="{{ $m }}" {{ $checkinMinuto === $m ? 'selected' : '' }}>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- 🧼 Limpeza com switch Bootstrap --}}
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">Limpeza de Quarto</label>
                    <div class="col-md-10 d-flex align-items-center">
                        <label class="switch-slide mb-0">
                            <input type="hidden" name="limpeza_quarto" value="0">
                            <input type="checkbox" id="limpeza_quarto" name="limpeza_quarto" value="1"
                                @checked(old('limpeza_quarto', $preferencia?->limpeza_quarto ?? true))>
                            <span class="slider-slide"></span>
                        </label>
                    </div>
                </div>

                {{-- 💸 Valor da Diária --}}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Cálculo de diárias por</label>
                    <div class="col-sm-10 d-flex align-items-center">
                        <div class="form-check mr-3">
                            <input class="form-check-input" type="radio" name="valor_diaria" id="radio1" value="diaria"
                                {{ old('valor_diaria', $preferencia?->valor_diaria) == 'diaria' ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio1">Valor Diária</label>
                        </div>
                        <div class="form-check mr-3">
                            <input class="form-check-input" type="radio" name="valor_diaria" id="radio2" value="totalDiaria"
                                {{ old('valor_diaria', $preferencia?->valor_diaria) == 'totalDiaria' ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio2">Valor Total das Diárias</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="valor_diaria" id="radio3" value="tarifario"
                                {{ old('valor_diaria', $preferencia?->valor_diaria) == 'tarifario' ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio3">Somente Tarifário</label>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- 🐾 Taxas PET --}}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Taxas Pet (Diária)</label>
                    <div class="col-sm-10">
                        <div class="row">
                            <div class="col-md-4" style="display: none">
                                <label class="small text-muted font-weight-bold">Pequeno</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="text" name="valor_pet_pequeno" class="form-control money" 
                                        placeholder="0,00"
                                        value="{{ old('valor_pet_pequeno', number_format($preferencia->valor_pet_pequeno ?? 0, 2, ',', '.')) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted font-weight-bold">Adicional</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="text" name="valor_pet_medio" class="form-control money" 
                                        placeholder="0,00"
                                        value="{{ old('valor_pet_medio', number_format($preferencia->valor_pet_medio ?? 0, 2, ',', '.')) }}">
                                </div>
                            </div>
                            <div class="col-md-4" style="display: none">
                                <label class="small text-muted font-weight-bold">Grande</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="text" name="valor_pet_grande" class="form-control money" 
                                        placeholder="0,00"
                                        value="{{ old('valor_pet_grande', number_format($preferencia->valor_pet_grande ?? 0, 2, ',', '.')) }}">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Estes valores serão sugeridos automaticamente ao adicionar pets em uma reserva.</small>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Preferências</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CARD 2: TEMPORADAS --}}
    <div class="card shadow">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-calendar-alt mr-2"></i> Temporadas / Alta Estação</h3>
            <button class="btn btn-sm btn-dark" onclick="abrirModalTemporada()">
                <i class="fas fa-plus"></i> Nova Temporada
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nome</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th class="text-center" width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($temporadas ?? [] as $temp)
                            <tr>
                                <td><strong>{{ $temp->nome }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($temp->data_inicio)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($temp->data_fim)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-primary mr-1" 
                                        onclick='editarTemporada(@json($temp))' title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('temporadas.destroy', $temp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta temporada?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> Nenhuma temporada cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TEMPORADA --}}
    <div class="modal fade" id="modalTemporada" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formTemporada" method="POST" action="{{ route('temporadas.store') }}">
                    @csrf
                    <div id="method_put"></div> {{-- Placeholder para @method('PUT') --}}
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitulo">Nova Temporada</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome da Temporada</label>
                            <input type="text" name="nome" id="temp_nome" class="form-control" placeholder="Ex: Carnaval, Alta Temporada..." required>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Data Início</label>
                                    <input type="date" name="data_inicio" id="temp_inicio" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Data Fim</label>
                                    <input type="date" name="data_fim" id="temp_fim" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .switch-slide {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch-slide input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-slide {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider-slide:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .switch-slide input:checked+.slider-slide {
            background-color: var(--green-1);
        }

        .switch-slide input:checked+.slider-slide:before {
            transform: translateX(24px);
        }
        
        /* Ajuste de cor global */
        :root { --green-1: #28a745; }
    </style>
@stop

@section('js')
    {{-- Importando JQuery Mask para funcionar a máscara de moeda nos campos novos --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        $(document).ready(function(){
            // Aplica a máscara de dinheiro nos campos de valor pet
            $('.money').mask('#.##0,00', {reverse: true});
        });

        // Função auxiliar para abrir o modal compatível com BS4 e BS5
        function showModal(modalId) {
            var modalEl = document.getElementById(modalId);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                // Bootstrap 5
                var myModal = new bootstrap.Modal(modalEl);
                myModal.show();
            } else {
                // Bootstrap 4 / jQuery
                $('#' + modalId).modal('show');
            }
        }

        function abrirModalTemporada() {
            // Reseta para modo Criação
            document.getElementById('formTemporada').action = "{{ route('temporadas.store') }}";
            document.getElementById('method_put').innerHTML = "";
            document.getElementById('modalTitulo').innerText = "Nova Temporada";
            
            document.getElementById('temp_nome').value = "";
            document.getElementById('temp_inicio').value = "";
            document.getElementById('temp_fim').value = "";
            
            // Chama o modal com a correção
            showModal('modalTemporada');
        }

        function editarTemporada(temporada) {
            // Configura para modo Edição
            let urlUpdate = "{{ route('temporadas.update', ':id') }}";
            urlUpdate = urlUpdate.replace(':id', temporada.id);
            
            document.getElementById('formTemporada').action = urlUpdate;
            // Adiciona o input hidden para o método PUT
            document.getElementById('method_put').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('modalTitulo').innerText = "Editar Temporada";
            
            // Preenche os dados
            document.getElementById('temp_nome').value = temporada.nome;
            document.getElementById('temp_inicio').value = temporada.data_inicio;
            document.getElementById('temp_fim').value = temporada.data_fim;
            
            // Chama o modal com a correção (Aqui estava o erro)
            showModal('modalTemporada');
        }
    </script>
@stop