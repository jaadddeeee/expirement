<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ROLE;

class EmployeeID
{
    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isEmployeeID()) {
            return redirect('/');
        }

        return $next($request);
    }
}
