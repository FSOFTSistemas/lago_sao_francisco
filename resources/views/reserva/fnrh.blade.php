<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>FNRH</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            margin: 20px;
            color: #333;
        }
        .container {
            border: 1px solid #000;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        .hotel-header {
            background-color: #f0f5fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #d0ddee;
            font-size: 11px;
            line-height: 1.5;
            color: #334;
        }
        .hotel-header strong {
            font-size: 18px;
            font-weight: bold;
            color: #111;
            display: block;
            margin-bottom: 5px;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #eee;
            padding: 4px;
            margin-top: 10px;
            margin-bottom: 5px;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        td {
            border: 1px solid #ccc;
            padding: 4px;
            vertical-align: top;
            height: 20px; /* Altura padrão para campos */
        }
        .label {
            font-weight: bold;
            font-size: 9px;
            color: #555;
            display: block;
            margin-bottom: 2px;
        }
        .value {
            font-size: 11px;
            font-weight: bold;
            min-height: 15px; 
        }
        .field-box {
            border: none;
            border-bottom: 1px solid #999;
            height: 20px;
        }
        .checkbox-label {
            font-size: 10px;
        }
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 4px;
            vertical-align: middle;
        }
        .checkbox-item {
            display: inline-block;
            white-space: nowrap; 
            margin-right: 15px; 
            margin-bottom: 5px; 
        }
    </style>
</head>
<body>
    <div class="container">
      <div class="header">
          <h1>FICHA NACIONAL DE REGISTRO DE HÓSPEDES (FNRH)</h1>
          <span>(Lei nº 11.771/2008 - Art. 23)</span>
      </div>

        <div class="hotel-header">
            <strong>Lago São Francisco</strong>
            gerencia@lagosaofrancisco.com<br>
            +55 (87) 98178-0808 / +55 (87) 98178-0808<br>
            fazenda santa quiteria, 0, fazenda lago sao francisco, zona rural, São João, Pernambuco<br>
            CNPJ: 40.065.099/0001-24
        </div>


        <div class="section-title">Informações da Hospedagem</div>
        <table>
            <tr>
                <td style="width: 20%;">
                    <span class="label">UH N° (Local) / Quarto</span>
                    <div class="value">{{ $quarto->nome ?? 'N/A' }}</div>
                </td>
                <td style="width: 20%;">
                    <span class="label">N° Acompanhantes</span>
                    <div class="value">{{ $n_acompanhantes }}</div>
                </td>
                <td style="width: 30%;">
                    <span class="label">Data de Entrada</span>
                    <div class="value">{{ $data_entrada }}</div>
                </td>
                <td style="width: 30%;">
                    <span class="label">Data de Saída</span>
                    <div class="value">{{ $data_saida }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Hora Entrada</span>
                    <div class="value">{{ $hora_entrada }}</div>
                </td>
                <td colspan="3">
                    <span class="label">Hora Saída</span>
                    <div class="value">{{ $hora_saida }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Informações do Hóspede</div>
        <table>
            <tr>
                <td colspan="2">
                    <span class="label">Nome Completo</span>
                    <div class="value">{{ $hospede->nome ?? '' }}</div>
                </td>
                <td>
                    <span class="label">E-mail</span>
                    <div class="value">{{ $hospede->email ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 33%;">
                    <span class="label">Nascimento (DD/MM/AAAA)</span>
                    <div class="value">{{ $hospede->data_nascimento ? \Carbon\Carbon::parse($hospede->data_nascimento)->format('d/m/Y') : '' }}</div>
                </td>
                <td style="width: 33%;">
                    <span class="label">Profissão</span>
                    <div class="value">{{ $hospede->profissao ?? '' }}</div>
                </td>
                <td style="width: 34%;">
                    <span class="label">Nacionalidade</span>
                    <div class="value">{{ $hospede->nacionalidade ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Sexo</span>
                    <div class="value">{{ $hospede->sexo ?? '' }}</div>
                </td>
                <td>
                    <span class="label">CPF</span>
                    <div class="value">{{ $hospede->cpf ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Fone</span>
                    <div class="value">{{ $hospede->telefone ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Documento de Identidade (Nº)</span>
                    <div class="value">{{ $hospede->rg ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Órgão Expedidor</span>
                    <div class="value">{{ $hospede->rg_orgao ?? '' }}</div>
                </td>
                <td>
                    <span class="label">Tipo (Passaporte, etc.)</span>
                    <div class="value">{{ $hospede->rg ? 'RG' : ($hospede->passaporte ? 'Passaporte' : '') }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Endereço</div>
        <table>
            <tr>
                <td colspan="3">
                    <span class="label">Endereço (Rua, N°, Bairro)</span>
                    <div class="value">{{ $hospede->endereco ?? '' }}</div>
                </td>
                <td style="width: 25%;">
                    <span class="label">CEP</span>
                    <div class="value">{{ $hospede->cep ?? '' }}</div>
                </td>
            </tr>
            <tr>
                <td style="width: 35%;">
                    <span class="label">Cidade</span>
                    <div class="value">{{ $hospede->cidade ?? '' }}</div>
                </td>
                <td style="width: 20%;">
                    <span class="label">Estado (UF)</span>
                    <div class="value">{{ $hospede->estado ?? '' }}</div>
                </td>
                <td colspan="2" style="width: 45%;">
                    <span class="label">País</span>
                    <div class="value">{{ $hospede->pais ?? 'Brasil' }}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Informações da Viagem (a preencher pelo hóspede)</div>
        <table>
            <tr>
                <td style="width: 50%;">
                    <span class="label">Último Destino (Cidade, País)</span>
                    <div class="field-box"></div>
                </td>
                <td style="width: 50%;">
                    <span class="label">Próximo Destino (Cidade, País)</span>
                    <div class="field-box"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Motivo da Viagem</span>
                    
                    <div style="margin-top: 15px;">
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Férias</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Negócios</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Congresso</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Saúde</span>
                        </span>
                        <br> 
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Estudos</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Outro</span>
                        </span>
                    </div>

                </td>
                <td>
                    <span class="label">Meio de Transporte</span>

                    <div style="margin-top: 15px;">
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Automóvel</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Avião</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Ônibus</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Navio</span>
                        </span>
                        <br> 
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Trem</span>
                        </span>
                        <span class="checkbox-item">
                            <span class="checkbox"></span> <span class="checkbox-label">Outro</span>
                        </span>
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; font-size: 9px; text-align: justify;">
            Declaro que as informações acima são verdadeiras. Estou ciente de que os dados aqui fornecidos são protegidos pela Lei Geral de Proteção de Dados (LGPD) e serão utilizados exclusivamente para fins de registro de hospedagem e cumprimento de obrigações legais.
        </div>

        <div classs="signature-box" style="margin-top: 40px;">
            <div style="width: 60%; margin: 0 auto; border-top: 1px solid #000; padding-top: 5px; text-align: center;">
                Assinatura do Hóspede
            </div>
        </div>
    </div>
</body>
</html>