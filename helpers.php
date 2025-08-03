<?php

use Carbon\Carbon;

if (!function_exists('getProximaDataExame')) {
    function getProximaDataExame($diaSemana)
    {
        $diasSemana = [
            'domingo'    => 'sunday',
            'segunda'    => 'monday',
            'terca'      => 'tuesday',
            'terça'      => 'tuesday',
            'quarta'     => 'wednesday',
            'quinta'     => 'thursday',
            'sexta'      => 'friday',
            'sabado'     => 'saturday',
            'sábado'     => 'saturday',
        ];

        $diaSemana = strtolower($diaSemana);

        if (!array_key_exists($diaSemana, $diasSemana)) {
            throw new InvalidArgumentException("Dia da semana inválido: $diaSemana");
        }

        $hoje = Carbon::today();
        $alvo = Carbon::parse("next " . $diasSemana[$diaSemana]);

        // Se hoje for o mesmo dia da semana, retorna hoje
        if ($hoje->englishDayOfWeek === ucfirst($diasSemana[$diaSemana])) {
            return $hoje->toDateString();
        }

        return $alvo->toDateString();
    }
}
