<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Crypt;

class StudentIdController extends Controller
{

    public function index()
    {
        $student = Student::all();

        $pageTitle = "School ID Card";
        // $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        return view('slsu.studentid.index', [
            'pageTitle' => $pageTitle,
            // 'headerAction' => $headerAction
            'student' => $student
        ]);
    }


    public function getaddnewid()
    {
        $pageTitle = "Add New ID";
        return view('slsu.studentid.addnewid', [
            'pageTitle' => $pageTitle
        ]);
    }

    public function getprocessid($id)
    {
        $encrypted_id = Crypt::decryptString($id);

        $student = Student::where('StudentNo', $encrypted_id)->firstOrFail();

        $pageTitle = "Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';
        return view('slsu.studentid.processid', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' => $student
        ]);
    }
}
