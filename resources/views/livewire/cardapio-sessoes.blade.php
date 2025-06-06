<div>

    <h5 class="mb-3">Seções do Cardápio</h5>
        <div class="col d-flex justify-content-end">
            @if($refeicao)
            <button class="btn btn-success new" wire:click="proximo">Próximo</button>
            @else
            <button class="btn btn-success new" wire:click="proximoCategoria({{$cardapioId}})">Próximo</button>
            @endif
        </div>
    <br>

    <form wire:submit.prevent="addSessao" class="mb-4">
        <div class="form-row">
            <div class="col-md-3">
                <label>Nome da Seção</label>
                <input type="text" wire:model="nomeSessao" class="form-control" required  wire:key="nomeSessao-{{ $inputKey }}" value= {{ old('nomeSessao', $this->cardapio->nomeSessao ?? '') }}>
                @error('nomeSessao') <span class="text-danger">{{ $message }}</span> @enderror

            </div>
            <div class="col-md-3">
                <label>Ordem de Exibição</label>
                <input
                    type="number"
                    wire:model="ordemExibicao"
                    wire:keyup="verificarOrdemExibicao"
                    wire:key="ordemExibicao-{{ $inputKey }}"
                    class="form-control @if($ordemExibicaoError) is-invalid @enderror"
                    required
                >

                @error('ordemExibicao')
                    <span class="text-danger">{{ $message }}</span>
                @enderror

                @if($ordemExibicaoError)
                    <span class="text-danger">{{ $ordemExibicaoError }}</span>
                @endif

            </div>
            

            <div class="form-group row" x-data="{ ativo: @entangle('ehOpcaoPrincipal').live }" wire:key="switch-{{ $inputKey }}">
                <label class="col-md-6 form-label d-block label-control">É conteúdo principal?</label>
                <div class="form-check form-switch">
                    <input
                        class="form-check-input" 
                        type="checkbox"
                        id="ehOpcaoPrincipalSwitch"
                        x-model="ativo"
                        wire:key="ehOpcaoPrincipal-{{ $inputKey }}"
                        @change="$wire.set('ehOpcaoPrincipal', ativo ? 1 : 0)"
                    >
                    <label class="form-check-label ms-2" for="ehOpcaoPrincipalSwitch">
                        <span x-text="ativo ? 'Sim' : 'Não'"></span>
                    </label>
                </div>
                @error('ehOpcaoPrincipal') <span class="text-danger">{{ $message }}</span> @enderror
            </div>




          
            <div class="col-auto d-flex align-items-end">
                <button class="btn btn-primary" @if($ordemExibicaoError) disabled @endif>Adicionar</button>
            </div>
        </div>
    </form>

    @if($sessoes->count())
        <table class="table table-bordered table-striped">
            <thead class="thead-light">
                <tr>
                    <th>Nome</th>
                    <th>Ordem</th>
                    <th>Principal?</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sessoes as $sessao)
                    <tr>
                        <td>{{ $sessao->nome_secao_cardapio }}</td>
                        <td>{{ $sessao->ordem_exibicao }}</td>
                        <td>{{ $sessao->opcao_conteudo_principal_refeicao ? 'Sim' : 'Não' }}</td>
                        <td>
                            <button 
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                wire:click="deletarSessao({{$sessao->id}})"
                                title="Excluir Seção"
                            >
                                🗑️
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">Nenhuma seção cadastrada ainda.</p>
    @endif
    <div class="footer">
        @if($refeicao)

        @else
        @endif
    </div>

    @script
    <script>
        $wire.on("confirm", (event) => {
            Swal.fire({
            title: "Deletar seção?",
            text: "Você não poderá desfazer!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, deletar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("delete", { id: event.id})
            }
            });
        })

        $wire.on("confirmProximo", () => {
            Swal.fire({
            title: "Continuar para a próxima seção?",
            text: "Você não poderá voltar.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, continuar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("proximoConfirmado")
            }
            });
        })

        $wire.on("confirmProxAba", (e) => {
            Swal.fire({
            title: "Ir para a próxima página?",
            text: "Confira todos os campos antes de prosseguir.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, prosseguir!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("proxAbaConfirmado", { id: e.id})
            }
            });
        })
    </script>
    @endscript
</div>


