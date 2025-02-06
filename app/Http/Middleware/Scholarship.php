<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;

class Scholarship
{

    public function handle(Request $request, Closure $next) : Response
    {

        if (!ROLE::isScholarship()){
          return redirect('/');
        }

        return $next($request);
    }
}
