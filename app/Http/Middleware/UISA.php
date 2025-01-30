<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class UISA
{

    public function handle(Request $request, Closure $next)
    {

        $proceed = false;

        if (ROLE::isUISA()){
          $proceed = true;
        }

        if (ROLE::isRegistrar()){
          $proceed = true;
        }

        if (!$proceed){
          return redirect('/');
        }
        return $next($request);
    }
}
