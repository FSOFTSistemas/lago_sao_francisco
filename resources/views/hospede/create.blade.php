@extends('adminlte::page')

@section('title', isset($hospede) ? 'Atualizar Hóspede' : 'Cadastrar Hóspede')

@section('content_header')
    <h1>{{ isset($hospede) ? 'Atualizar Hóspede' : 'Cadastrar Hóspede' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header green bg-primary text-white">
            <h3 class="card-title">
                {{ isset($hospede) ? 'Preencha os dados atualizados do Hóspede' : 'Preencha os dados do novo Hóspede' }}</h3>
        </div>
        <div class="card-body">
            <form id="createHospedeForm" action="{{ isset($hospede) ? route('hospede.update', $hospede->id) : route('hospede.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if (isset($hospede))
                    @method('PUT')
                @endif

                <!-- Avatar -->
                <div class="row">
                  <div class="col-md-3 d-flex justify-content-end">
                      <div class="img-upload-box mx-auto">
                        <img id="img-upload-placeholder" 
                        src="{{ isset($hospede) && $hospede->avatar ? asset('storage/' . $hospede->avatar) : asset('avatar.svg') }}" 
                        style="width: 100%; border-radius: 50%;">
                      </div>
                  </div>

                  <div class="col-md-9 d-flex align-items-center">
                      <input type="file" id="img-upload" name="avatar" class="d-none" accept="image/*">
                      <button type="button" class="btn btn-primary" id="select-image">
                          Selecione uma foto (Proporção: 500 x 500)
                      </button>
                  </div>
                </div>
                <input type="hidden" name="avatar_base64" id="avatar_base64">


              
              <!-- Modal de Crop -->
              <div class="modal fade" id="cropImageModal" tabindex="-1" aria-hidden="true"  data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Recortar Imagem</h5>
                      <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body text-center">
                      <img id="cropper-image" style="max-width: 100%;">
                    </div>
                    <div class="modal-footer">
                      <button type="button" id="crop-btn" class="btn btn-success">Recortar</button>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                  </div>
                </div>
              </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="nome">* Nome completo:</label>
                  <div class="col-md-9">
                    <div><input class="form-control" required="required" type="text" name="nome" id="nome" value="{{ old('nome', $hospede->nome ?? '') }}" autocomplete="off"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="email">Email:</label>
                  <div class="col-md-9">
                    <div><input class="form-control" type="email" name="email" id="email" value="{{ old('email', $hospede->email ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="passaporte">Passaporte:</label>
                  <div class="col-md-3">
                    <div><input class="form-control"  type="text" name="passaporte" id="passaporte" value="{{ old('passaporte', $hospede->passaporte ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="nascimento">Data de nascimento:</label>
                  <div class="col-md-2">
                    <div><input class="form-control"  type="date" name="nascimento" id="nascimento" value="{{ old('nascimento', $hospede->nascimento ?? '') }}"></div>
                  </div>
                </div>
                


                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="sexo">Sexo</label>
                  <div class="col-md-2">
                    <div>
                      <select class="form-control select2" id="sexo" name="sexo">
                        <option value="masculino" {{ old('sexo', $hospede->sexo ?? '') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                        <option value="feminino" {{ old('sexo', $hospede->sexo ?? '') == 'feminino' ? 'selected' : '' }}>Feminino</option>
                        <option value="outro" {{ old('sexo', $hospede->sexo ?? '') == 'outro' ? 'selected' : '' }}>Outro</option>
                      </select>
                    </div>
                  </div>
                </div>
              
                <div class="form-group row">
                  <label class="col-md-3 label-control"  for="profissao">Profissão:</label>
                  <div class="col-md-9">
                    <div><input class="form-control"  type="text" name="profissao" id="profissao" value="{{ old('profissao', $hospede->profissao ?? '') }}"></div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="observacoes" class="col-md-3 label-control" >Observações</label>
                  <div class="col-md-9">
                    <textarea class="form-control" name="observacoes" rows="3">{{ old('observacoes', $hospede->observacoes ?? '') }}</textarea>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 form-label d-block label-control">Ativo?</label>
                  <div class="form-check form-switch">
                      <input type="hidden" name="status" value="0">
                      <input
                          class="form-check-input"
                          type="checkbox"
                          id="ativoSwitch"
                          name="status"
                          value="1"
                          {{ old('status', $quarto->status ?? true) ? 'checked' : '' }}>
                      <label class="form-check-label ms-2" for="ativoSwitch" id="ativoLabel">
                          {{ old('status', $quarto->status ?? true) ? 'Ativo' : 'Inativo' }}
                      </label>
                  </div>
                </div>

                <div>
                  <h4 class="form-section"><i class="fa-regular fa-user"></i> Contato</h4>
                  <hr>
                </div>

                <div class="form-group row">
                  <label class="col-md-3 label-control" for="telefone">Telefone:</label>
                  <div class="col-md-3">
                    <div><input class="form-control"  type="tel" name="telefone" id="telefone" value="{{ old('telefone', $hospede->telefone ?? '') }}"></div>
                  </div>
                </div>


                <div class="form-group row">
                  <label class="col-md-3 label-control" for="endereco_id">Endereço:</label>
                  <div class="col-md-3">
                      <select class="form-control select2" id="endereco_id" name="endereco_id">
                          <option value="">Selecione</option>
                          @foreach ($endereco as $item)
                              <option value="{{ $item->id }}" 
                                  {{ old('endereco_id', $hospede->endereco_id ?? '') == $item->id ? 'selected' : '' }}>
                                  {{ $item->logradouro }}, {{ $item->numero }}
                              </option>
                          @endforeach
                      </select>
                  </div>
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enderecoModal">
                      <i class="fas fa-plus"></i> Novo Endereço
                  </button>
              </div>

                <div class="card-footer">
                    <a href="{{ route('hospede.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">{{ isset($hospede) ? 'Atualizar Hóspede' : 'Adicionar Hóspede' }}</button>
                </div>
            </form>
        </div>
    </div>


    @include('components.endereco-modal')
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet"/>
<style>
  .img-upload-box {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #f0f0f0; 
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.img-upload-box img {
    object-fit: cover;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

  </style>
@stop

@section('js')
<script src="{{ asset('js/endereco.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.min.js"></script>

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
  document.addEventListener('DOMContentLoaded', function () {
      const switchInput = document.getElementById('ativoSwitch');
      const label = document.getElementById('ativoLabel');
      label.textContent = switchInput.checked ? 'Ativo' : 'Inativo';
      switchInput.addEventListener('change', function () {
          label.textContent = this.checked ? 'Ativo' : 'Inativo';
      });
  });
</script>
</script>
<script>
  $(document).ready(function() {
    $('#cep').mask('00000-000');
    $('#telefone').mask('(00) 00000-0000');
  })
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      const input = document.getElementById('img-upload');
      const btn = document.getElementById('select-image');
      const cropperImage = document.getElementById('cropper-image');
      const preview = document.getElementById('img-upload-placeholder');
      const modalEl = document.getElementById('cropImageModal');
      const cropBtn = document.getElementById('crop-btn');
      const base64Input = document.getElementById('avatar_base64'); 
      const modal = new bootstrap.Modal(modalEl);
      let cropper;
  
      btn.addEventListener('click', () => input.click());
  
      input.addEventListener('change', function (e) {
          const file = e.target.files[0];
          if (!file) return;
  
          const reader = new FileReader();
          reader.onload = function (event) {
              cropperImage.src = event.target.result;
  
              cropperImage.onload = function () {
                  if (cropper) {
                      cropper.destroy();
                  }
  
                  cropper = new Cropper(cropperImage, {
                      aspectRatio: 1,
                      viewMode: 1,
                      minContainerWidth: 500,
                      minContainerHeight: 500
                  });
  
                  modal.show();
              };
          };
  
          reader.readAsDataURL(file);
      });
  
      cropBtn.addEventListener('click', function () {
          if (!cropper) return;
  
          const canvas = cropper.getCroppedCanvas({
              width: 500,
              height: 500
          });
  
          if (canvas) {
              const base64 = canvas.toDataURL('image/jpeg');

              preview.src = base64;

              base64Input.value = base64;

              input.value = '';

              modal.hide();
          }
      });
  });
</script>

  </script>
  
  
@stop