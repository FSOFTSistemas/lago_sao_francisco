<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cardápio - {{ $cardapio->NomeCardapio }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 10px 0 5px; }
        ul { margin: 0 0 10px 20px; padding: 0; }
        .categoria { margin-left: 10px; font-weight: bold; }
        .item { margin-left: 20px; }
        .section-title { background: #f0f0f0; padding: 5px; font-weight: bold; margin-top: 15px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h1 style="color: #3e7222">{{ $cardapio->NomeCardapio }}</h1>
    <p><strong>Ano:</strong> {{ $cardapio->AnoCardapio }}</p>
    @if($cardapio->PrecoBasePorPessoa)
        <p><strong>Valor base por pessoa:</strong> R$ {{ number_format($cardapio->PrecoBasePorPessoa, 2, ',', '.') }}</p>
    @endif

    {{-- SEÇÕES DO CARDÁPIO --}}
    @if($cardapio->secoes->count())
        <h2>Seções do Cardápio</h2>
        @foreach($cardapio->secoes as $secao)
            <div class="section-title">{{ $secao->nome_secao_cardapio }}</div>

            @foreach($secao->categorias as $categoria)
                <div class="categoria" style="text-decoration: underline">
                    {{ $categoria->nome_categoria_item }}
                    ({{ $categoria->numero_escolhas_permitidas }} escolha{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }} permitida{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }})
                </div>
               
                @foreach($categoria->itens as $disp)
                    <div class="item">- {{ $disp->item->nome_item }} @if($disp->item->tipo_item) ({{ $disp->item->tipo_item }}) @endif</div>
                @endforeach
                <br>
            @endforeach
        @endforeach
    @endif

    {{-- OPÇÕES DE REFEIÇÃO PRINCIPAL --}}
    @if($cardapio->opcoes->count())
        <div class="page-break"></div>
        <h2>Opções de Refeição Principal</h2>

        @foreach($cardapio->opcoes as $opcao)
            <div class="section-title">
                {{ $opcao->NomeOpcaoRefeicao ?? 'Opção #' . $loop->iteration }}
            </div>

            @foreach($opcao->categorias as $categoria)
                <div class="categoria">
                    {{ $categoria->nome_categoria_item }}
                    ({{ $categoria->numero_escolhas_permitidas }} escolha{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }} permitida{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }})
                </div>

                @foreach($categoria->item as $disp)
                    <div class="item">- {{ $disp->item->nome_item }} @if($disp->item->tipo_item) ({{ $disp->item->tipo_item }}) @endif</div>
                @endforeach
            @endforeach
        @endforeach
    @endif
    
    <div class="info">
      <h2>Informações Importantes</h2>
      <ul>
        <li>Crianças de 0 a {{$cardapio->PoliticaCriancaGratisLimiteIdade}} não paga.</li>
        <li>Crianças de {{$cardapio->PoliticaCriancaDescontoIdadeInicio}} a {{$cardapio->PoliticaCriancaDescontoIdadeFim}} pagam {{intval($cardapio->PoliticaCriancaDescontoPercentual)}}% do valor da senha.</li>
        <li>Orçamento válido por {{$cardapio->ValidadeOrcamentoDias}} dias a contar da data do envio do orçamento.</li>
      </ul>
    </div>

    <div class="contato">
      <h2>Relacionamento ao cliente.</h2>
      <p>Contato WhatsApp (87) 98178-0808</p>
      <p>E-mail: <span style="text-decoration: underline">gerencia@lagosaofrancisco.com</span></p>
    </div>

</body>
</html>
