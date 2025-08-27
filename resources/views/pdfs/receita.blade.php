<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            position: relative;
            margin: 0;
            padding: 0;
            position: relative;
            height: 100%;
        }

        .header {
            position: absolute;
            top: 10px;
            right: 30px;
            z-index: 2;
        }

        .header img {
            max-height: 90px;
        }

        .header-wave {
            margin-top: -50px;
            margin-left: -50px;

        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 20px;
        }

        .info p {
            margin: 3px 0;
        }

        .medicamentos {
            margin-top: 30px;
        }

        .linha-medicamento {
            margin-bottom: 15px;
        }

        .linha-texto,
        .linha-detalhes {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .linha-texto .esquerda,
        .linha-detalhes .esquerda {
            width: 25%;
            border-bottom: 1px solid #000;
            padding-right: 10px;
        }

        .linha-texto .direita,
        .linha-detalhes .direita {
            width: 20%;
            text-align: right;
            border-bottom: 1px solid #000;
            padding-left: 10px;
        }

        .linha-detalhes .esquerda {
            border-bottom: 1px dotted #000;
        }

        .linha-detalhes .direita {
            border-bottom: 1px dotted #000;
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

        .assinatura {
            margin-top: 60px;
            text-align: right;
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

        .assinatura {
            margin-top: 40px;
            text-align: center;   
            }

            .assinatura-linha {
            width: 220px;        
            border-top: 1px solid #000;
            margin: 0 auto 6px;   
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
        <img src="{{ public_path('images/logo.png') }}" width = "180" alt="Logo da Clínica">
    </div>

    <div class="title">Receita Médica</div>

   <div class="info">
        <p><strong>Nome do Paciente:</strong> {{ $paciente->nome }}</p>
        <p><strong>Idade:</strong> {{ optional(\Carbon\Carbon::parse($paciente->data_nascimento))->age ?? '—' }} anos</p>
        <p><strong>Data:</strong> {{ now()->format('d/m/Y') }}</p>

        {{-- ERA $crm. Trocar por $registro e com fallback pelo próprio usuário --}}
            @php
                $reg = $registro
                    ?? ( ($medico->registro_tipo && $medico->registro_numero)
                        ? (($medico->registro_uf ? ($medico->registro_tipo.'-'.strtoupper($medico->registro_uf)) : $medico->registro_tipo).' '.$medico->registro_numero)
                        : null
                    );
            @endphp
        </p>
    </div>


    <div class="medicamentos">
        <table style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif;">
            @foreach($medicamentos as $med)
                @php
                    $linhaPontos = str_repeat('.', 30);
                    $tipo = $med['tipo'] ?? '';

                    $nome = $med['nome'] ?? '';
                    $quantidade = $med['quantidade'] ?? '';
                    $mg = isset($med['mg']) && !empty($med['mg']) ? $med['mg'] . (str_contains($med['mg'], 'ml') ? '' : 'mg') : '';
                    $intervalo = !empty($med['intervaloHoras']) ? $med['intervaloHoras'] . ' hs' : '';
                    $quantidadeTipo = '';
                    $dosagem = $med['dosagem'] ?? '';

                    // Ajustar quantidadeTipo com base no tipo
                    if ($tipo === 'Comprimido' && !empty($med['detalhes']['comprimidos'])) {
                        $quantidadeTipo = $med['detalhes']['comprimidos'] . ' Comp.';
                    } elseif ($tipo === 'Gotas' && !empty($med['detalhes']['gotas'])) {
                        $quantidadeTipo = $med['detalhes']['gotas'] . ' Gotas';
                    } elseif ($tipo === 'Líquido' && !empty($med['detalhes']['ml'])) {
                        $quantidadeTipo = $med['detalhes']['ml'] . ' ml';
                    } elseif ($tipo === 'Injetável' && !empty($med['detalhes']['ampolas'])) {
                        $quantidadeTipo = $med['detalhes']['ampolas'] . ' Amp';
                    }
                @endphp

                @if($tipo === 'Injetável')
                    {{-- Linha 1: Nome + ampola --}}
                    <tr>
                        <td colspan="2" style="padding: 4px;">
                            {{ $nome }} {{ $linhaPontos }} {{ $quantidadeTipo }}
                        </td>
                    </tr>

                    {{-- Linha 2: Dosagem --}}
                    @if(!empty($dosagem))
                        <tr>
                            <td colspan="2" style="padding: 4px;">
                                {{ $dosagem }} {{ !empty($med['unidade']) ? $med['unidade'] : 'mg/ml' }}
                            </td>
                        </tr>
                    @endif
                @else
                    {{-- Linha 1: Nome + mg/ml + pontos + quantidade de caixa --}}
                    <tr>
                        <td colspan="2" style="padding: 4px;">
                            {{ $nome }}
                            @if(!empty($med['mg']))
                                - {{ $med['mg'] }}
                            @endif
                            {{ $linhaPontos }}
                            {{ $quantidade }} {{ ($quantidade ?? 1) > 1 ? 'Cxs' : 'Cx' }}
                        </td>
                    </tr>

                    {{-- Linha 2: Tipo e intervalo --}}
                    <tr>
                        <td colspan="2" style="padding: 4px;">
                            {{ $quantidadeTipo }} {{ str_repeat('.', 20) }} {{ $intervalo }}
                        </td>
                    </tr>
                @endif

                {{-- Linha 3 (ambos os casos): Instrução de Aplicação, se houver --}}
                @if(!empty($med['instrucao']))
                    <tr>
                        <td colspan="2" style="padding: 4px; font-style: italic;">
                            Instrução: {{ $med['instrucao'] }}
                        </td>
                    </tr>
                @endif

                {{-- Espaçamento entre medicamentos --}}
                <tr><td colspan="2" style="height: 15px;"></td></tr>
            @endforeach
        </table>
    </div>




   <div class="assinatura">
        <div class="assinatura-linha"></div>
        <p style="font-weight:600; margin-top:6px;">{{ $medico->name }}</p>
        <p>
            @php
            // Usa $registro passado pelo controller; se não vier, monta pelo usuário
            $regFinal = $registro
                ?? ( ($medico->registro_tipo && $medico->registro_numero)
                    ? (($medico->registro_uf ? ($medico->registro_tipo.'-'.strtoupper($medico->registro_uf)) : $medico->registro_tipo).' '.$medico->registro_numero)
                    : null
                );
            @endphp
            {{ $regFinal ?? 'Registro não informado' }}
        </p>
        {{-- opcional: usuário abaixo da assinatura --}}
        {{-- <p style="font-size:12px; color:#555;">Usuário: {{ $medico->usuario }}</p> --}}
        </div>



    <div class="footer">
        R. Areolino de Abreu, 346 – São Pedro – CEP 64235-000 – Cocal - PI<br>
        Tel: (86) 99918-8366 • @pontoclinico_eulila
    </div>

    <div class="footer-wave">
        <img src="{{ public_path('images/footer-wave.png') }}" width="100%" alt="Onda azul">
    </div>

</body>
</html>

