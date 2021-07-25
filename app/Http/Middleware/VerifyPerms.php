<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Server;

class VerifyPerms
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
		if (! $request->user()) {
            return response('', 401);
		}
        if (! $request->user()->hasPermissionTo($permission)) {
            return response('', 403);
        }
		
        return $next($request);
    }
}
