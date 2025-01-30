<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Exception;
use GENERAL;

class SearchController extends Controller
{
    //clearance functions
    public function or(Request $request)
    {
      try{

        $orno = $request->searchorno;
        if (empty($orno))
          throw new Exception("Invalid ORNo");

        $ex = DB::connection(strtolower(session('campus')))
          ->table('paid_assess')
          ->where('ORNo', $orno)
          ->first();
        if (empty($ex))
          throw new Exception("ORNo ".$orno." not found.");

        return response()->json($ex);

      }catch(Exception $e){
        return response()->json(['errors' => GENERAL::Error($e->getMessage())], 400);
      }
    }

}
