<?php

namespace Mpociot\Teamwork\Middleware;

use Closure;

class TeamOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->id) {
            abort(404);
        }

        $team = Team::findOrFail($request->id);

        if (!auth()->user()->isOwnerOfTeam($team)) {
            return abort(403);
        }

        return $next($request);
    }
}
