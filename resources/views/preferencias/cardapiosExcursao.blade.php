@extends('adminlte::page')

@section('title', 'Cardápios de Excursão')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mb-1">Cardápios de Excursão</h1>
            <p class="text-muted mb-0">Cadastre as opções de almoço disponíveis para excursões.</p>
        </div>
        <a href="{{ route('preferencias') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title"><i class="fas fa-utensils mr-2"></i>Opções cadastradas</h3>
            <button type="button" class="btn btn-success btn-sm ml-auto" data-toggle="modal" data-target="#modalCriarCardapio">
                <i class="fas fa-plus mr-1"></i> Novo cardápio
            </button>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Itens do cardápio</th>
                        <th class="text-right">Valor por pessoa</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cardapios as $cardapio)
                        <tr>
                            <td class="font-weight-bold">{{ $cardapio->nome }}</td>
                            <td style="max-width: 440px;">
                                <ul class="mb-0 pl-3">
                                    @foreach (preg_split('/\r\n|\r|\n/', $cardapio->descricao_cardapio, -1, PREG_SPLIT_NO_EMPTY) as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-right text-nowrap">R$ {{ number_format((float) $cardapio->valor_por_pessoa, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $cardapio->ativo ? 'success' : 'secondary' }}">
                                    {{ $cardapio->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm" title="Editar"
                                    data-toggle="modal" data-target="#modalEditarCardapio{{ $cardapio->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" title="Excluir"
                                    data-toggle="modal" data-target="#modalExcluirCardapio{{ $cardapio->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        @include('preferencias.partials.cardapioExcursaoEdit', ['cardapio' => $cardapio])
                        @include('preferencias.partials.cardapioExcursaoDelete', ['cardapio' => $cardapio])
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum cardápio de excursão cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalCriarCardapio" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('cardapios-excursao.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_origem" value="modalCriarCardapio">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title"><i class="fas fa-plus mr-1"></i> Novo cardápio de excursão</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('preferencias.partials.cardapioExcursaoForm', [
                            'prefixo' => 'novo',
                            'cardapio' => null,
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function exibirValorCardapio(campo, digitos) {
                const valorOculto = document.getElementById(campo.dataset.moneyTarget);
                if (!valorOculto) return;

                if (!digitos) {
                    campo.value = '';
                    valorOculto.value = '';
                    return;
                }

                const valor = Number(digitos) / 100;
                campo.value = valor.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                valorOculto.value = valor.toFixed(2);
                campo.setCustomValidity('');
            }

            function digitosAtuais(campo) {
                const valor = document.getElementById(campo.dataset.moneyTarget)?.value;
                return valor ? String(Math.round(Number(valor) * 100)) : '';
            }

            document.addEventListener('keydown', event => {
                const campo = event.target.closest('.valor-cardapio-display');
                if (!campo) return;

                let digitos = digitosAtuais(campo);
                if (/^\d$/.test(event.key)) {
                    event.preventDefault();
                    if (campo.selectionStart !== campo.selectionEnd) digitos = '';
                    digitos = (digitos + event.key).replace(/^0+(?=\d)/, '').slice(-10);
                    exibirValorCardapio(campo, digitos);
                } else if (event.key === 'Backspace' || event.key === 'Delete') {
                    event.preventDefault();
                    digitos = campo.selectionStart !== campo.selectionEnd ? '' : digitos.slice(0, -1);
                    exibirValorCardapio(campo, digitos);
                }
            });

            document.addEventListener('input', event => {
                const campo = event.target.closest('.valor-cardapio-display');
                if (!campo) return;
                const digitos = campo.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '').slice(-10);
                exibirValorCardapio(campo, digitos);
            });

            document.addEventListener('paste', event => {
                const campo = event.target.closest('.valor-cardapio-display');
                if (!campo) return;
                event.preventDefault();
                const digitos = event.clipboardData.getData('text').replace(/\D/g, '').slice(-10);
                exibirValorCardapio(campo, digitos);
            });

            document.querySelectorAll('.valor-cardapio-display').forEach(campo => {
                campo.closest('form').addEventListener('submit', event => {
                    const valorOculto = document.getElementById(campo.dataset.moneyTarget);
                    if (Number(valorOculto.value) <= 0) {
                        event.preventDefault();
                        campo.setCustomValidity('Informe um valor por pessoa maior que zero.');
                        campo.reportValidity();
                    }
                });
            });

            function atualizarEditor(editor) {
                const linhas = editor.querySelectorAll('.item-cardapio-linha');
                linhas.forEach((linha, indice) => {
                    linha.querySelector('.numero-item').textContent = indice + 1;
                    linha.classList.toggle('border-bottom', indice < linhas.length - 1);
                });
                editor.querySelector('.lista-itens-vazia').classList.toggle('d-none', linhas.length > 0);
            }

            document.querySelectorAll('.itens-cardapio-editor').forEach(atualizarEditor);

            function adicionarItem(editor) {
                const campo = editor.querySelector('.novo-item-cardapio');
                const nome = campo.value.trim();
                if (!nome) {
                    campo.focus();
                    return;
                }

                const repetido = [...editor.querySelectorAll('input[name="itens[]"]')]
                    .some(input => input.value.toLocaleLowerCase('pt-BR') === nome.toLocaleLowerCase('pt-BR'));
                if (repetido) {
                    campo.setCustomValidity('Este item já foi adicionado.');
                    campo.reportValidity();
                    return;
                }

                campo.setCustomValidity('');
                const linha = document.createElement('div');
                linha.className = 'd-flex align-items-center py-1 item-cardapio-linha';

                const numero = document.createElement('span');
                numero.className = 'badge badge-light border mr-2 numero-item';
                const texto = document.createElement('span');
                texto.className = 'flex-grow-1 texto-item-cardapio';
                texto.textContent = nome;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'itens[]';
                input.value = nome;
                const remover = document.createElement('button');
                remover.type = 'button';
                remover.className = 'btn btn-link text-danger btn-sm p-1 remover-item-cardapio';
                remover.title = 'Remover item';
                remover.innerHTML = '<i class="fas fa-times"></i>';

                linha.append(numero, texto, input, remover);
                editor.querySelector('.itens-cardapio-lista').insertBefore(
                    linha,
                    editor.querySelector('.lista-itens-vazia')
                );
                campo.value = '';
                atualizarEditor(editor);
                campo.focus();
            }

            document.addEventListener('click', function (event) {
                const adicionar = event.target.closest('.adicionar-item-cardapio');
                if (adicionar) {
                    const editor = adicionar.closest('.itens-cardapio-editor');
                    adicionarItem(editor);
                    return;
                }

                const remover = event.target.closest('.remover-item-cardapio');
                if (remover) {
                    const editor = remover.closest('.itens-cardapio-editor');
                    remover.closest('.item-cardapio-linha').remove();
                    atualizarEditor(editor);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && event.target.classList.contains('novo-item-cardapio')) {
                    event.preventDefault();
                    adicionarItem(event.target.closest('.itens-cardapio-editor'));
                }
            });

            document.querySelectorAll('.itens-cardapio-editor').forEach(editor => {
                editor.querySelector('.novo-item-cardapio').addEventListener('input', event => {
                    event.target.setCustomValidity('');
                });

                editor.closest('form').addEventListener('submit', event => {
                    const campo = editor.querySelector('.novo-item-cardapio');
                    if (campo.value.trim()) {
                        adicionarItem(editor);
                    }

                    if (!campo.checkValidity()) {
                        event.preventDefault();
                        campo.reportValidity();
                        return;
                    }

                    if (!editor.querySelector('.item-cardapio-linha')) {
                        event.preventDefault();
                        campo.setCustomValidity('Adicione pelo menos um item ao cardápio.');
                        campo.reportValidity();
                    }
                });
            });

            @if ($errors->any() && old('_origem'))
                $('#{{ old('_origem') }}').modal('show');
            @endif
        });
    </script>
@stop
