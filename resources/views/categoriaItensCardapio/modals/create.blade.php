<div class="modal fade" id="createItemModal" tabindex="-1" role="dialog" aria-labelledby="createItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createItemModalLabel">
                    <i class="fas fa-utensils mr-2"></i>Novo Item do Cardápio
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="createItemForm" action="{{ route('itemCardapio.store') }}" method="POST">
                @csrf
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nome_item" class="font-weight-bold">Nome do Item*</label>
                                <input type="text" class="form-control @error('nome_item') is-invalid @enderror" 
                                       id="nome_item" name="nome_item" value="{{ old('nome_item') }}" 
                                       placeholder="Ex: Filé Mignon com Molho Madeira" required>
                                @error('nome_item')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_item" class="font-weight-bold">Tipo*</label>
                                <select class="form-control @error('tipo_item') is-invalid @enderror" 
                                        id="tipo_item" name="tipo_item" required>
                                    <option value="">Selecione...</option>
                                    <option value="Entrada" {{ old('tipo_item') == 'Entrada' ? 'selected' : '' }}>Entrada</option>
                                    <option value="Prato Principal" {{ old('tipo_item') == 'Prato Principal' ? 'selected' : '' }}>Prato Principal</option>
                                    <option value="Sobremesa" {{ old('tipo_item') == 'Sobremesa' ? 'selected' : '' }}>Sobremesa</option>
                                    <option value="Bebida" {{ old('tipo_item') == 'Bebida' ? 'selected' : '' }}>Bebida</option>
                                </select>
                                @error('tipo_item')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    // Validação do formulário
    $('#createItemForm').validate({
        rules: {
            nome_item: {
                required: true,
                minlength: 3
            },
            tipo_item: {
                required: true
            }
        },
        messages: {
            nome_item: {
                required: "Por favor, informe o nome do item",
                minlength: "O nome deve ter pelo menos 3 caracteres"
            },
            tipo_item: {
                required: "Por favor, selecione o tipo do item"
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            // Submissão via AJAX
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                beforeSend: function() {
                    $('.modal-footer button').prop('disabled', true);
                    $('.modal-footer').append('<span class="ml-2"><i class="fas fa-spinner fa-spin"></i> Salvando...</span>');
                },
                success: function(response) {
                    toastr.success('Item cadastrado com sucesso!');
                    $('#createItemModal').modal('hide');
                    
                    // Recarrega a tabela ou adiciona o novo item dinamicamente
                    if(typeof tableItens !== 'undefined') {
                        tableItens.ajax.reload();
                    } else {
                        setTimeout(() => { location.reload(); }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Erro ao cadastrar item: ' + xhr.responseJSON.message);
                    $('.modal-footer button').prop('disabled', false);
                    $('.modal-footer span').remove();
                }
            });
        }
    });
    
    // Limpa o formulário quando o modal é fechado
    $('#createItemModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
});
</script>
@endsection