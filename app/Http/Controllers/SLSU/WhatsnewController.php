<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;

class WhatsnewController extends Controller
{
    public function index(){
        return view('slsu.whatsnew.index');
    }

}
