<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Department;

class AccountRole
{

    public static function hasRole($ro, $id, $campus)
    {
        // dd($role);
        $out = [];
        if (Auth::check()) {
            $roles = DB::connection(strtolower($campus))
                ->table("accountrole")
                ->where("EmpID", $id)
                ->get();
            foreach ($roles as $role) {
                if (strtolower($role->Role) == strtolower($ro))
                    return $role;
            }
        }
        return $out;
    }

    public static function isDepartmentHead()
    {
        $out = false;
        $depts = Department::where('DepartmentHead', auth()->user()->Emp_No)
            ->where("Active", 0)
            ->first();

        if (!empty($depts)) {
            $out = true;
        }

        return $out;
    }

    public static function isTES()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "tes")
                    return true;
            }
        }
        return false;
    }

    public static function isOSAS()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "osas")
                    return true;
            }
        }
        return false;
    }

    public static function isCashier()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "cashier")
                    return true;
            }
        }
        return false;
    }

    public static function isTeacher()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "teacher")
                    return true;
            }
        }
        return false;
    }

    public static function isClearance()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "clearance")
                    return true;
            }
        }
        return false;
    }

    public static function isNSTP()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "nstp")
                    return true;
            }
        }
        return false;
    }


    public static function isRegistrar()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "registrar")
                    return true;
            }
        }
        return false;
    }

    public static function isDepartment()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "department")
                    return true;
            }
        }
        return false;
    }

    public static function isUISA()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "uisa")
                    return true;
            }
        }
        return false;
    }

    public static function isScholarship()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "scholarship")
                    return true;
            }
        }
        return false;
    }

    public static function isPresident()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "president")
                    return true;
            }
        }
        return false;
    }

    public static function isVPAA()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "vpaa")
                    return true;
            }
        }
        return false;
    }



    public static function checkRolePermission($role, $permission)
    {
        try {
            if ($role->hasPermissionTo($permission)) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function demoUserPermission()
    {
        if (Auth::user()->hasRole('demo_admin')) {
            return true;
        } else {
            return false;
        }
    }

    public static function isStudentID()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "stuid")
                    return true;
            }
        }
        return false;
    }

    public static function isEmployeeID()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "emid")
                    return true;
            }
        }
        return false;
    }

    public static function isScholar()
    {
        if (Auth::check()) {
            foreach (Auth::user()->role as $role) {
                if (strtolower($role->Role) == "scholar")
                    return true;
            }
        }
        return false;
    }
}
