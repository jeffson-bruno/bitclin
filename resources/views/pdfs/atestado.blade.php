<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 14px;
        position: relative;
        margin: 0;
        padding: 0;
        position: relative;
        height: 100%;
    }

    .header {
        position: absolute;
        top: 20px;
        right: 30px;
        z-index: 2;
    }

    .header-wave {
        margin-top: -50px;
        margin-left: -50px;
    }

    .header img {
        max-height: 90px;
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

    .assinatura {
        margin-top: 130px;
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

    .watermark {
        position: fixed;
        top: 53%;
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.05;
        z-index: -1;
    }

    .watermark img {
        width: 500px;
    }


    .footer-wave {
        position: fixed;
        bottom: -47px;
        left: 0;
        width: 220%;
        margin-left: -50%;
        z-index: -1;
    }
    .footer {
        position: fixed;
        bottom: 30px;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 11px;
        color: #444;
    }
</style>

</head>
<body>

    <div class="watermark">
        <img src="{{ public_path('images/logo.png') }}" alt="Marca d'água">
    </div>

    <div class="header-wave">
        <img src="{{ public_path('images/header-wave.png') }}" width="100%" alt="Onda vermelha">
    </div>

    <div class="header">
        <img src="{{ public_path('images/logo.png') }}"width="180" alt="Logo da Clínica">
    </div>

    


    <div class="title">Atestado Médico</div>

    @php
        $cpfFormatado = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $paciente->cpf ?? '');
    @endphp


    <div class="info">
        <p><strong>Nome do Paciente:</strong> {{ $paciente->nome }}</p>
        <p><strong>CPF:</strong> {{ $cpfFormatado }}</p>
        <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
        <p><strong>Data Emissão:</strong> {{ $data }}</p>
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



    <div class="footer">
        Rua das Clínicas, 123 – Centro – CEP 12345-678 – Cidade/UF<br>
        Tel: (00) 0000-0000 • @clinicaexemplo
    </div>

    <div class="footer-wave">
        <img src="{{ public_path('images/footer-wave.png') }}" width="100%" alt="Onda azul">
    </div>



</body>
</html>

