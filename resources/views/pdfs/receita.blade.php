<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Receita Médica</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            position: relative;
        }

        .marca-dagua {
            position: absolute;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            font-size: 80px;
            white-space: nowrap;
            z-index: 0;
        }

        .cabecalho, .rodape {
            text-align: center;
            margin-bottom: 20px;
        }

        .cabecalho img {
            height: 60px;
        }

        .info-paciente {
            margin-bottom: 20px;
            z-index: 1;
            position: relative;
        }

        .info-paciente p {
            margin: 2px 0;
        }

        table.medicamentos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.medicamentos th, table.medicamentos td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .assinatura {
            margin-top: 50px;
            text-align: center;
        }

        .assinatura p {
            margin-top: 60px;
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 5px;
        }

        .rodape {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Marca d'água -->
    <div class="marca-dagua">
        BitClin
    </div>

    <!-- Cabeçalho -->
    <div class="cabecalho">
        <img src="{{ public_path('logo.png') }}" alt="Logomarca">
        <h2>Clínica Médica BitClin</h2>
        <p>Rua Projetada 01 - Cocal - PI</p>
        <p>(86) 9 9907-8090 / (86) 9 9998-6493</p>
    </div>

    <!-- Informações do Paciente -->
    <div class="info-paciente">
        <p><strong>Paciente:</strong> {{ $paciente->nome }}</p>
        <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
        <p><strong>Data:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
        <p><strong>Médico:</strong> Dr(a). {{ $medico->name }} | CRM: {{ $crm }}</p>
    </div>

    <!-- Medicamentos -->
    <table class="medicamentos">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Quantidade</th>
                <th>Frequência</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($medicamentos as $med)
                <tr>
                    <td>{{ $med['nome'] }}</td>
                    <td>{{ $med['tipo'] }}</td>
                    <td>{{ $med['quantidade'] }}</td>
                    <td>
                        @if (isset($med['vezesPorDia']) && is_array($med['vezesPorDia']))
                            {{ implode('x ao dia, ', $med['vezesPorDia']) }}x ao dia
                        @else
                            {{ $med['vezesPorDia'] ?? '-' }}
                        @endif
                    </td>
                    <td>
                        @php
                            $detalhes = $med['detalhes'] ?? [];
                            echo implode(', ', array_filter([
                                $detalhes['gotas'] ? $detalhes['gotas'] . ' gotas' : null,
                                $detalhes['ml'] ? $detalhes['ml'] . ' ml' : null,
                                $detalhes['comprimidos'] ? $detalhes['comprimidos'] . ' comprimidos' : null,
                                $detalhes['ampolas'] ? $detalhes['ampolas'] . ' ampolas' : null,
                            ]));
                        @endphp
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Assinatura -->
    <div class="assinatura">
        <p>Assinatura e Carimbo do Médico</p>
    </div>

    <!-- Rodapé -->
    <div class="rodape">
        Clínica BitClin &copy; {{ now()->year }} - Todos os direitos reservados.
    </div>
</body>
</html>

