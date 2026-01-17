<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class LoginController extends Controller
{
    /**
     * Exibe a tela de login
     */
    public function show(Request $request)
    {
        return view('auth.login');
    }

    /**
     * Processa o login
     */
    public function login(Request $request)
    {
        // validação básica
        $request->validate([
            'cpf' => ['required'],
            'password' => ['required'],
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        $usuario = Usuario::query()
            ->where('cpf', $cpf)
            ->where('status', 'ativo')
            ->first();

        // usuário não encontrado ou senha inválida
        if (
            !$usuario ||
            !Hash::check($request->password, $usuario->senha)
        ) {
            return back()
                ->withInput(['cpf' => $request->cpf])
                ->with('toastr_error', 'CPF ou senha inválidos.');
        }

        // garante que o usuário pertence à empresa do subdomínio
        if ($usuario->empresa_id !== session('tenant_empresa_id')) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect('/login')
                ->with('toastr_error', 'Acesso não autorizado para esta empresa.');
        }

        Auth::login($usuario, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
