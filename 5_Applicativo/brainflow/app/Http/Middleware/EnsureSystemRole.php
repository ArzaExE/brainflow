<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Non autenticato.');
        }

        if (!in_array($user->sys_role, $roles, true)) {
            abort(403, 'Permessi di sistema insufficienti.');
        }

        return $next($request);
    }
}
