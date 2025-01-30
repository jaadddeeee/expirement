<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cashier
{

    public function handle(Request $request, Closure $next)
    {

        if (!strtolower(auth()->user()->Role) == "cashier"){
            return redirect()->route("login");
        }

        return $next($request);
    }
}
