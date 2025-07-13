<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Anual</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 30px;
            position: relative;
        }

        .logo-marca {
            position: absolute;
            top: 40%;
            left: 30%;
            opacity: 0.06;
            z-index: -1;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #444;
            padding: 6px;
            text-align: center;
        }

        .total {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .highlight {
            background-color: #d0f0d0;
        }
    </style>
</head>
<body>
    <img class="logo-marca" src="{{ public_path('logo_clinica.png') }}" width="400" alt="Marca d'água">

    <h1>Relatório Financeiro Anual - {{ $ano }}</h1>

    <table>
        <thead>
            <tr>
                <th>Mês</th>
                <th>Entradas (R$)</th>
                <th>Despesas (R$)</th>
                <th>Saldo (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($relatorioMensal as $mes => $dados)
                <tr
                    @if ($mes == $mesMaisLucro)
                        class="highlight"
                    @elseif ($mes == $mesMaisGasto)
                        style="background-color: #fdd;"
                    @endif
                >
                    <td>{{ ucfirst($mes) }}</td>
                    <td>{{ number_format($dados['entradas'], 2, ',', '.') }}</td>
                    <td>{{ number_format($dados['despesas'], 2, ',', '.') }}</td>
                    <td>{{ number_format($dados['saldo'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total Anual</td>
                <td>{{ number_format($totalEntradas, 2, ',', '.') }}</td>
                <td>{{ number_format($totalDespesas, 2, ',', '.') }}</td>
                <td>{{ number_format($saldoAnual, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <br>

    <p><strong>Mês com maior lucro:</strong> {{ ucfirst($mesMaisLucro) }}</p>
    <p><strong>Mês com maior gasto:</strong> {{ ucfirst($mesMaisGasto) }}</p>
</body>
</html>
