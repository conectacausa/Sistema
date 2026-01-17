<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $empresa = config('tenant.empresa');
        $user = Auth::user();

        if ($empresa && $user->empresa_id !== $empresa->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('toastr', ['type' => 'error', 'message' => 'Acesso inválido para este subdomínio. Faça login novamente.']);
        }

        return $next($request);
    }
}
