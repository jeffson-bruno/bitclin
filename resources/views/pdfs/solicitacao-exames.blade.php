<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Solicitação de Exames</title>
  <style>
  body {
    font-family: "DejaVu Sans", sans-serif;
    font-size: 12px;
    margin: 0;
    padding: 0;
    position: relative;
    height: 100%;
  }

  /* LOGO NO TOPO DIREITO */
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

  /* TÍTULO CENTRAL */
  .titulo {
    margin-top: 50px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
  }

  .paciente-info {
    margin: 40px 30px 20px 30px;
  }

  .paciente-info p {
    margin: 8px 0;
  }

  .exames {
    margin: 40px 30px;
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
    top: 52%;
    left: 50%;
    width: 400px;
    opacity: 0.05;
    transform: translate(-50%, -50%);
    z-index: 0;
  }

  .content {
    position: relative;
    z-index: 1;
    padding-bottom: 120px;
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

  <!-- Marca d'água -->
  <img src="{{ public_path('images/logo.png') }}" alt="Marca d'água" class="watermark">

  <!-- Faixa vermelha no topo -->
  <div class="header-wave">
    <img src="{{ public_path('images/header-wave.png') }}" width="100%" alt="Onda vermelha">
  </div>

  <!-- Logo no canto direito -->
  <div class="header">
    <img src="{{ public_path('images/logo.png') }}" width="180" alt="Logo da Clínica">
  </div>

  <div class="content">

    <!-- Título centralizado -->
    <div class="titulo">Solicitação de Exames</div>

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
          <p style="font-weight:600; margin-top:6px;">
              {{ $medico->name }}
          </p>
          <p>
              {{ $registro ?? 'Registro não informado' }}
          </p>
      </div>
    </div>

  </div>

  <!-- Rodapé fixo -->
  <div class="footer">
    R. Areolino de Abreu, 346 – Centro – CEP 64.235-000 – Cocal - PI<br>
    Tel: (86) 9 9918-8366 • @pontoclinico_eulila
  </div>

  <div class="footer-wave">
    <img src="{{ public_path('images/footer-wave.png') }}" width="100%" alt="Onda azul">
  </div>
</body>
</html>
