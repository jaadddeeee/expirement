<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{

    public function handle(Request $request, Closure $next)
    {

        if (!strtolower(auth()->user()->AccountLevel) == "administrator"){
            return redirect()->route("login");
        }

        return $next($request);
    }
}
