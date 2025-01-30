<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


class ScholarController extends Controller
{
    public function index(){
        return view('slsu.scholar.scholar');
    }
}
