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
            margin-bottom: 15px;
        }

        .info p {
            margin: 3px 0;
        }

        .medicamentos {
            margin-top: 20px;
        }

        .medicamento-item {
            margin-bottom: 20px;
        }

        .linha {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 100%;
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

        .assinatura {
            margin-top: 60px;
            text-align: right;
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
        @foreach($medicamentos as $med)
            <div class="medicamento-item">
                <p><strong>Nome/Remédio:</strong> <span class="linha">&nbsp;&nbsp;{{ $med['nome'] }}&nbsp;&nbsp;</span></p>

                <p>
                    <strong>Qnt Caixas:</strong> <span class="linha" style="width: 120px;">&nbsp;&nbsp;{{ $med['quantidade'] }}&nbsp;&nbsp;</span>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <strong>Nº Comprimidos/Gotas/Ml/Ampolas:</strong> 
                    <span class="linha" style="width: 200px;">
                        &nbsp;&nbsp;
                        {{ $med['detalhes']['comprimidos'] ?? $med['detalhes']['gotas'] ?? $med['detalhes']['ml'] ?? $med['detalhes']['ampolas'] ?? '' }}
                        &nbsp;&nbsp;
                    </span>
                </p>

                <p>
                    <strong>Quantidade de vezes ao dia:</strong> 
                    <span class="linha" style="width: 150px;">
                        &nbsp;&nbsp;{{ implode(' / ', $med['vezesPorDia']) }}x&nbsp;&nbsp;
                    </span>
                </p>
            </div>
        @endforeach
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
