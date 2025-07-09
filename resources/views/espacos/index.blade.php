@extends('adminlte::page')

@section('title', 'Espaços')

@section('content_header')
    <h5>Lista de Espacos para Aluguel</h5>
    <hr>
@stop

@section('content')
<div class="row mb-3">
    <div class="col d-flex justify-content-start">
        <a href="{{ route('preferencias') }}" class="btn btn-success new">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="col d-flex justify-content-end">
      <button class="btn btn-success new" data-bs-toggle="modal" data-bs-target="#createEspacoModal">
          <i class="fas fa-plus"></i> Novo Espaço
      </button>
    </div>
</div>
    

    @component('components.data-table', [
        'responsive' => [
            ['responsivePriority' => 1, 'targets' => 0],
            ['responsivePriority' => 2, 'targets' => 1],
            ['responsivePriority' => 3, 'targets' => 2],
            ['responsivePriority' => 4, 'targets' => -1],
        ],
        'itemsPerPage' => 10,
        'showTotal' => false,
        'valueColumnIndex' => 1,
    ])
            <thead class="bg-primary text-white">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Valor na Semana</th>
                    <th>Valor no Final de semana</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($espacos as $espaco)
                    <tr>
                        <td>{{ $espaco->id }}</td>
                        <td>{{ $espaco->nome }}</td>
                        <td>
                            R$ {{ $espaco->valor_semana }} {{ $espaco->capela ? '(Batizado)' : '' }}
                        </td>
                        <td>
                            R$ {{ $espaco->valor_fim }} {{ $espaco->capela ? '(Casamento)' : '' }}
                        </td>
            
                        <td>

                          <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                          data-target="#editEspacoModal{{ $espaco->id }}">
                          ✏️
                          </button>

                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                            data-target="#deleteEspacoModal{{ $espaco->id }}">
                            🗑️
                        </button>
                        </td>
                    </tr>
                    @include('espacos.modals._edit', ['espaco' => $espaco])
                    @include('espacos.modals._delete', ['espaco' => $espaco])
                @endforeach
            </tbody>
    @endcomponent
    @include('espacos.modals._create')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('[debug] DOM carregado');

        const atualizarTextoLabels = () => {
            const capelaCheckbox = document.getElementById('capela');
            const labelSemana = document.getElementById('labelSemana');
            const labelFim = document.getElementById('labelFim');

            console.log('[debug] checkbox checked?', capelaCheckbox.checked);

            if (capelaCheckbox.checked) {
                console.log('cheguei')
                labelSemana.textContent = 'Valor Batizado:';
                labelFim.textContent = 'Valor Casamento:';
            } else {
                labelSemana.textContent = 'Valor na semana (Seg a Qui):';
                labelFim.textContent = 'Valor no Fim de semana (Sex a Dom):';
            }
        };

        const modal = document.getElementById('createEspacoModal');
        if (modal) {
            $('#createEspacoModal').on('shown.bs.modal', function () {
                console.log('[debug] Modal exibido');
                atualizarTextoLabels();
            });

            $('#capela').on('change', function () {
                atualizarTextoLabels();
            });
        }

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('[debug] DOM carregado');

        // Para cada modal de edição
        document.querySelectorAll('[id^="editEspacoModal"]').forEach(function (modal) {
            const id = modal.id.replace('editEspacoModal', '');

            const capelaCheckbox = document.getElementById('capela' + id);
            const labelSemana = document.getElementById('labelSemana' + id);
            const labelFim = document.getElementById('labelFim' + id);

            const atualizarTextoLabels = () => {
                if (!capelaCheckbox || !labelSemana || !labelFim) return;

                console.log(`[debug edit ${id}] checkbox checked?`, capelaCheckbox.checked);

                if (capelaCheckbox.checked) {
                    labelSemana.textContent = 'Valor Batizado:';
                    labelFim.textContent = 'Valor Casamento:';
                } else {
                    labelSemana.textContent = 'Valor na semana (Seg a Qui):';
                    labelFim.textContent = 'Valor no Fim de semana (Sex a Dom):';
                }
            };
            atualizarTextoLabels();
            // Usa jQuery para escutar o evento do Bootstrap 5
            $('#editEspacoModal' + id).on('shown.bs.modal', function () {
                console.log(`[debug edit ${id}] Modal exibido`);
                atualizarTextoLabels();
            });

            // Escuta mudanças no checkbox
            if (capelaCheckbox) {
                capelaCheckbox.addEventListener('change', atualizarTextoLabels);
            }
        });
    });
</script>

@stop

@section('js')

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
  top: 0; left: 0; right: 0; bottom: 0;
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

.switch-slide input:checked + .slider-slide {
  background-color: var(--green-1);
}

.switch-slide input:checked + .slider-slide:before {
  transform: translateX(24px);
}
</style>
