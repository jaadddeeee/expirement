<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\Student;


class TypeAheadController extends Controller
{
    protected $deptID = 0;

    public function emps(Request $request): JsonResponse
    {

        $str = $request->str;
        $data = DB::connection(strtolower(session('campus')))
                ->table("employees")
                ->select(DB::connection(strtolower(session('campus')))->raw('concat(LastName, ", ",FirstName) as Name'))
                ->where(function($query) use ($str){
                    $query->whereOr("LastName","LIKE","%{$str}%")
                      ->whereOr("FirstName","LIKE","%{$str}%");
                })
                ->pluck('Name');

        return response()->json($data);
    }

    public function search(Request $request): JsonResponse
    {

        try{
          $str = $request->str;
          $data = Student::select(DB::connection(strtolower(session('campus')))->raw('concat(StudentNo, " - ",LastName, ", ",FirstName) as Name'))
                  ->where(function($query) use ($str){
                      $query->whereOr("LastName","LIKE","%{$str}%")
                        ->whereOr("FirstName","LIKE","%{$str}%")
                        ->whereOr("StudentNo","LIKE","%{$str}%");
                  })
                  ->pluck('Name');
          return response()->json($data);
        }catch(Exception $e){
          return response()->json($e->getMessage());
        }

    }

    public function fees(Request $request): JsonResponse
    {

        $str = $request->str;
        $data = DB::connection(strtolower(session('campus')))
            ->table('chartaccount')
            ->select(DB::connection(strtolower(session('campus')))->raw('concat(AccountID, " - ",Description) as Name'))
                ->where(function($query) use ($str){
                    $query->whereOr("AccountID","LIKE","%{$str}%")
                      ->whereOr("Description","LIKE","%{$str}%");
                })
                ->pluck('Name');

        return response()->json($data);
    }

    public function sch(Request $request): JsonResponse
    {

        $str = $request->str;
        $data = DB::connection(strtolower(session('campus')))
            ->table('scholar')
            ->select(DB::connection(strtolower(session('campus')))->raw('concat(scholar_name, " - ",amount) as Name'))
                ->where(function($query) use ($str){
                    $query->whereOr("scholar_name","LIKE","%{$str}%");
                })
                ->pluck('Name');

        return response()->json($data);
    }


}
