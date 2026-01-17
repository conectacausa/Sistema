<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'cpf' => ['required','string'],
            'password' => ['required','string'],
            'remember_cpf' => ['nullable'],
        ]);

        $cpf = preg_replace('/\D+/', '', $data['cpf']); // só números

        // Sua tabela de usuarios usa "senha" (não "password").
        // A autenticação padrão do Laravel usa "password".
        // Então: ou você adapta o Model para usar senha como password,
        // ou faz login manual. Vamos fazer manual por enquanto:

        $user = \App\Models\Usuario::where('cpf', $cpf)->first();

        if (!$user || !password_verify($data['password'], $user->senha)) {
            return back()
                ->withInput(['cpf' => $data['cpf']])
                ->with('toastr', ['type' => 'error', 'message' => 'CPF ou Senha estão inválidos.']);
        }

        if ($user->status !== 'ativo') {
            return back()
                ->withInput(['cpf' => $data['cpf']])
                ->with('toastr', ['type' => 'warning', 'message' => 'Usuário inativo.']);
        }

        // valida tenant (empresa do subdomínio)
        $empresa = config('tenant.empresa');
        if ($empresa && $user->empresa_id !== $empresa->id) {
            return back()
                ->withInput(['cpf' => $data['cpf']])
                ->with('toastr', ['type' => 'error', 'message' => 'Usuário não pertence a esta empresa.']);
        }

        Auth::login($user, false);

        $cookie = null;
        if ($request->boolean('remember_cpf')) {
            $cookie = cookie('remember_cpf', $data['cpf'], 60 * 24 * 30); // 30 dias
        } else {
            $cookie = Cookie::create('remember_cpf')->withValue('')->withExpires(0);
        }

        return redirect()
            ->route('dashboard')
            ->withCookie($cookie)
            ->with('toastr', ['type' => 'success', 'message' => 'Login realizado com sucesso!']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('toastr', ['type' => 'info', 'message' => 'Sessão encerrada.']);
    }
}
