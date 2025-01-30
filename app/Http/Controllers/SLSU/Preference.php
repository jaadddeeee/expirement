<?php

namespace App\Http\Controllers\SLSU;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Preference extends Controller
{

    public function GetDefaults($tbl = array()){
        if (empty($tbl)){
            $defaults = DB::connection(strtolower(session('campus')))->table("defaultvalue")->get();
        }else{
            $defaults = DB::connection(strtolower(session('campus')))->table("defaultvalue")->whereIn("DefaultName", $tbl)->get();
        }
        return $defaults;
    }

    public function GetDefaultValue($lists, $needle){
        $out = "";
        foreach ($lists as $list){
            if ($list->DefaultName == $needle){
                $out = $list->DefaultValue;
            }
        }
        return $out;
    }
}

?>
