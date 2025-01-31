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


class ScholarController extends Controller
{
    public function index(){

      $pageTitle = "Scholarships";
      $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

    //   $scholars = Scholarship::orderby("typ")
    //         ->orderby("scholar_name")->get();

      return view('slsu.scholar.index',[
      'pageTitle' => $pageTitle,
      'headerAction' => $headerAction
      ]);
        return view('slsu.scholar.index');
    }
}
