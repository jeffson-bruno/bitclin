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
            padding: 16px;
        }

        .via {
            position: relative;
            margin-bottom: 28px;
            padding: 28px;
            border: 1px solid #000;
            page-break-inside: avoid;
        }

        .via-clinica { 
            margin-top: -12px;   
        }

        .marca-dagua-1,
        .marca-dagua-2 {
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            pointer-events: none;
            z-index: 0;
            width: 210px;
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
            height: 10px;
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

@php
    $textoProcedimento = '';

    if ($paciente->procedimento === 'consulta') {
        $especialidade = $paciente->medico->especialidade->nome ?? 'Especialidade';
        $textoProcedimento = 'Consulta com ' . $especialidade;
    } elseif ($paciente->procedimento === 'exame') {
        $exameNome = $paciente->exame->nome ?? 'não especificado';
        $textoProcedimento = 'Exame / ' . $exameNome;
    }

    $status = 'Não pago';
    if ($paciente->pago) {
        if ($paciente->forma_pagamento === 'pix') {
            $status = 'Pago via Pix';
        } elseif ($paciente->forma_pagamento === 'cartao') {
            $status = 'Pago com Cartão';
        } elseif ($paciente->forma_pagamento === 'dinheiro') {
            $status = 'Pagamento efetuado à vista';
        }
    }
@endphp

@php
    // Helpers de formatação para PDF
    use Carbon\Carbon;

    function idade($dataNasc) {
        if (empty($dataNasc)) return '—';
        try {
            return Carbon::parse($dataNasc)->age . ' anos';
        } catch (\Exception $e) {
            return '—';
        }
    }

    function soDigitos($v) {
        return preg_replace('/\D+/', '', (string) $v);
    }

    function formatCPF($v) {
        $d = soDigitos($v);
        if (strlen($d) !== 11) return $v;
        return substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2);
    }

    function formatTelefoneBR($v) {
        $d = soDigitos($v);
        if (strlen($d) === 10) { // fixo: (AA) NNNN-NNNN
            return '('.substr($d,0,2).') '.substr($d,2,4).'-'.substr($d,6,4);
        } elseif (strlen($d) === 11) { // celular: (AA) NNNNN-NNNN
            return '('.substr($d,0,2).') '.substr($d,2,5).'-'.substr($d,7,4);
        }
        return $v; // mantém como veio se não bater com 10/11
    }
@endphp


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
            <td><strong>CPF:</strong> {{ formatCPF($paciente->cpf) }}</td>
            <td><strong>Idade:</strong> {{ idade($paciente->data_nascimento) }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td><strong>Estado Civil:</strong> {{ $paciente->estado_civil }}</td>
            <td><strong>Telefone:</strong> {{ formatTelefoneBR($paciente->telefone) }}</td>
            <td></td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3"><strong>Endereço:</strong> {{ $paciente->endereco }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3" class="procedimento">Procedimento: {{ $textoProcedimento }}</td>
        </tr>

        @if ($paciente->procedimento === 'exame')
            <tr class="linha">
                <td><strong>Turno:</strong> {{ ucfirst($paciente->turno_exame) }}</td>
                <td><strong>Dia da Semana:</strong>
                    @php
                        $dias = is_array(json_decode($paciente->dia_semana_exame))
                            ? json_decode($paciente->dia_semana_exame)
                            : [$paciente->dia_semana_exame];
                    @endphp

                    {{ implode(', ', array_map(fn($d) => ucfirst($d), $dias)) }}
                </td>

                <td></td>
            </tr>
        @endif
    </table>

    <div class="rodape">
        Rua das Clínicas, 123 - Bairro Saúde - São Paulo/SP - CEP: 01234-567<br>
        (11) 91234-5678 | @clinica.exemplo
    </div>
</div>

<!-- VIA DA CLÍNICA -->
<div class="via via-clinica">
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
            <td><strong>CPF:</strong> {{ formatCPF($paciente->cpf) }}</td>
            <td><strong>Idade:</strong> {{ idade($paciente->data_nascimento) }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td><strong>Estado Civil:</strong> {{ $paciente->estado_civil }}</td>
            <td><strong>Telefone:</strong> {{ formatTelefoneBR($paciente->telefone) }}</td>
            <td></td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3"><strong>Endereço:</strong> {{ $paciente->endereco }}</td>
        </tr>
        <tr class="linha espaco"><td colspan="3"></td></tr>

        <tr class="linha">
            <td colspan="3" class="procedimento">Procedimento: {{ $textoProcedimento }}</td>
        </tr>

        @if ($paciente->procedimento === 'exame')
            <tr class="linha">
                <td><strong>Turno:</strong> {{ ucfirst($paciente->turno_exame) }}</td>
                <td><strong>Dia da Semana:</strong> {{ ucfirst($paciente->dia_semana_exame) }}</td>
                <td></td>
            </tr>
        @endif

        <tr class="linha">
            <td colspan="3">
                <strong>Valor:</strong> R$ {{ number_format($paciente->preco, 2, ',', '.') }} - 
                <strong>Status:</strong> {{ $status }}
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
