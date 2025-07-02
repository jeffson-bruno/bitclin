<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;


class RecepcaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Recepcao/Dashboard');
    }
}

