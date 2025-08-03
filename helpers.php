<?php

use Carbon\Carbon;

if (!function_exists('getProximaDataExame')) {
    function getProximaDataExame(string $diaSemana): string
    {
        $diasSemana = [
            'domingo' => Carbon::SUNDAY,
            'segunda' => Carbon::MONDAY,
            'terça' => Carbon::TUESDAY,
            'terca' => Carbon::TUESDAY,
            'quarta' => Carbon::WEDNESDAY,
            'quinta' => Carbon::THURSDAY,
            'sexta' => Carbon::FRIDAY,
            'sábado' => Carbon::SATURDAY,
            'sabado' => Carbon::SATURDAY,
        ];

        $diaSemana = strtolower(trim($diaSemana));

        if (!array_key_exists($diaSemana, $diasSemana)) {
            throw new InvalidArgumentException("Dia da semana inválido: $diaSemana");
        }

        $hoje = Carbon::today();
        $alvo = Carbon::parse("next " . $diaSemana);

        // Se hoje for o mesmo dia da semana, retorna hoje
        if ($hoje->dayOfWeek === $diasSemana[$diaSemana]) {
            return $hoje->toDateString();
        }

        return $alvo->toDateString();
    }
}
