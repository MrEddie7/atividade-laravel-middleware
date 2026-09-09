<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $permissao = false;

        if (!$permissao) {
            return response()->view('acesso-negado', [], 403);
        }

        return $next($request);
    }
}
