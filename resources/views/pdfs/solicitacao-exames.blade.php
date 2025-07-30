<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Solicitação de Exames</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 0;
      position: relative;
      height: 100%;
    }

    .header {
      position: relative;
      margin: 0 30px 30px 30px;
      text-align: center;
    }

    .header img.logo {
      position: absolute;
      top: 10px;
      right: 0;
      width: 120px;
    }

    .header h2 {
      margin-top: 100px;
      font-size: 18px;
    }

    .paciente-info {
      margin: 0 30px 40px 30px;
    }

    .paciente-info p {
      margin: 8px 0;
    }

    .exames {
      margin: 60px 30px 40px 30px;
    }

    .exames ul {
      padding-left: 20px;
    }

    .data-solicitacao {
      margin-top: 40px;
      margin-right: 30px;
      text-align: right;
    }

    .assinatura-box {
      margin-top: 100px;
      text-align: center;
    }

    .linha-assinatura {
      border-top: 1px solid #000;
      width: 250px;
      margin: 0 auto 5px auto;
    }

    .assinatura-info {
      font-size: 12px;
      text-align: center;
    }

    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 400px;
      opacity: 0.05;
      transform: translate(-50%, -50%);
      z-index: 0;
    }

    .content {
      position: relative;
      z-index: 1;
      padding-bottom: 120px; /* Reservar espaço para o rodapé */
    }

    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      text-align: center;
      font-size: 10px;
      background-color: #fff;
    }

    .footer-info {
      padding: 10px 20px 5px 20px;
    }

    .linha-footer-vermelha {
      height: 8px;
      background-color: #d10d0d;
    }

    .linha-footer-azul {
      height: 10px;
      background-color: #1671de;
    }
  </style>
</head>
<body>

  <!-- Marca d'água -->
  <img src="{{ public_path('images/logo.png') }}" alt="Marca d'água" class="watermark">

  <div class="content">

    <!-- Cabeçalho com logo e título -->
    <div class="header">
      <img src="{{ public_path('images/logo.png') }}" alt="Logo Clínica" class="logo">
      <h2>Solicitação de Exames</h2>
    </div>

    <!-- Informações do Paciente -->
    <div class="paciente-info">
      <p><strong>Nome:</strong> {{ $paciente->nome }}</p>
      <p><strong>CPF:</strong> {{ $paciente->cpf }}</p>
      <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
    </div>

    <!-- Exames Solicitados -->
    <div class="exames">
      <p><strong>Exames Solicitados:</strong></p>
      <ul>
        @foreach($exames as $exame)
          <li>{{ $exame }}</li>
        @endforeach
      </ul>
    </div>

    <!-- Data -->
    <div class="data-solicitacao">
      <p><strong>Data:</strong> {{ $data }}</p>
    </div>

    <!-- Assinatura -->
    <div class="assinatura-box">
      <div class="linha-assinatura"></div>
      <div class="assinatura-info">
        <p>{{ $medico->name }}</p>
        <p>CRM: {{ $medico->crm ?? '__________' }}</p>
      </div>
    </div>

  </div>

  <!-- Rodapé fixo -->
  <div class="footer">
    <div class="footer-info">
      Clínica Santa Esperança - Rua da Saúde, 123 - Centro, Cidade/UF - CEP: 12345-678<br>
      Tel: (99) 99999-9999 • Instagram: @clinicasantaesperanca
    </div>
    <div class="linha-footer-vermelha"></div>
    <div class="linha-footer-azul"></div>
  </div>

</body>
</html>
