<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Relatório de Consultas - {{ $hoje }}</title>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    th { background-color: #eee; }
  </style>
</head>
<body>
  <h2>Relatório de Pacientes - Consultas em {{ $hoje }}</h2>

  <table>
    <thead>
      <tr>
        <th>Nome</th>
        <th>Data de Cadastro</th>
        <th>Procedimento</th>
        <th>Telefone</th>
        <th>Preço</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($pacientes as $p)
        <tr>
          <td>{{ $p->nome }}</td>
          <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}</td>
          <td>{{ ucfirst($p->procedimento) }}</td>
          <td>{{ $p->telefone ?? '-' }}</td>
          <td>R$ {{ number_format($p->preco, 2, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
