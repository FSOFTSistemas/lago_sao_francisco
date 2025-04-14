@extends('adminlte::page')

@section('title', isset($reserva) ? 'Editar Reserva' : 'Cadastrar Reserva')

@section('content_header')
    <h1>{{ isset($reserva) ? 'Editar Reserva' : 'Cadastrar Reserva' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                {{ isset($reserva) ? 'Editar informações do Reserva' : 'Preencha os dados do novo Reserva' }}</h3>
        </div>
        <div class="card-body">
            <form id="createReservaForm" action="{{ isset($reserva) ? route('reserva.update', $reserva->id) : route('reserva.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if (isset($reserva))
                    @method('PUT')
                @endif

                <div class="form-group row" id="campoSituacao">
                  <label class="col-md-3 label-control"><strong>* Situação</strong></label>
                  <div class="situacao-options">
                    
                      <label class="radio-option">
                          <input type="radio" name="situacao" value="pre-reserva"
                              @checked(old('situacao', $reserva->situacao ?? '') === 'pre-reserva')>
                          <span class="badge badge-warning">pré-reservar</span>
                      </label>
              
                      <label class="radio-option">
                          <input type="radio" name="situacao" value="reserva"
                              @checked(old('situacao', $reserva->situacao ?? '') === 'reserva')>
                          <span class="badge badge-primary">reservar</span>
                      </label>
              
                      <label class="radio-option">
                          <input type="radio" name="situacao" value="hospedado"
                              @checked(old('situacao', $reserva->situacao ?? '') === 'hospedado')>
                          <span class="badge badge-danger">hospedar</span>
                      </label>
              
                      <label class="radio-option">
                          <input type="radio" name="situacao" value="bloqueado"
                              @checked(old('situacao', $reserva->situacao ?? '') === 'bloqueado')>
                          <span class="badge badge-dark">bloquear datas</span>
                      </label>
                  </div>
              </div>
              
              
                
                <div>
                  <hr>
                </div>

                <div class="form-group row">
                  <label for="hospede_id" class="col-md-3 label-control">Hóspede</label>
                  <div class="col-sm-4">
                    <select class="form-control select2" name="hospede_id" id="hospede_id">
                        <option value="">Selecione um hóspede</option>
                        @foreach($hospedes as $hospede)
                            <option value="{{ $hospede->id }}" 
                                {{ old('hospede_id', $reserva->hospede_id ?? '') == $hospede->id ? 'selected' : '' }}>
                                {{ $hospede->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                  <div class="col-sm-2">
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCadastrarHospede">
                          <i class="fas fa-user-plus"></i>
                      </button>
                  </div>
                </div>




                <div class="form-group row" id="campoPeriodo">
                  <label class="col-md-3 label-control" for="periodo">* Período</label>
                  <div class="col-md-4">
                      <input type="text" class="form-control" id="periodo" name="periodo"
                          value="{{ old('periodo', isset($reserva) ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '') }}" />
                  </div>
              </div>
              
              <input type="hidden" name="data_checkin" id="data_checkin" value="{{ old('data_checkin', $reserva->data_checkin ?? '') }}">
              <input type="hidden" name="data_checkout" id="data_checkout" value="{{ old('data_checkout', $reserva->data_checkout ?? '') }}">
              
              


              <div class="form-group row" id="campoQuarto">
                <label class="col-md-3 label-control" for="quarto">* Quarto</label>
                <div class="col-md-4">
                    <select class="form-control select2" id="quarto" name="quarto_id">
                        <option value="">Selecione um quarto</option>
                        @foreach ($quartos as $quarto)
                            <option value="{{ $quarto->id }}" {{ old('quarto_id', $reserva->quarto_id ?? '') == $quarto->id ? 'selected' : '' }}>
                                {{ $quarto->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>


                
                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="profissao">* Valor da diária:</label>
                  <div class="col-md-4">
                    <div><input class="form-control"  type="number"  name="valor_diaria" id="valor_diaria" value="{{ old('valor_diaria', $reserva->valor_diaria ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"><strong>Nº hóspedes</strong></label>
                  <div class="row col-md-6">
                      <div class="col-md-3">
                          <label for="n_adultos">* Nº adultos</label>
                          <input type="number" name="n_adultos" id="n_adultos" class="form-control"
                              value="{{ old('n_adultos', $reserva->n_adultos ?? 1) }}" min="1">
                      </div>
              
                      <div class="col-md-3">
                          <label for="n_criancas">* Nº crianças</label>
                          <input type="number" name="n_criancas" id="n_criancas" class="form-control"
                              value="{{ old('n_criancas', $reserva->n_criancas ?? 0) }}" min="0">
                      </div>
                  </div>
                </div>

                <div class="form-group row" id="campoObservacoes">
                  <label for="observacao" class="col-md-3 label-control" >Observações</label>
                  <div class="col-md-9">
                    <textarea class="form-control" name="observacao" rows="3">{{ old('observacao', $reserva->observacao ?? '') }}</textarea>
                  </div>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-danger" data-toggle="modal"
                    data-target="#deleteReservaModal{{ $reserva->id }}">
                    Excluir 🗑️
                    </button>
                    <a href="{{ route('reserva.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($reserva) ? 'Atualizar Reserva' : 'Adicionar Reserva' }}</button>
                </div>
              </form>    
            </div>    
          </div>    
          <!-- Modal de Cadastro de Hóspede -->
          <div class="modal fade" id="modalCadastrarHospede" tabindex="-1" role="dialog" aria-labelledby="modalHospedeLabel" aria-hidden="true">
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
                            <label class="col-md-2 label-control"  for="nome">* Nome completo:</label>
                            <div class="col-md-6">
                              <div><input class="form-control" required="required" type="text" name="nome" id="nome" autocomplete="off"></div>
                            </div>
                        </div>
          
                        <div class="form-group row">
                            <label class="col-md-2 label-control"  for="email">Email:</label>
                            <div class="col-md-6">
                              <div><input class="form-control" type="email" name="email" id="email"></div>
                            </div>
                          </div>
      
                          <div class="form-group row">
                            <label class="col-md-2 label-control" for="telefone">Telefone:</label>
                            <div class="col-md-6">
                              <div><input class="form-control"  type="tel" name="telefone" id="telefone"></div>
                            </div>
                          </div>
                        </div>
      
      
                        <div class="modal-footer">
                            <a href="{{route('hospede.create')}}">Cadastro Completo</a>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>
          </div>


          



          <div class="dicas">
          <div id="quadroDicas" class="card shadow-sm transition-all" >
            <div class="card-body pb-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-uppercase text-muted" style="letter-spacing: 1px;">DICAS</h6>
                    <button id="btnToggleDicas" class="btn btn-sm text-muted p-0" style="font-size: 18px;">
                        <i id="iconeToggleDicas" class="fas fa-minus"></i>
                    </button>
                </div>

                <div id="conteudoDicas">
                    <div id="textoDica" class="text-dark mb-3">
                    </div>

                    <div class="d-flex justify-content-center mb-2">
                        <div id="indicadores" class="d-flex gap-1"></div>
                    </div>
                </div>
            </div>
          </div>
          </div>
          @include('reserva.modals._delete', ['reserva' => $reserva])
    @include('components.endereco-modal')
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
  .card {
    width: 80%
  }
  .label-control{
    text-align: right
  }

  .card-footer{
    text-align: right
  }
  
  .situacao-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 1rem;
}

.radio-option input[type="radio"] {
    cursor: pointer;
}

input[type="number"] {
    max-width: 100%;
    padding: 6px 10px;
}

.form-group .row > div {
    margin-right: 10px;
}

.indicador {
        transition: background-color 0.3s ease;
    }

#conteudoDicas {
    overflow: hidden;
    transition: max-height 0.5s ease;
}

#conteudoDicas.collapsed {
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
}

#delbtn {
  t: left
}
  </style>
  <style>
    @media (max-width: 768px) {
      .label-control{
        text-align: start
      }
    }
  </style>
@stop

@section('js')
<script src="{{ asset('js/endereco.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></scrip>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "selecione...",
            allowClear: true,
            width: '100%'
        });
    });

</script>
<script>
  $(document).ready(function() {
    $('#telefone').mask('(00) 00000-0000');
  })
</script>
<script>
  $(document).ready(function() {
    $('#periodo').daterangepicker({
      locale: {
        format: 'DD/MM/YYYY', 
        applyLabel: 'Aplicar',
        cancelLabel: 'Cancelar',
        fromLabel: 'Início',
        toLabel: 'Fim',
        customRangeLabel: 'Personalizado'
      },
      opens: 'center', 
    }, function(start, end) {
      $('input[name="data_checkin"]').val(start.format('YYYY-MM-DD'));
      $('input[name="data_checkout"]').val(end.format('YYYY-MM-DD'));
      
      $('#periodo').val(start.format('DD/MM/YYYY') + ' a ' + end.format('DD/MM/YYYY'));
    });

    let initialStartDate = $('input[name="data_checkin"]').val();
    let initialEndDate = $('input[name="data_checkout"]').val();

    if (initialStartDate && initialEndDate) {
      $('#periodo').data('daterangepicker').setStartDate(moment(initialStartDate, 'YYYY-MM-DD'));
      $('#periodo').data('daterangepicker').setEndDate(moment(initialEndDate, 'YYYY-MM-DD'));
      $('#periodo').val(moment(initialStartDate, 'YYYY-MM-DD').format('DD/MM/YYYY') + ' a ' + moment(initialEndDate, 'YYYY-MM-DD').format('DD/MM/YYYY'));
    }
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      const modalHospede = document.getElementById('modalHospede');
      modalHospede.addEventListener('hidden.bs.modal', function () {
          location.reload();
      });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      const dicas = [
          {
              texto: "<strong>Situação:</strong> É o momento em que a reserva se encontra, ela pode estar como: <i>pré-reservado, reservado, hospedado, ou com bloqueio de data.</i> "
          },
          {
              texto: "<strong>Nº hóspedes:</strong> É o número de hóspedes da reserva, além de informar quantas pessoas estão no quarto, também serve para calcular o valor das diárias"
          },
          {
              texto: "<strong>Período:</strong> Campo que informa o período da reserva, basta escolher a data de entrada e saída."
          },
          {
              texto: "<strong>Hóspede:</strong> Este é o campo onde você escolhe ou adiciona o hóspede responsável pela reserva. <i>OBS: Você poderá adicionar outros hóspedes após criar a reserva.</i>"
          },
          {
              texto: "<strong> Observação:</strong> Campo onde você pode colocar qualquer informação sobre a reserva."
          },
      ];

      let dicaAtual = 0;
        const textoDica = document.getElementById('textoDica');
        const indicadores = document.getElementById('indicadores');
        const conteudoDicas = document.getElementById('conteudoDicas');
        const btnToggle = document.getElementById('btnToggleDicas');
        const iconeToggle = document.getElementById('iconeToggleDicas');

        dicas.forEach((_, index) => {
            const span = document.createElement('span');
            span.classList.add('indicador');
            span.style.width = '20px';
            span.style.height = '4px';
            span.style.margin = '0 3px';
            span.style.borderRadius = '2px';
            span.style.backgroundColor = index === 0 ? '#333' : '#e0e0e0';
            indicadores.appendChild(span);
        });

        function trocarDica() {
            $(textoDica).fadeOut(300, () => {
                textoDica.innerHTML = dicas[dicaAtual].texto;
                atualizarIndicadores();
                $(textoDica).fadeIn(300);
            });
            dicaAtual = (dicaAtual + 1) % dicas.length;
        }

        function atualizarIndicadores() {
            [...indicadores.children].forEach((el, idx) => {
                el.style.backgroundColor = idx === dicaAtual ? '#333' : '#e0e0e0';
            });
        }

        trocarDica();
        setInterval(trocarDica, 8000);

        let expandido = true;

        btnToggle.addEventListener('click', function () {
        expandido = !expandido;

        if (expandido) {
            conteudoDicas.classList.remove('collapsed');
            iconeToggle.classList = 'fas fa-minus';
        } else {
            conteudoDicas.classList.add('collapsed');
            iconeToggle.classList = 'fas fa-plus';
        }
    });
    });
</script>

<script>
  $(document).ready(function () {
    const hospedeBloqueadoId = "{{ $hospedeBloqueado->id ?? '' }}";

    function atualizarCampos() {
      const situacao = $('input[name="situacao"]:checked').val();

      if (situacao === 'bloqueado') {
        $('.form-group').each(function () {
          const id = $(this).attr('id');
          if (['campoPeriodo', 'campoQuarto', 'campoObservacoes', 'campoSituacao'].includes(id)) {
            $(this).slideDown(200);
          } else {
            $(this).slideUp(200);
          }
        });

        $('#hospede_id').val(hospedeBloqueadoId).prop('readyonly', true);

        $('#valor_diaria').val(0);
      } else {
        $('.form-group').slideDown(200);
        $('#hospede_id').prop('disabled', false).val('');
        $('#valor_diaria').val('');
      }
    }

    $('input[name="situacao"]').on('change', atualizarCampos);

    atualizarCampos();
  });
</script>


@if(session('success'))
<script>
    Swal.fire({
        title: 'Sucesso!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'OK'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        title: 'Erro!',
        text: "{{ session('error') }}",
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
@endif

@stop