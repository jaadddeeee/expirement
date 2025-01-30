<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class NSTP
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isNSTP()){
          return redirect('/');
        }

        return $next($request);
    }
}
