<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VarsityController extends Controller
{
    public function index(){
        return view('slsu.varsity.event');
    }
}
