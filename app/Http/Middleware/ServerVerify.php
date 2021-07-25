<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Server;
class ServerVerify
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
        $token = $request->input('srv_token');
        if ($token) {
            $srv = Server::where('api_token', $token)->first();
            if ($srv) {
                $request->offsetSet('srv', $srv);
            }
        }
        return $next($request);
    }
}
