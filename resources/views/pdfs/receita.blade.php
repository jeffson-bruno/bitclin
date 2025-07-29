<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 30px;
            font-size: 13px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header img {
            max-height: 100px;
            margin-bottom: 5px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 3px 0;
        }

        .medicamentos {
            margin-top: 30px;
        }

        .linha-medicamento {
            margin-bottom: 15px;
        }

        .linha-texto,
        .linha-detalhes {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .linha-texto .esquerda,
        .linha-detalhes .esquerda {
            width: 25%;
            border-bottom: 1px solid #000;
            padding-right: 10px;
        }

        .linha-texto .direita,
        .linha-detalhes .direita {
            width: 20%;
            text-align: right;
            border-bottom: 1px solid #000;
            padding-left: 10px;
        }

        .linha-detalhes .esquerda {
            border-bottom: 1px dotted #000;
        }

        .linha-detalhes .direita {
            border-bottom: 1px dotted #000;
        }

        .assinatura {
            margin-top: 60px;
            text-align: right;
        }

        .footer {
            position: absolute;
            bottom: 30px;
            left: 0;
            width: 100%;
            font-size: 11px;
            text-align: center;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo-clinica.png') }}" alt="Logo da Clínica">
    </div>

    <div class="title">Receita Médica</div>

    <div class="info">
        <p><strong>Nome do Paciente:</strong> {{ $paciente->nome }}</p>
        <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
        <p><strong>Data:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>Médico:</strong> {{ $medico->name }}</p>
        <p><strong>CRM:</strong> {{ $crm }}</p>
    </div>

    <div class="medicamentos">
        <table style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif;">
            @foreach($medicamentos as $med)
                @php
                    $quantidadeTipo = '';
                    if (!empty($med['detalhes']['comprimidos'])) {
                        $quantidadeTipo = $med['detalhes']['comprimidos'] . ' Comp.';
                    } elseif (!empty($med['detalhes']['gotas'])) {
                        $quantidadeTipo = $med['detalhes']['gotas'] . ' Gotas';
                    } elseif (!empty($med['detalhes']['ml'])) {
                        $quantidadeTipo = $med['detalhes']['ml'] . ' ml';
                    } elseif (!empty($med['detalhes']['ampolas'])) {
                        $quantidadeTipo = $med['detalhes']['ampolas'] . ' Amp';
                    }

                    $intervalo = trim(($med['intervaloHoras'] ?? $med['intervalo'] ?? ''));

                    if (!empty($intervalo)) {
                        $intervalo .= ' hs';
                    }

                    $linhaPontos = str_repeat('.', 40);
                    $linhaPontos2 = str_repeat('.', 30);
                @endphp

                <tr style="margin-bottom: 20px;">
                    <td colspan="2" style="padding: 4px; padding-top: 10px; border-bottom: 1px dashed #ccc;">
                        {{-- Nome, Caixas e Miligramas (inclusive para Injetável) --}}
                        {{ $med['nome'] }} {{ $linhaPontos }}
                        {{ $med['quantidade'] ?? '1' }} {{ ($med['quantidade'] ?? 1) > 1 ? 'Cxs' : 'Cx' }}
                        @if (!empty($med['miligramas']))
                            - {{ $med['miligramas'] }}
                        @endif
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding: 4px;">
                        {{ $quantidadeTipo }} {{ $linhaPontos2 }} {{ $intervalo }}
                    </td>
                </tr>

                @if(($med['tipo'] ?? '') === 'Injetável' && !empty($med['instrucao']))
                    <tr>
                        <td colspan="2" style="padding: 4px;">
                            <strong>Instruções de Aplicação:</strong> {{ $med['instrucao'] }}
                        </td>
                    </tr>
                @endif

            @endforeach
        </table>
    </div>



    <div class="assinatura">
        ___________________________<br>
        Assinatura / Carimbo Médico
    </div>

    <div class="footer">
        Rua Projetada 01, Nº 123, Centro – Cocal-PI<br>
        CEP 64230-000 – Telefone: (86) 9 9907-8090<br>
        @bitclin.oficial | www.bitclin.com.br
    </div>

</body>
</html>

