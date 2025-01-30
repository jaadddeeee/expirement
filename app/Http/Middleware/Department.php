<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class Department
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isDepartment()){
          return redirect('/');
        }

        return $next($request);
    }
}
