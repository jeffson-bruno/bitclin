<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro - {{ $periodo }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 30px;
            position: relative;
        }

        .header {
            position: absolute;
            top: 2px;
            right: 20px;
            margin-top: -50px;
            z-index: 2;
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
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #444;
            padding: 6px;
            text-align: left;
        }

        .total {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" width = "180" alt="Logo da Clínica">
    </div>

    <img class="logo-marca" src="{{ public_path('images/logo.png') }}" width="400" alt="Marca d'água">

    <h1>Relatório Financeiro - {{ ucfirst($periodo) }}</h1>

    <p class="info"><strong>Período:</strong> {{ $data_inicio }} a {{ $data_fim }}</p>

    <h2>Entradas</h2>
    <table>
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Pago</th>
                <th>Forma de Pagamento</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entradas as $e)
                <tr>
                    <td>{{ $e->nome ?? 'N/A' }}</td>
                    <td>{{ ucfirst($e->procedimento) }}</td>
                    <td>R$ {{ number_format($e->preco, 2, ',', '.') }}</td>
                    <td>{{ $e->pago ? 'Sim' : 'Não' }}</td>
                    <td>{{ ucfirst($e->forma_pagamento) }}</td>
                    <td>{{ \Carbon\Carbon::parse($e->data_pagamento)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">Total de Entradas</td>
                <td colspan="4">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Despesas</h2>
    <table>
        <thead>
            <tr>
                <th>Nome da Despesa</th>
                <th>Valor</th>
                <th>Data de Pagamento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($despesas as $d)
                <tr>
                    <td>{{ $d->nome }}</td>
                    <td>R$ {{ number_format($d->valor, 2, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($d->data_pagamento)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total de Despesas</td>
                <td colspan="2">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Resumo</h2>
    <p><strong>Saldo Final:</strong> R$ {{ number_format($saldo, 2, ',', '.') }}</p>
</body>
</html>
