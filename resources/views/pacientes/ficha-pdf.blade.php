<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Atendimento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .cabecalho {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid #000;
            padding: 10px;
        }
        .cabecalho-info {
            display: flex;
            text-align: right;
            justify-content: flex-start;
        }
        .titulo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .data-impressao {
            font-size: 13px;
            color: #555;
        }
        .logo {
            width: 180px;
            margin-bottom: 28px;
            object-fit: contain;
        }
        .tabela {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tabela th, .tabela td {
            border: none;
            padding: 6px 0;
            text-align: left;
        }
        .subtitulo {
            margin-top: 20px;
            text-align: center;
            font-size: 16px;
        }
        .divisoria {
            margin: 20px 0;
            text-align: center;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="cabecalho">
        <div class="cabecalho-info">
            <div class="titulo">Ficha de Atendimento</div>
            <div class="data-impressao">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        </div>
        <img src="{{ public_path('images/logo.jpg') }}" alt="Logo" class="logo">
    </div>

    <h3 class="subtitulo">Via do Paciente</h3>

    <table class="tabela">
        <tr><th>Nome</th><td>{{ $paciente->nome }}</td></tr>
        <tr><th>Estado Civil</th><td>{{ $paciente->estado_civil }}</td></tr>
        <tr><th>CPF</th><td>{{ $paciente->cpf }}</td></tr>
        <tr><th>Telefone</th><td>{{ $paciente->telefone }}</td></tr>
        <tr><th>Endereço</th><td>{{ $paciente->endereco }}</td></tr>
        <tr><th>Data de Nascimento</th><td>{{ \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') }}</td></tr>
        <tr><th>Procedimento</th><td>{{ $procedimento['procedimento'] }}</td></tr>
    </table>

    <div class="divisoria">---------------- Corte Aqui ----------------</div>

    <div class="cabecalho">
        <div class="cabecalho-info">
            <div class="titulo">Ficha de Atendimento</div>
            <div class="data-impressao">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        </div>
        <img src="{{ public_path('images/logo.jpg') }}" alt="Logo" class="logo">
    </div>

    <h3 class="subtitulo">Via da Clínica</h3>

    <table class="tabela">
        <tr><th>Nome</th><td>{{ $paciente->nome }}</td></tr>
        <tr><th>Estado Civil</th><td>{{ $paciente->estado_civil }}</td></tr>
        <tr><th>CPF</th><td>{{ $paciente->cpf }}</td></tr>
        <tr><th>Telefone</th><td>{{ $paciente->telefone }}</td></tr>
        <tr><th>Endereço</th><td>{{ $paciente->endereco }}</td></tr>
        <tr><th>Data de Nascimento</th><td>{{ \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') }}</td></tr>
        <tr><th>Procedimento</th><td>{{ $procedimento['procedimento'] }}</td></tr>
        <tr><th>Valor</th><td>R$ {{ number_format($procedimento['valor'], 2, ',', '.') }}</td></tr>
        <tr><th>Status</th><td>{{ $procedimento['pago'] ? 'PAGO' : 'PENDENTE' }}</td></tr>
    </table>

</body>
</html>


