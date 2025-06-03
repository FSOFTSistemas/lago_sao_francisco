<div class="modal fade" id="createCategoriaModal" tabindex="-1" role="dialog" aria-labelledby="createCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCategoriaModalLabel">
                    <i class="fas fa-folder-plus mr-2"></i>Nova Categoria do Cardápio
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="createCategoriaForm" action="{{ route('categoriaItensCardapio.store') }}" method="POST">
                @csrf
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome_categoria_item" class="font-weight-bold">Nome da Categoria*</label>
                                <input type="text" class="form-control @error('nome_categoria_item') is-invalid @enderror" 
                                       id="nome_categoria_item" name="nome_categoria_item" value="{{ old('nome_categoria_item') }}" 
                                       placeholder="Ex: Pratos Principais" required>
                                @error('nome_categoria_item')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sessao_cardapio_id" class="font-weight-bold">Seção do Cardápio*</label>
                                <select class="form-control @error('sessao_cardapio_id') is-invalid @enderror" 
                                        id="sessao_cardapio_id" name="sessao_cardapio_id" required>
                                    <option value="">Selecione a seção...</option>
                                    @foreach($secoes as $secao)
                                        <option value="{{ $secao->id }}" {{ old('sessao_cardapio_id') == $secao->id ? 'selected' : '' }}>
                                            {{ $secao->nome_secao_cardapio }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sessao_cardapio_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="numero_escolhas_permitidas" class="font-weight-bold">Nº de Escolhas Permitidas*</label>
                                <input type="number" class="form-control @error('numero_escolhas_permitidas') is-invalid @enderror" 
                                       id="numero_escolhas_permitidas" name="numero_escolhas_permitidas" 
                                       value="{{ old('numero_escolhas_permitidas', 1) }}" min="1" max="10" required>
                                @error('numero_escolhas_permitidas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ordem_exibicao" class="font-weight-bold">Ordem de Exibição*</label>
                                <input type="number" class="form-control @error('ordem_exibicao') is-invalid @enderror" 
                                       id="ordem_exibicao" name="ordem_exibicao" value="{{ old('ordem_exibicao', 1) }}" min="1" required>
                                @error('ordem_exibicao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Escolha Exclusiva?</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="eh_grupo_escolha_exclusiva" 
                                           name="eh_grupo_escolha_exclusiva" value="1" {{ old('eh_grupo_escolha_exclusiva') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="eh_grupo_escolha_exclusiva">Sim</label>
                                </div>
                                @error('eh_grupo_escolha_exclusiva')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="refeicao_principal_id">Refeição Principal Relacionada</label>
                                <select class="form-control @error('refeicao_principal_id') is-invalid @enderror" 
                                        id="refeicao_principal_id" name="refeicao_principal_id">
                                    <option value="">Nenhuma</option>
                                    @foreach($refeicoes as $refeicao)
                                        <option value="{{ $refeicao->id }}" {{ old('refeicao_principal_id') == $refeicao->id ? 'selected' : '' }}>
                                            {{ $refeicao->NomeOpcaoRefeicao }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('refeicao_principal_id')
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
                        <i class="fas fa-save mr-1"></i> Salvar Categoria
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
    $('#createCategoriaForm').validate({
        rules: {
            nome_categoria_item: {
                required: true,
                minlength: 3
            },
            sessao_cardapio_id: {
                required: true
            },
            numero_escolhas_permitidas: {
                required: true,
                min: 1,
                max: 10
            },
            ordem_exibicao: {
                required: true,
                min: 1
            }
        },
        messages: {
            nome_categoria_item: {
                required: "Por favor, informe o nome da categoria",
                minlength: "O nome deve ter pelo menos 3 caracteres"
            },
            sessao_cardapio_id: {
                required: "Por favor, selecione a seção do cardápio"
            },
            numero_escolhas_permitidas: {
                required: "Informe o número de escolhas permitidas",
                min: "Mínimo de 1 escolha",
                max: "Máximo de 10 escolhas"
            },
            ordem_exibicao: {
                required: "Informe a ordem de exibição",
                min: "A ordem deve ser pelo menos 1"
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
                    toastr.success('Categoria cadastrada com sucesso!');
                    $('#createCategoriaModal').modal('hide');
                    
                    // Recarrega a tabela ou adiciona a nova categoria dinamicamente
                    if(typeof tableCategorias !== 'undefined') {
                        tableCategorias.ajax.reload();
                    } else {
                        setTimeout(() => { location.reload(); }, 1500);
                    }
                },
                error: function(xhr) {
                    toastr.error('Erro ao cadastrar categoria: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                    $('.modal-footer button').prop('disabled', false);
                    $('.modal-footer span').remove();
                }
            });
        }
    });
    
    // Limpa o formulário quando o modal é fechado
    $('#createCategoriaModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
});
</script>
@endsection