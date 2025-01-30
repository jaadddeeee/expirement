<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class Clearance
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isClearance()){
          return redirect('/');
        }

        return $next($request);
    }
}
