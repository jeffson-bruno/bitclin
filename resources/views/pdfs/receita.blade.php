<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .marca-dagua {
        position: absolute;
        top: 35%;
        left: 25%;
        opacity: 0.1;
        z-index: 0;
    }
    .conteudo {
        position: relative;
        z-index: 1;
    }
    .rodape {
        position: absolute;
        bottom: 40px;
        text-align: center;
        font-size: 10px;
        width: 100%;
    }
  </style>
</head>
<body>
  <img src="{{ public_path('images/logo-clinica.png') }}" class="marca-dagua" width="300" alt="Marca d'água">
  <div class="conteudo">
    <h2 style="text-align: center;">Receita Médica</h2>

    <p><strong>Paciente:</strong> {{ $paciente->nome }}</p>
    <p><strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos</p>
    <p><strong>Médico:</strong> {{ $medico->nome }}</p>
    <p><strong>CRM:</strong> {{ $crm ?? '—' }}</p>

    <h4>Prescrição:</h4>
    <ul>
      @foreach ($medicamentos as $med)
        <li>
          <strong>{{ $med['nome'] }}</strong> - {{ $med['quantidade'] }} caixas - {{ $med['tipo'] ?? '' }}
          @if($med['intervaloHoras']) (a cada {{ $med['intervaloHoras'] }}h) @endif
        </li>
      @endforeach
    </ul>
  </div>

  <div class="rodape">
    Clínica Exemplo - Rua Projetada 01, Cocal-PI<br>
    Telefone: (86) 9 9999-9999 | CEP: 64230-000<br>
    Instagram: @clinicaexemplo | Facebook: /clinicaexemplo
  </div>
</body>
</html>
