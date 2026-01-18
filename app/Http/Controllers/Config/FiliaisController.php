<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Filial;

class FiliaisController extends Controller
{
    public function index()
    {
        // Tela: config/filiais (screen_id = 5)
        return view('config.filiais.index', [
            'screenId' => 5,
        ]);
    }

    public function create()
    {
        // Página de criar (você disse que será criada na sequência)
        return view('config.filiais.create', [
            'screenId' => 5,
        ]);
    }

    public function edit(Filial $filial)
    {
        // Página de editar (você disse que será criada na sequência)
        return view('config.filiais.edit', [
            'screenId' => 5,
            'filial' => $filial,
        ]);
    }
}
