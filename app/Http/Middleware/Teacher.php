<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Session;
use ROLE;
class Teacher
{

    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isTeacher()){
          return redirect('/');
        }

        return $next($request);
    }
}
