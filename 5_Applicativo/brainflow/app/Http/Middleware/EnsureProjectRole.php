<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectRole
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
            abort(401);
        }

        // Admin di sistema vede tutto, senza essere nella pivot
        if ($user->sys_role === 'admin') {
            $request->attributes->set('project_role', 'admin');
            return $next($request);
        }

        $projectParameter = $request->route('project');

        // Se il parametro è già un'istanza del modello Project
        if ($projectParameter instanceof Project) {
            $project = $projectParameter;
        } else {
            // Se è solo un ID numerico o una stringa, cercalo nel database
            $project = Project::find($projectParameter);
        }

        if (!$project) {
            abort(404);
        }

        $membership = $project->members()
            ->where('users.id', $user->id)
            ->first();
        // parametro strict è l'equivalente del controllo ===. Viene controllato anche il tipo.
        if (!$membership || !in_array($membership->pivot->role, $roles, true)) {
            abort(403);
        }

        $request->attributes->set('project_role', $membership->pivot->role);

        return $next($request);
    }
}
