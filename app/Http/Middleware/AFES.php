<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Department;
use ROLE;
class AFES
{

    public function handle(Request $request, Closure $next)
    {
        $ok = false;

        // if (strtolower(auth()->user()->AccountLevel) == "administrator"){
        //   $ok = true;
        // }

        if (auth()->user()->AllowSuper == 1){
          $ok = true;
        }

        if (ROLE::isVPAA()){
          $ok = true;
        }


        if (ROLE::isPresident()){
          $ok = true;
        }

        $depts = Department::where('DepartmentHead', auth()->user()->Emp_No)
              ->where("Active", 0)
              ->first();

        if (!empty($depts)){
          $ok = true;
        }

        if (!$ok){
            session(['autherror' => "You are not permitted to view the page. Please re login."]);
            return redirect()->route("login");
        }

        return $next($request);
    }
}
