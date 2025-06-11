<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cardápio - {{ $cardapio->NomeCardapio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 12px; 
            color: #2d3748; 
            line-height: 1.5;
            background: #ffffff;
            padding-top: 30px;
            margin-top: 20px;
        }
        
        .header {
            background: #3e7222;
            color: white;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        h1 { 
            font-size: 28px; 
            font-weight: bold; 
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 0 20px;
            margin-top: 15px;
        }
        
        h2 { 
            font-size: 20px; 
            font-weight: bold; 
            color: #2d5016; 
            margin: 20px 0 12px; 
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        h3 { 
            font-size: 16px; 
            font-weight: bold; 
            margin: 12px 0 6px; 
            color: #4a5568;
        }
        
        .section-title { 
            background: #f7fafc;
            border-left: 4px solid #3e7222;
            padding: 12px 16px; 
            font-weight: bold; 
            font-size: 14px;
            margin: 15px 0 10px;
            color: #2d5016;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .price-badge {
            background: #38a169;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
        }
        
        .categoria { 
            margin: 12px 0 8px 15px; 
            font-weight: bold; 
            color: #2d5016;
            font-size: 13px;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .item { 
            margin: 6px 0 6px 30px; 
            padding: 4px 0;
            color: #4a5568;
            position: relative;
        }
        
        .item:before {
            content: "• ";
            color: #3e7222;
            font-weight: bold;
            position: absolute;
            left: -12px;
        }
        
        .item-type {
            color: #718096;
            font-style: italic;
            font-size: 11px;
        }
        
        .info {
            background: #ebf8ff;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            border-left: 4px solid #3182ce;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .info h2 {
            color: #2c5282;
            margin-top: 0;
            font-size: 18px;
        }
        
        .info ul {
            list-style: none;
            margin: 12px 0;
        }
        
        .info li {
            padding: 6px 0;
            position: relative;
            padding-left: 20px;
        }
        
        .info li:before {
            content: "→ ";
            position: absolute;
            left: 0;
            top: 6px;
            color: #3182ce;
            font-weight: bold;
        }
        
        .contato {
            background: #f0fff4;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            border-left: 4px solid #38a169;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        .contato h2 {
            color: #22543d;
            margin-top: 0;
            font-size: 18px;
        }
        
        .contato p {
            margin: 8px 0;
            font-weight: bold;
        }
        
        .contact-item {
            margin: 10px 0;
        }
        
        .whatsapp {
            color: #25d366;
        }
        
        .email {
            color: #3182ce;
        }
        
        .page-break { 
            page-break-after: always; 
        }
        
        .cover-page {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 40px;
            text-align: center;
        }
        
        .cover-logo {
            max-width: 300px;
            max-height: 300px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .cover-title {
            font-size: 36px;
            font-weight: bold;
            color: #2d5016;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .cover-year {
            position: absolute;
            bottom: 40px;
            right: 40px;
            font-size: 18px;
            font-weight: bold;
            color: #4a5568;
            background: #f7fafc;
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #e2e8f0;
        }
        
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 20px 0;
        }
        
        .highlight {
            background: #fef5e7;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        .icon-text:before {
            margin-right: 6px;
        }
        
        .menu-icon:before {
            content: "🍽 ";
        }
        
        .main-icon:before {
            content: "🍖 ";
        }
        
        .info-icon:before {
            content: "📋 ";
        }
        
        .contact-icon:before {
            content: "📞 ";
        }
        
        .whatsapp-icon:before {
            content: "📱 ";
        }
        
        .email-icon:before {
            content: "📧 ";
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: #f7fafc;
            font-weight: bold;
            color: #2d5016;
        }
    </style>
</head>
<body>
    {{-- PÁGINA DE CAPA --}}
    <div class="cover-page">
        {{-- Substitua 'caminho/para/sua/imagem.jpg' pelo caminho real da sua imagem --}}
        <img src="{{ asset('lago-login.jpg') }}" alt="Logo do Cardápio" class="cover-logo">
        
        <h1 class="cover-title">{{ $cardapio->NomeCardapio }}</h1>
        
        <div class="cover-year">{{ $cardapio->AnoCardapio }}</div>
    </div>
    
    {{-- QUEBRA DE PÁGINA PARA INICIAR O CONTEÚDO --}}
    <div class="page-break"></div>

    <div class="header">
        <h1>{{ $cardapio->NomeCardapio }}</h1>
        <p class="subtitle"><strong>Ano:</strong> {{ $cardapio->AnoCardapio }}</p>
    </div>

    <div class="content">
        {{-- SEÇÕES DO CARDÁPIO --}}
        @if($cardapio->secoes->count())
            <h2 class="menu-icon">Seções do Cardápio</h2>
            @foreach($cardapio->secoes as $secao)
                <div class="section-title">{{ $secao->nome_secao_cardapio }}</div>

                @foreach($secao->categorias as $categoria)
                    <div class="categoria">
                        {{ $categoria->nome_categoria_item }}
                        <span class="highlight">
                            ({{ $categoria->numero_escolhas_permitidas }} escolha{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }} permitida{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }})
                        </span>
                    </div>
                   
                    @foreach($categoria->itens as $disp)
                        <div class="item">
                            {{ $disp->item->nome_item }} 
                            @if($disp->item->tipo_item) 
                                <span class="item-type">({{ $disp->item->tipo_item }})</span> 
                            @endif
                        </div>
                    @endforeach
                    <br>
                @endforeach
            @endforeach
        @endif

        {{-- OPÇÕES DE REFEIÇÃO PRINCIPAL --}}
        @if($cardapio->opcoes->count())
            <div class="page-break"></div>
            <h2 class="main-icon">Opções de Refeição Principal</h2>

            @foreach($cardapio->opcoes as $opcao)
                <div class="section-title">
                    {{ $opcao->NomeOpcaoRefeicao ?? 'Opção #' . $loop->iteration }}
                    <span class="price-badge">R$ {{ number_format($opcao->PrecoPorPessoa, 2, ',', '.') }}</span>
                </div>

                @foreach($opcao->categorias as $categoria)
                    <div class="categoria">
                        {{ $categoria->nome_categoria_item }}
                        <span class="highlight">
                            ({{ $categoria->numero_escolhas_permitidas }} escolha{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }} permitida{{ $categoria->numero_escolhas_permitidas > 1 ? 's' : '' }})
                        </span>
                    </div>
                    
                    @foreach($categoria->itens ?? [] as $disp)
                        <div class="item">
                            {{ $disp->item->nome_item }} 
                            @if($disp->item->tipo_item) 
                                <span class="item-type">({{ $disp->item->tipo_item }})</span> 
                            @endif
                        </div>
                    @endforeach
                @endforeach

                @if(!$loop->last)
                    <div class="divider"></div>
                @endif
            @endforeach
        @endif
        
        <div class="info">
            <h2 class="info-icon">Informações Importantes</h2>
            <ul>
                <li>Crianças de 0 a {{$cardapio->PoliticaCriancaGratisLimiteIdade}} anos não pagam.</li>
                <li>Crianças de {{$cardapio->PoliticaCriancaDescontoIdadeInicio}} a {{$cardapio->PoliticaCriancaDescontoIdadeFim}} anos pagam {{intval($cardapio->PoliticaCriancaDescontoPercentual)}}% do valor da senha.</li>
                <li>Orçamento válido por {{$cardapio->ValidadeOrcamentoDias}} dias a contar da data do envio do orçamento.</li>
            </ul>
        </div>

        <div class="contato">
            <h2 class="contact-icon">Relacionamento ao Cliente</h2>
            <div class="contact-item">
                <p class="whatsapp-icon whatsapp"><strong>WhatsApp:</strong> (87) 98178-0808</p>
            </div>
            <div class="contact-item">
                <p class="email-icon email"><strong>E-mail:</strong> gerencia@lagosaofrancisco.com</p>
            </div>
        </div>
    </div>
</body>
</html>

