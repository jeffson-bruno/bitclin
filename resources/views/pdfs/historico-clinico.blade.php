<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico Clínico</title>
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        position: relative;
    }

    .logo {
        text-align: center;
        margin-bottom: 10px;
    }

    .watermark {
        position: fixed;
        top: 60%; /* abaixo do centro */
        left: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.05;
        z-index: -1;
    }

    .watermark img {
        width: 500px;
    }

    h1, h2 {
        text-align: center;
    }

    .section {
        margin-top: 20px;
    }

    .divider {
        border-bottom: 1px solid #ccc;
        margin: 20px 0;
    }
</style>


</head>
<body>
<div class="watermark">
    <img src="{{ public_path('images/logo.png') }}" alt="Marca d'água">
</div>


<div class="logo">
    <img src="{{ public_path('images/logo.png') }}" width="160" alt="Logo Clínica">
</div>

<h1>Histórico Clínico</h1>

<div>
    <strong>Nome:</strong> {{ $paciente->nome }}<br>
    <strong>CPF:</strong> {{ $paciente->cpf }}<br>
    <strong>Data de Nascimento:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') }}<br>
    <strong>Idade:</strong> {{ \Carbon\Carbon::parse($paciente->data_nascimento)->age }} anos
</div>

@foreach ($prontuariosPorData as $data => $registros)
    @foreach ($registros as $registro)
        <div class="divider"></div>
        <h2>Atendimento em {{ $data }}</h2>

        @if ($registro->queixa_principal || $registro->historia_doenca || $registro->historico_progressivo)
            <div class="section">
                <h3>Anamnese</h3>

                @if ($registro->queixa_principal)
                    <p><strong>Queixa Principal:</strong> {{ $registro->queixa_principal }}</p>
                @endif

                @if ($registro->historia_doenca)
                    <p><strong>História da Doença:</strong> {{ $registro->historia_doenca }}</p>
                @endif

                @if ($registro->historico_progressivo)
                    <p><strong>Histórico Médico Progressivo:</strong> {{ $registro->historico_progressivo }}</p>
                @endif

                @if ($registro->historico_familiar)
                    <p><strong>Histórico Familiar:</strong> {{ $registro->historico_familiar }}</p>
                @endif

                @if ($registro->habitos_vida)
                    <p><strong>Hábitos de Vida:</strong> {{ $registro->habitos_vida }}</p>
                @endif

                @if ($registro->revisao_sistemas)
                    <p><strong>Revisão de Sistemas:</strong> {{ $registro->revisao_sistemas }}</p>
                @endif
            </div>
        @endif


        @if ($registro->exames && is_array($registro->exames))
            <div class="section">
                <h3>Solicitação de Exames</h3>
                <ul>
                    @foreach ($registro->exames as $exame)
                        <li>{{ $exame }}</li> {{-- trata $exame como string --}}
                    @endforeach
                </ul>
            </div>
        @endif


        @if (!empty($registro->receitas))
            <div class="section">
                <h3>Receita Médica</h3>
                <ul>
                    @foreach ($registro->receitas as $receita)
                        <li>
                            @if (is_array($receita))
                                {{ $receita['nome'] ?? 'Medicamento' }}
                                ({{ $receita['tipo'] ?? '?' }})
                                –
                                {{ $receita['mg'] ?? '' }}

                                @php
                                    $detalhes = $receita['detalhes'] ?? [];
                                    $frequencia = collect($detalhes)->filter()->map(function($valor, $chave) {
                                        return "$valor $chave";
                                    })->implode(', ');
                                @endphp

                                @if ($frequencia)
                                    – {{ $frequencia }}
                                @endif

                                @if (!empty($receita['intervaloHoras']))
                                    – a cada {{ $receita['intervaloHoras'] }} horas
                                @endif

                            @else
                                {{ $receita }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif



    

       @if (!empty($registro->atestados))
            <div class="section">
                <h3>Atestado Médico</h3>
                @foreach ($registro->atestados as $atestado)
                    @php
                        $cid = is_array($atestado) ? ($atestado['cid'] ?? null) : null;
                        $texto = is_array($atestado) ? ($atestado['texto'] ?? null) : $atestado;
                    @endphp

                    @if ($texto)
                        <p>{{ $texto }} @if ($cid) – <strong>CID:</strong> {{ $cid }} @endif</p>
                    @endif
                @endforeach
            </div>
        @endif




    @endforeach
@endforeach

</body>
</html>
