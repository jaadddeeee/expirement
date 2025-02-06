<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

use App\Models\Scholarship;
use GENERAL;

class ScholarshipController extends Controller
{

    public function index(){

      $pageTitle = "Manage Scholarships";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

      $scholars = Scholarship::orderby("typ")
            ->orderby("scholar_name")->get();

      return view('slsu.scholarship.scholarships',compact('scholars'),[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
      ]);
    }
    
    public function save(Request $request){
      $ScholarshipName = $request->ScholarshipName;
      $Amount = $request->Amount;
      $ScholarshipType = $request->ScholarshipType;

      if (empty($ScholarshipName))
        return response()->json(['Error' => 1, "Message" => "Empty Scholarship Name"]);

      if (empty($Amount))
        return response()->json(['Error' => 1, "Message" => "Empty Amount"]);

      if (empty($ScholarshipType))
        return response()->json(['Error' => 1, "Message" => "Please select type"]);

      if ($ScholarshipType == 1){
          if ($Amount < 1 or $Amount > 100){
            return response()->json(['Error' => 1, "Message" => "Invalid amount. Enter number from 1 to 100 only."]);
          }
      }

      //check if exist
      $ex = Scholarship::where("scholar_name",trim($ScholarshipName))
        ->where('typ', $ScholarshipType)
        ->where('amount',str_replace(",","",$Amount))
        ->first();

      if (!empty($ex))
        return response()->json(['Error' => 1, "Message" => "Scholarship already exist."]);

      $id = date('Ymd').time();

      $data = [
        'id' => $id,
        'scholar_name' => trim($ScholarshipName),
        'amount' => str_replace(",","",$Amount),
        'typ' => $ScholarshipType
      ];

      $ins = Scholarship::insert($data);
      if ($ins)
        return response()->json(['Error' => 0, "Message" => trim($ScholarshipName). " successfully inserted." ]);

      return response()->json(['Error' => 1, "Message" => "Unable to insert scholarship"]);

    }
}
