<?php

namespace App\Http\Middleware;

use Closure;

class IsBanned
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
        if(auth()->user()->isbaned=='1') {
            return redirect('/logout');
        }
        return $next($request);
    }
}
