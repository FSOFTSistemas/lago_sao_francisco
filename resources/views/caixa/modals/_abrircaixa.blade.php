<div class="modal fade" id="abrirCaixaModal{{ $caixa->id }}" tabindex="-1"
  aria-labelledby="abrirCaixaLabel{{ $caixa->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('caixas.abrir', $caixa->id) }}" method="POST" class="form-abrir-caixa w-100">
      @csrf
      <div class="modal-content" style="background-color: #fff; color: #212529; border: 1px solid #adb5bd;">
        <div class="modal-header" style="background-color: #146c43; color: #fff; border-bottom: 1px solid #0f5132;">
          <h5 class="modal-title font-weight-bold" id="abrirCaixaLabel{{ $caixa->id }}" style="color: #fff !important;">
            Abrir Caixa #{{ $caixa->id }}
          </h5>
          <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Fechar"
            style="color: #fff; opacity: 1; text-shadow: none;">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="background-color: #fff; color: #212529;">
          <label for="valor_inicial{{ $caixa->id }}" class="font-weight-bold" style="color: #212529;">
            Valor inicial
          </label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text font-weight-bold" style="background-color: #e9ecef; color: #212529;">R$</span>
            </div>
            <input type="text" inputmode="numeric" name="valor_inicial" id="valor_inicial{{ $caixa->id }}"
              class="form-control valor-inicial-caixa" value="{{ number_format((float) old('valor_inicial', 0), 2, ',', '.') }}"
              autocomplete="off" aria-describedby="ajudaValorInicial{{ $caixa->id }}" required
              style="background-color: #fff; color: #212529; border-color: #6c757d;">
          </div>
          <small id="ajudaValorInicial{{ $caixa->id }}" class="form-text" style="color: #495057;">
            Informe o valor disponível no momento da abertura.
          </small>
        </div>
        <div class="modal-footer" style="background-color: #f8f9fa; border-top: 1px solid #ced4da;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success font-weight-bold">Confirmar abertura</button>
        </div>
      </div>
    </form>
  </div>
</div>

@once
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const formatarValor = function (campo) {
        const digitos = campo.value.replace(/\D/g, '');
        const valor = Number(digitos || 0) / 100;

        campo.value = valor.toLocaleString('pt-BR', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      };

      document.querySelectorAll('.valor-inicial-caixa').forEach(function (campo) {
        formatarValor(campo);
        campo.addEventListener('input', function () {
          formatarValor(campo);
        });
        campo.addEventListener('focus', function () {
          campo.select();
        });
      });

      document.querySelectorAll('.form-abrir-caixa').forEach(function (formulario) {
        formulario.addEventListener('submit', function () {
          const campo = formulario.querySelector('.valor-inicial-caixa');
          campo.value = campo.value.replace(/\./g, '').replace(',', '.');
        });
      });
    });
  </script>
@endonce
