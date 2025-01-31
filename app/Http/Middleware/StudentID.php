<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ROLE;

class StudentID
{
    public function handle(Request $request, Closure $next)
    {

        if (!ROLE::isStudentID()) {
            return redirect('/');
        }

        return $next($request);
    }
}
