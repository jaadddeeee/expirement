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

use App\Models\Scholar;
use App\Models\Student;
use App\Models\ScholarshipNew;


use GENERAL;

class ScholarController extends Controller
{
    public function index()
    {
        $pageTitle = "Scholars";
        $headerAction = '<a href="' . url()->previous() . '" class="btn btn-sm btn-primary" role="button">Back</a>';

        $scholars = Scholar::with(['scholarship', 'student'])
            ->orderBy('date_awarded', 'desc')
            ->paginate(10);

        return view('slsu.scholar.scholars', compact('pageTitle', 'headerAction', 'scholars'));
    }

    // search student
    public function searchStudent(Request $request)
    {
        $query = $request->input('query');

        $students = Student::where('StudentNo', 'like', "%$query%")
            ->orWhere('LastName', 'like', "%$query%")
            ->orWhere('FirstName', 'like', "%$query%")
            ->orWhere('Course', 'like', "%$query%")
            ->select('StudentNo', 'LastName', 'FirstName', 'Sex', 'Course')
            ->get();

        return response()->json([ 'html' => view('_partials.students-table', compact('students'))->render() ]);
    }

    // search scholars
    public function searchScholar(Request $request)
    {
        $searchTerm = $request->get('query');

        $scholars = Scholar::with(['scholarship', 'student'])
            ->whereHas('student', function ($query) use ($searchTerm) {
                $query->where('FirstName', 'like', "%$searchTerm%")
                    ->orWhere('LastName', 'like', "%$searchTerm%");
            })
            ->orWhereHas('scholarship', function ($query) use ($searchTerm) {
                $query->where('sch_name', 'like', "%$searchTerm%");
            })
            ->orderBy('date_awarded', 'desc')
            ->paginate(10);

        return response()->json([ 'html' => view('_partials.scholars-table', compact('scholars'))->render() ]);
    }

    
    public function add()
    {
        $pageTitle = "Add Scholar";
        $headerAction = '<a href="' . url()->previous() . '" class="btn btn-sm btn-primary" role="button">Back</a>';
    
        $students = Student::with(['course'])
            ->orderBy('LastName')
            ->paginate(10);

        $scholarships = ScholarshipNew::select('id', 'sch_name')->get();

       
        return view('slsu.scholar.add-scholar', compact('pageTitle', 'headerAction', 'students', 'scholarships'));
    }

    public function addScholar(Request $request){

        $request->validate([
            'studentNo' => 'required|exists:students,StudentNo',
            'selectScholarship' => 'required|exists:scholarship_new,id',
            'dateAwarded' => 'nullable|date',
            'schoolYear' => 'nullable|string|max:9',
            'semester' => 'nullable|string|max:10',
            'bankAccNo' => 'nullable|string|max:20',
        ]);

        try {
            DB::table('sch_scholars')->insert([
                'scholarship_id' => $request->selectScholarship,
                'student_no' => $request->studentNo,
                'date_awarded' => $request->dateAwarded,
                'SchoolYear' => $request->schoolYear,
                'Semester' => $request->semester,
                'bank_account' => $request->bankAccNo,
            ]);

            return response()->json(['success' => true, 'message' => 'Scholar added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
