<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ROLE;

class Enrol
{
    public function handle(Request $request, Closure $next)
    {

        if (auth()->user()->AllowSuper == 1){
          return $next($request);
        }

        if (ROLE::isDepartment()){
            return $next($request);
        }

        if (ROLE::isRegistrar()){
            return $next($request);
        }

        if (ROLE::isVPAA()){
              return $next($request);
        }

        if (ROLE::isPresident()){
            return $next($request);
        }

        return redirect()->route("login");
    }
}
