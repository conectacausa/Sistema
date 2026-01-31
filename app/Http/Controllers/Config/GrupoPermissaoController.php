<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GrupoPermissaoController extends Controller
{
    public function index(Request $request)
    {
        return view('config.grupos.index');
    }
}
