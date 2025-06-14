<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Atendimento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .via {
            position: relative;
            margin-bottom: 90px;
            padding: 40px;
            border: 1px solid #000;
        }

        .marca-dagua-1 {
            position: absolute;
            top: 20%; /* Ajuste a porcentagem para mover mais para cima ou para baixo */
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1; /* Diminui a opacidade para ficar mais suave */
            pointer-events: none;
            z-index: 0;
            width: 210px; /* Ajuste o tamanho da marca d'água conforme necessário */
            height: auto;
        }

        .marca-dagua-2 {
            position: absolute;
            top: 20%; /* Ajuste a porcentagem para posicionar a marca d'água na segunda via */
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1; /* Opacidade mais suave */
            pointer-events: none;
            z-index: 0;
            width: 210px; /* Ajuste o tamanho da marca d'água conforme necessário */
            height: auto;
        }

        .cabecalho {
            width: 100%;
            margin-bottom: 20px;
        }

        .cabecalho td {
            vertical-align: top;
        }

        .logo {
            width: 130px;
            height: auto;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }

        .data-impressao {
            font-size: 12px;
            text-align: right;
        }

        .subtitulo {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .espaco {
            height: 15px;
        }

        .linha td {
            padding: 5px 10px;
        }

        .procedimento {
            background-color: #eee;
            padding: 8px;
            font-weight: bold;
            border-left: 3px solid #000;
        }

        .rodape {
            text-align: center;
            font-size: 11px;
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .divisoria {
            text-align: center;
            font-size: 12px;
            margin: 30px;
            border-top: 1px dashed #000;
        }
    </style>
</head>
<body>

<!-- VIA DO PACIENTE -->
<div class="via">
    <img src="{{ public_path('images/marca-dagua.png') }}" class="marca-dagua-1" alt="Marca d'água">

    <table class="cabecalho">
        <tr>
            <td><img src="{{ public_path('images/logo.jpg') }}" class="logo" alt="Logo"></td>
            <td>
                <div class="titulo">Ficha de Atendimento</div>
                <div class="data-impressao">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="subtitulo">Via do Paciente</div>

    <table width="100%">
        <tr class="linha">
            <td><strong>Nome:</strong> {{ $paciente->nome }}</td>
            <td><strong>CPF:</strong> {{ $paciente->cpf }}</td>
            <td><strong>Data de Nascimento:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td><strong>Estado Civil:</strong> {{ $paciente->estado_civil }}</td>
            <td><strong>Telefone:</strong> {{ $paciente->telefone }}</td>
            <td></td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3"><strong>Endereço:</strong> {{ $paciente->endereco }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3" class="procedimento">Procedimento: {{ $procedimento['procedimento'] }}</td>
        </tr>
    </table>

    <div class="rodape">
        Rua das Clínicas, 123 - Bairro Saúde - São Paulo/SP - CEP: 01234-567<br>
        (11) 91234-5678 | @clinica.exemplo
    </div>
</div>

<div class="divisoria">---------------- Corte Aqui ----------------</div>

<!-- VIA DA CLÍNICA -->
<div class="via">
    <img src="{{ public_path('images/marca-dagua.png') }}" class="marca-dagua-2" alt="Marca d'água">

    <table class="cabecalho">
        <tr>
            <td><img src="{{ public_path('images/logo.jpg') }}" class="logo" alt="Logo"></td>
            <td>
                <div class="titulo">Ficha de Atendimento</div>
                <div class="data-impressao">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="subtitulo">Via da Clínica</div>

    <table width="100%">
        <tr class="linha">
            <td><strong>Nome:</strong> {{ $paciente->nome }}</td>
            <td><strong>CPF:</strong> {{ $paciente->cpf }}</td>
            <td><strong>Data de Nascimento:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td><strong>Estado Civil:</strong> {{ $paciente->estado_civil }}</td>
            <td><strong>Telefone:</strong> {{ $paciente->telefone }}</td>
            <td></td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3"><strong>Endereço:</strong> {{ $paciente->endereco }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3" class="procedimento">Procedimento: {{ $procedimento['procedimento'] }}</td>
        </tr>
        <tr class="linha">
            <td colspan="3"><strong>Valor:</strong> R$ {{ number_format($procedimento['valor'], 2, ',', '.') }} - 
                <strong>Status:</strong> {{ $procedimento['pago'] ? 'PAGO' : 'PENDENTE' }}
            </td>
        </tr>
    </table>

    <div class="rodape">
        Rua das Clínicas, 123 - Bairro Saúde - São Paulo/SP - CEP: 01234-567<br>
        (11) 91234-5678 | @clinica.exemplo
    </div>
</div>

</body>
</html>

