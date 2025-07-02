<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AgendaController extends Controller
{
    public function create()
    {
        return Inertia::render('Recepcao/InserirAgenda');
    }
}
