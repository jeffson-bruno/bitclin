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

        @php
            $an = isset($registro->anamnese) && (is_array($registro->anamnese) || is_object($registro->anamnese))
                ? (object) $registro->anamnese
                : $registro;
        @endphp

        @if (
            !empty($an->queixa_principal) ||
            !empty($an->historia_doenca) ||
            !empty($an->historico_progressivo) ||
            !empty($an->historico_familiar) ||
            !empty($an->habitos_vida) ||
            !empty($an->revisao_sistemas) ||
            !empty($an->outras_observacoes) ||
            !empty($an->resumo)
        )
            <div class="section">
                <h3>Anamnese</h3>

                @if (!empty($an->queixa_principal))
                    <p><strong>Queixa Principal:</strong> {{ $an->queixa_principal }}</p>
                @endif
                @if (!empty($an->historia_doenca))
                    <p><strong>História da Doença:</strong> {{ $an->historia_doenca }}</p>
                @endif
                @if (!empty($an->historico_progressivo))
                    <p><strong>Histórico Médico Progressivo:</strong> {{ $an->historico_progressivo }}</p>
                @endif
                @if (!empty($an->historico_familiar))
                    <p><strong>Histórico Familiar:</strong> {{ $an->historico_familiar }}</p>
                @endif
                @if (!empty($an->habitos_vida))
                    <p><strong>Hábitos de Vida:</strong> {{ $an->habitos_vida }}</p>
                @endif
                @if (!empty($an->revisao_sistemas))
                    <p><strong>Revisão de Sistemas:</strong> {{ $an->revisao_sistemas }}</p>
                @endif

                {{-- NOVOS --}}
                @if (!empty($an->outras_observacoes))
                    <p><strong>Outras Observações:</strong> {{ $an->outras_observacoes }}</p>
                @endif
                @if (!empty($an->resumo))
                    <p><strong>Resumo:</strong> {{ $an->resumo }}</p>
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


        @php
    
        $receitas = is_array($registro->receitas ?? null)
                ? $registro->receitas
                : (is_string($registro->receitas ?? null) ? json_decode($registro->receitas, true) : []);
        @endphp

        @if (!empty($receitas))
            <div class="section">
                <h3>Receitas</h3>
                <ul>
                    @foreach ($receitas as $r)
                        @if (is_array($r))
                            @php $emissor = $r['emissor'] ?? null; @endphp

                            {{-- Receita vinda da ENFERMAGEM: mostrar posologia --}}
                            @if ($emissor === 'enfermeiro')
                                <li>
                                    <strong>{{ $r['nome'] ?? 'Medicamento' }}</strong>
                                    @if(!empty($r['posologia'])) — <em>{{ $r['posologia'] }}</em>@endif
                                    <span style="font-size: 11px; color: #666;">(Origem: Enfermagem)</span>
                                </li>

                            {{-- Receita do MÉDICO (mantém teu layout atual) --}}
                            @else
                                <li>
                                    {{ $r['nome'] ?? 'Medicamento' }}
                                    @if(!empty($r['tipo'])) ({{ $r['tipo'] }}) @endif
                                    @if(!empty($r['mg'])) – {{ $r['mg'] }} @endif

                                    @php
                                        $detalhes  = $r['detalhes'] ?? [];
                                        $frequencia = collect($detalhes)->filter()->map(fn($v,$k)=>"$v $k")->implode(', ');
                                    @endphp

                                    @if ($frequencia)
                                        – {{ $frequencia }}
                                    @endif
                                    @if (!empty($r['intervaloHoras']))
                                        – a cada {{ $r['intervaloHoras'] }} horas
                                    @endif
                                    @if (!empty($r['instrucao']))
                                        – <em>{{ $r['instrucao'] }}</em>
                                    @endif
                                </li>
                            @endif
                        @else
                            <li>{{ $r }}</li>
                        @endif
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

    {{-- TRIAGEM (Enfermagem) para essa mesma data --}}
    @isset($triagensPorData[$data])
        @if(count($triagensPorData[$data]))
            <div class="section">
                <h3>Triagem (Enfermagem)</h3>
                @foreach ($triagensPorData[$data] as $t)
                    <div style="margin-bottom: 8px;">
                        <p>
                            <strong>Triagem feita por:</strong>
                            {{ $t['prof'] ?? '—' }}
                            @if(!empty($t['reg'])) — {{ $t['reg'] }} @endif
                        </p>
                        @if(!empty($t['pa']))
                            <p><strong>Pressão arterial:</strong> {{ $t['pa'] }}</p>
                        @endif

                        @php $an = $t['anamnese'] ?? []; @endphp
                        @if(!empty($an))
                            @if(!empty($an['queixa_principal']))
                                <p><strong>Queixa principal:</strong> {{ $an['queixa_principal'] }}</p>
                            @endif
                            @if(!empty($an['historia_doenca']))
                                <p><strong>História da doença:</strong> {{ $an['historia_doenca'] }}</p>
                            @endif
                            @if(!empty($an['historico_medico']))
                                <p><strong>Histórico médico:</strong> {{ $an['historico_medico'] }}</p>
                            @endif
                            @if(!empty($an['historico_familiar']))
                                <p><strong>Histórico familiar:</strong> {{ $an['historico_familiar'] }}</p>
                            @endif
                            @if(!empty($an['habitos_vida']))
                                <p><strong>Hábitos de vida:</strong> {{ $an['habitos_vida'] }}</p>
                            @endif
                            @if(!empty($an['revisao_sistemas']))
                                <p><strong>Revisão de sistemas:</strong> {{ $an['revisao_sistemas'] }}</p>
                            @endif
                            @if(!empty($an['observacoes']))
                                <p><strong>Observações:</strong> {{ $an['observacoes'] }}</p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endisset

@endforeach

</body>
</html>
