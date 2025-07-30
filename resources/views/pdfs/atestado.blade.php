<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 40px;
            font-size: 14px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            max-height: 100px;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 3px 0;
        }

        .texto {
            margin-top: 30px;
            line-height: 1.8;
            text-align: justify;
        }

        .footer {
            margin-top: 60px;
            text-align: right;
        }

        .assinatura {
            margin-top: 80px;
            text-align: center;
        }

        .assinatura hr {
            border: none;
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto;
        }

        .cid {
            margin-top: 15px;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('logo-clinica.png') }}" alt="Logo da Clínica">
    </div>

    <div class="title">Atestado Médico</div>

    <div class="info">
        <p><strong>Nome do Paciente:</strong> {{ $paciente->nome }}</p>
        <p><strong>CPF:</strong> {{ $paciente->cpf }}</p>
        <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
        <p><strong>Data:</strong> {{ $data }}</p>
    </div>

    <div class="texto">
        {!! nl2br(e($texto)) !!}
    </div>

    @if(!empty($cid))
        <div class="cid">
            <strong>CID(s):</strong> {{ $cid }}
        </div>
    @endif

    <div class="assinatura">
        <hr>
        <p>{{ $medico->name }} - CRM: {{ $medico->crm ?? '_____' }}</p>
    </div>

</body>
</html>

