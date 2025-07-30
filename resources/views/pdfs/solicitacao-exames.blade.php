<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Solicitação de Exames</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      position: relative;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .logo {
      width: 120px;
    }

    .paciente-info {
      margin-bottom: 20px;
    }

    .paciente-info p {
      margin: 3px 0;
    }

    .exames {
      margin-top: 20px;
    }

    .exames ul {
      padding-left: 18px;
    }

    .assinatura {
      margin-top: 60px;
      text-align: right;
    }

    .data-solicitacao {
      margin-top: 20px;
      text-align: right;
    }

    .watermark {
      position: absolute;
      top: 35%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 60px;
      color: rgba(0, 0, 0, 0.05);
      z-index: 0;
      white-space: nowrap;
    }

    .content {
      position: relative;
      z-index: 1;
    }
  </style>
</head>
<body>

  <div class="watermark">Clínica Santa Esperança</div>

  <div class="content">

    <!-- Cabeçalho -->
    <div class="header">
      <div>
        <h2>Solicitação de Exames</h2>
      </div>
      <div>
        <img src="{{ public_path('images/logo.png') }}" alt="Logo Clínica" class="logo">
      </div>
    </div>

    <!-- Dados do Paciente -->
    <div class="paciente-info">
      <p><strong>Nome:</strong> {{ $paciente->nome }}</p>
      <p><strong>CPF:</strong> {{ $paciente->cpf }}</p>
      <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
    </div>

    <!-- Lista de Exames -->
    <div class="exames">
      <p><strong>Exames Solicitados:</strong></p>
      <ul>
        @foreach($exames as $exame)
          <li>{{ $exame }}</li>
        @endforeach
      </ul>
    </div>

    <!-- Assinatura e Data -->
    <div class="data-solicitacao">
      <p><strong>Data:</strong> {{ $data }}</p>
    </div>

    <div class="assinatura">
      <p>{{ $medico->name }}</p>
      <p>CRM: {{ $medico->crm ?? '__________' }}</p>
    </div>
    
  </div>

</body>
</html>
