<div>
    <ul class="nav nav-tabs" id="dayuseTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'geral' ? 'active' : '' }}" id="geral-tab" href="#" role="tab"
                wire:click.prevent="$set('abaAtual', 'geral')">Informações Gerais</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $abaAtual === 'pagamento' ? 'active' : '' }}" id="pagamento-tab" href="#"
                role="tab" wire:click.prevent="$set('abaAtual', 'pagamento')" >Seções</a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="dayuseTabContent">
        <div class="tab-pane fade {{ $abaAtual === 'geral' ? 'show active' : '' }}" id="geral" role="tabpanel">
            <form wire:submit.prevent="save">
                // tela aqui com os botões
            </form>
        </div>

        <div class="tab-pane fade {{ $abaAtual === 'pagamento' ? 'show active' : '' }}" id="pagamento" role="tabpanel">
                @livewire('DayUsePagamento')
        </div>

    </div>
  
        @script
    <script>
        $wire.on("confirmed", (event) => {
            Swal.fire({
            title: "Continuar para a próxima página?",
            text: "Revise todos os campos",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sim, continuar!"
            }).then((result) => {
            if (result.isConfirmed) {
               $wire.dispatch("avancou")
            }
            });
        })
    </script>
    @endscript
</div>
