<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class Registrar
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isRegistrar()){
          return redirect('/');
        }

        return $next($request);
    }
}
