<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ROLE;
class SuperAdmin
{

    public function handle(Request $request, Closure $next)
    {

        if (!auth()->check()){
            session(['autherror' => "Session expires."]);
            return redirect()->route("login");
        }

        // if (auth()->user()->AllowSuper != 1){
        //     session(['autherror' => "You are not permitted to view the page. Please re login."]);
        //     return redirect()->route("login");
        // }

        $ok = false;

        if (auth()->user()->AllowSuper == 1){
          $ok = true;
        }

        if (auth()->user()->AccountLevel == 'Administrator'){
          $ok = true;
        }

        if (ROLE::isRegistrar()){
          $ok = true;
        }

        if (!$ok){
          session(['autherror' => "You are not permitted to view the page. Please re login."]);
          return redirect()->route("login");
        }

        return $next($request);
    }
}
