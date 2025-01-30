<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class TES
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isTES()){
          return redirect('/');
        }

        return $next($request);
    }
}
