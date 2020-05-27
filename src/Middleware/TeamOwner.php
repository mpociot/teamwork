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
        if (!auth()->user()->isOwnerOfTeam(auth()->user()->currentTeam)) {
            return back();
        }

        return $next($request);
    }
}
