<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;

class OSAS
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isOSAS()){
          return redirect('/');
        }

        return $next($request);
    }
}
