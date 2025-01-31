<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Varsity;

class VarsityController extends Controller
{
    public function index(){
        $events = Varsity::all();

        $pageTitle = "Manage Event";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        return view('slsu.varsity.event',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'events' => $events
            ]);
    }
}
