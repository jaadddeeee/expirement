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
use Illuminate\Support\Facades\Log;


use App\Models\ScholarshipNew;
use App\Models\Student;
use App\Models\Scholar;

use GENERAL;

class ScholarshipNController extends Controller
{
    // SCHOLARSHIP

    // index scholarhip
    public function index(Request $request)
    {
        $pageTitle = "Scholarships";
        $headerAction = '<a href="' . route('home') . '" class="btn btn-sm btn-primary" role="button">Back</a>';

        $campus = strtolower(session('campus'));
    
        $query = DB::connection($campus)
            ->table('sch_scholarships')
            ->whereNull('deleted_at');
    
        // filter scholarships by type
        if ($request->has('scholarshipType') && !empty($request->scholarshipType)) {
            $query->where('sch_type', $request->scholarshipType);
        }
    
        if ($request->has('externalType') && !empty($request->externalType)) {
            $query->where('ext_type', $request->externalType);
        }
    
        $scholarships = $query->paginate(10);
    
        return view('slsu.scholarshipnew.index', compact('pageTitle', 'headerAction', 'scholarships'));
    }

    // search scholarship
    public function search(Request $request)
    {
        $query = $request->input('query');
        $isSearch = $request->input('is_search', false);

        $scholarships = ScholarshipNew::where('sch_name', 'LIKE', "%{$query}%")
            ->whereNull('deleted_at')
            ->orderBy('sch_type')
            ->orderBy('sch_name')
            ->paginate(10);

        return response()->json([
            'html' => view('_partials.scholarships-table', compact('scholarships', 'isSearch'))->render()
        ]);
    }

    // save scholarship
    public function save(Request $request)
    {
        try {
            $ScholarshipName = trim($request->ScholarshipName);
            $ScholarshipType = $request->ScholarshipType;
            $ExternalSchType = $request->ExternalScholarshipType;

            if (empty($ScholarshipName)) {
                return response()->json(['Error' => 1, "Message" => "Empty Scholarship Name"]);
            }

            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, "Message" => "Please select scholarship type"]);
            }

            if ($ScholarshipType == 1) {
                $ExternalSchType = 'N/A';
            } elseif (empty($ExternalSchType)) {
                return response()->json(['Error' => 1, "Message" => "Please select external type"]);
            }

            // check for duplicate
            $existing = ScholarshipNew::where('sch_name', $ScholarshipName)->first();

            if ($existing) {
                return response()->json(['Error' => 1, "Message" => "Scholarship already exists."]);
            }

            $scholarship = new ScholarshipNew();
            $scholarship->sch_name = $ScholarshipName;
            $scholarship->sch_type = $ScholarshipType;
            $scholarship->ext_type = $ExternalSchType;
            $scholarship->save();

            $scholarships = ScholarshipNew::paginate(10);

            return response()->json([
                'Error' => 0,
                "Message" => "$ScholarshipName successfully inserted.",
                'html' => view('_partials.scholarships-table', compact('scholarships'))->render(),
                'pagination' => $scholarships->links()->render()
            ]);

        } catch (\Exception $e) {
            return response()->json(['Error' => 1, "Message" => "An error occurred: " . $e->getMessage()], 400);
        } 
    }

    // fetch scholarship
    public function fetchScholarships()
    {
        $scholarships = ScholarshipNew::whereNull('deleted_at')
            ->paginate(10);

        return view('_partials.scholarships-table', compact('scholarships'))->render();
    }

    // get the scholarship id, name, and type
    public function edit(Request $request)
    {
        try {
            $scholarshipId = Crypt::decryptString($request->id);
            $scholarship = ScholarshipNew::findOrFail($scholarshipId);

            return response()->json([
                'Error' => 0,
                'Scholarship' => [
                    'id' => $scholarship->id,
                    'name' => $scholarship->sch_name,
                    'type' => $scholarship->sch_type,
                    'externalType' => $scholarship->ext_type,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'Scholarship not found or an error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

    // update scholarship
    public function update(Request $request)
    {
        try {
            $scholarshipId = Crypt::decryptString($request->updateScholarshipID);
            $scholarship = ScholarshipNew::findOrFail($scholarshipId);

            $ScholarshipName = trim($request->ScholarshipName);
            $ScholarshipType = $request->ScholarshipType;
            $ExternalSchType = $request->ExternalScholarshipType;

            if (empty($ScholarshipName)) {
                return response()->json(['Error' => 1, "Message" => "Empty Scholarship Name"]);
            }

            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, "Message" => "Please select scholarship type"]);
            }

            if ($ScholarshipType == 1) {
                $ExternalSchType = 'N/A';
            } elseif (empty($ExternalSchType)) {
                return response()->json(['Error' => 1, "Message" => "Please select external type"]);
            }

            $existing = ScholarshipNew::where('sch_name', $ScholarshipName)
                ->where('sch_type', $ScholarshipType)
                ->where('ext_type', $ExternalSchType)
                ->first();

            if ($existing) {
                return response()->json(['Error' => 1, "Message" => "Scholarship already exists."]);
            }

            $scholarship->update([
                'sch_name' => $ScholarshipName,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType
            ]);

            return response()->json(['Error' => 0, "Message" => "$ScholarshipName updated successfully."]);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, "Message" => "An error occurred: " . $e->getMessage()], 400);
        }
    }

    // delete scholarship using softdeletes
    public function delete(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id);

            $scholarship = ScholarshipNew::findOrFail($id);
            $scholarship->delete();

            return response()->json(['Error' => 0, 'Message' => 'Scholarship deleted successfully.']);
        } catch (DecryptException $e) {
            return response()->json(['Error' => 1, 'Message' => 'Invalid ID.'], 400);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'Error deleting scholarship: ' . $e->getMessage()], 400);
        }
    }




    // SCHOLAR

    // add Scholar View
    public function addScholarView(Request $request)
    {
        $pageTitle = "Add Scholar";
        $headerAction = '<a href="' . url()->previous() . '" class="btn btn-sm btn-primary" role="button">Back</a>';
    
        $encryptedScholarshipId = $request->query('id');
        $scholarshipName = $request->query('name');
        $search = $request->query('query', '');
        $schoolYear = $request->query('school_year', '');
        $semester = $request->query('semester', '');
    
        try {
            $scholarshipId = Crypt::decryptString($encryptedScholarshipId);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid scholarship ID');
        }
    
        $campus = strtolower(session('campus'));
    
        $scholars = DB::connection($campus)
            ->table('students')
            ->join('sch_scholars', 'students.StudentNo', '=', 'sch_scholars.student_no')
            ->whereNull('sch_scholars.deleted_at')
            ->where('sch_scholars.scholarship_id', '=', $scholarshipId)
            ->where(function ($query) use ($search, $schoolYear, $semester) {
                if ($search) {
                    $query->where('students.StudentNo', 'LIKE', "%{$search}%")
                        ->orWhere('students.FirstName', 'LIKE', "%{$search}%")
                        ->orWhere('students.MiddleName', 'LIKE', "%{$search}%")
                        ->orWhere('students.LastName', 'LIKE', "%{$search}%");
                }
                
                if ($schoolYear) {
                    $query->where('sch_scholars.SchoolYear', $schoolYear);
                }
                if ($semester) {
                    $query->where('sch_scholars.Semester', $semester);
                }
            })
            ->paginate(10, [
                'sch_scholars.id',
                'students.StudentNo',
                'students.FirstName',
                'students.MiddleName',
                'students.LastName',
                'sch_scholars.date_awarded',
                'sch_scholars.SchoolYear',
                'sch_scholars.Semester'
            ]);
    
        foreach ($scholars as $scholar) {
            $scholar->MiddleName = $scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : '';
        }
    
        return view('slsu.scholarshipnew.add-scholar-view', compact(
            'pageTitle', 
            'headerAction', 
            'scholarshipName', 
            'encryptedScholarshipId', 
            'scholars', 
            'scholarshipId',
            'search',
            'schoolYear',
            'semester'
        ));
    }

    // search scholar
    public function searchScholar(Request $request)
    {
        $search = $request->input('query');
        $encryptedScholarshipId = $request->input('scholarship_id');
        $isSearch = $request->input('is_search', false);
    
        try {
            $scholarshipId = Crypt::decryptString($encryptedScholarshipId);
        } catch (DecryptException $e) {
            return response()->json(['error' => 'Invalid scholarship ID'], 400);
        }
    
        $campus = strtolower(session('campus'));
    
        $scholars = DB::connection($campus)
            ->table('students')
            ->join('sch_scholars', 'students.StudentNo', '=', 'sch_scholars.student_no')
            ->whereNull('deleted_at')
            ->where('sch_scholars.scholarship_id', '=', $scholarshipId)
            ->where(function ($query) use ($search) {
                $query->where('students.StudentNo', 'LIKE', "%{$search}%")
                    ->orWhere('students.FirstName', 'LIKE', "%{$search}%")
                    ->orWhere('students.MiddleName', 'LIKE', "%{$search}%")
                    ->orWhere('students.LastName', 'LIKE', "%{$search}%");
            })
            ->paginate(10, [
                'sch_scholars.id',
                'students.StudentNo',
                'students.FirstName',
                'students.MiddleName',
                'students.LastName',
                'sch_scholars.date_awarded',
                'sch_scholars.SchoolYear',
                'sch_scholars.Semester'
            ]);
    
        foreach ($scholars as $scholar) {
            $scholar->MiddleName = $scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : '';
        }
    
        return response()->json([
            'html' => view('_partials.scholars-table', compact('scholars', 'isSearch'))->render()
        ]);
    }

    // search Student
    public function searchStudent(Request $request)
    {
        $search = $request->input('query');
        $encryptedScholarshipId = $request->input('scholarship_id');
        $schoolYear = $request->input('school_year');
        $semester = $request->input('semester');

        try {
            $scholarshipId = Crypt::decryptString($encryptedScholarshipId);
        } catch (DecryptException $e) {
            return response()->json(['error' => 'Invalid scholarship ID'], 400);
        }

        $campus = strtolower(session('campus'));

        $students = DB::connection($campus)
            ->table('students')
            ->leftJoin('sch_scholars', function ($join) use ($scholarshipId, $schoolYear, $semester) {
                $join->on('students.StudentNo', '=', 'sch_scholars.student_no')
                    ->where('sch_scholars.scholarship_id', '=', $scholarshipId)
                    ->where('sch_scholars.SchoolYear', '=', $schoolYear)
                    ->where('sch_scholars.Semester', '=', $semester);
            })
            ->where(function ($query) use ($search) {
                $query->where('students.StudentNo', 'LIKE', "%{$search}%")
                    ->orWhere('students.FirstName', 'LIKE', "%{$search}%")
                    ->orWhere('students.MiddleName', 'LIKE', "%{$search}%")
                    ->orWhere('students.LastName', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['students.StudentNo', 'students.FirstName', 'students.MiddleName', 'students.LastName', 'sch_scholars.scholarship_id as existingScholarshipId']);

        foreach ($students as $student) {
            $student->MiddleName = $student->MiddleName ? substr($student->MiddleName, 0, 1) . '.' : '';
            $student->alreadyExists = $student->existingScholarshipId ? true : false;
        }

        return response()->json($students);
    }

    // add Scholar
    public function addScholar(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['error' => 'Invalid campus database connection'], 400);
            }

            if (!$request->has('scholarship_id') || empty($request->scholarship_id)) {
                return response()->json(['error' => 'Scholarship ID is missing or empty'], 400);
            }

            try {
                $decryptedScholarshipId = Crypt::decryptString($request->scholarship_id);
            } catch (DecryptException $e) {
                return response()->json(['error' => 'Invalid or corrupted scholarship ID'], 400);
            }

            $request->merge(['scholarship_id' => $decryptedScholarshipId]);

            $request->validate([
                'scholarship_id' => 'required|exists:' . $campus . '.sch_scholarships,id',
                'students' => 'required|array',
                'students.*' => 'exists:' . $campus . '.students,StudentNo',
                'schoolYear' => 'required|string',
                'semester' => 'required|string',
            ]);

            $studentNos = $request->students;
            $schoolYear = $request->schoolYear;
            $semester = $request->semester;

            // Fetch already existing students for this scholarship
            $existingStudents = DB::connection($campus)
                ->table('sch_scholars')
                ->where('scholarship_id', $decryptedScholarshipId)
                ->where('SchoolYear', $schoolYear)
                ->where('Semester', $semester)
                ->pluck('student_no')
                ->toArray();

            // Filter out students that are already in the scholarship
            $newStudents = array_diff($studentNos, $existingStudents);

            if (empty($newStudents)) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Selected student/s already exist in this scholarship for the given school year and semester.'
                ]);
            }

            $insertData = array_map(function ($studentNo) use ($decryptedScholarshipId, $schoolYear, $semester) {
                return [
                    'scholarship_id' => $decryptedScholarshipId,
                    'student_no' => $studentNo,
                    'SchoolYear' => $schoolYear,
                    'Semester' => $semester
                ];
            }, $newStudents);

            DB::connection($campus)->table('sch_scholars')->insert($insertData);

            return response()->json([
                'Error' => 0,
                'Message' => count($newStudents) . ' student/s added successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while adding scholars.'], 400);
        }
    }

    // edit scholar
    public function editScholar(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection'], 400);
            }

            $scholarId = Crypt::decryptString($request->id);
            $scholar = DB::connection($campus)
                ->table('sch_scholars')
                ->join('students', 'sch_scholars.student_no', '=', 'students.StudentNo')
                ->where('sch_scholars.id', $scholarId)
                ->select('sch_scholars.*', 'students.FirstName', 'students.MiddleName', 'students.LastName', 'students.StudentYear')
                ->first();

            if (!$scholar) {
                return response()->json(['Error' => 1, 'Message' => 'Scholar not found'], 404);
            }

            $studentName = $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '. ' : '') . $scholar->LastName;
            $schoolYearLabel = \GENERAL::setSchoolYearLabel($scholar->SchoolYear, $scholar->Semester);
            $semesterLabel = \GENERAL::Semesters()[$scholar->Semester]['Long'];

            return response()->json([
                'Error' => 0,
                'Scholar' => [
                    'id' => $scholar->id,
                    'student_no' => $scholar->student_no,
                    'student_name' => $studentName,
                    'schoolYear' => $schoolYearLabel,
                    'semester' => $semesterLabel,
                    'date_awarded' => $scholar->date_awarded,
                    'bank_account' => $scholar->bank_account,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'Scholar not found or an error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

    public function updateScholar(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection'], 400);
            }

            $scholarId = Crypt::decryptString($request->updateScholarID);
            $scholar = DB::connection($campus)
                ->table('sch_scholars')
                ->where('id', $scholarId)
                ->first();

            if (!$scholar) {
                return response()->json(['Error' => 1, 'Message' => 'Scholar not found'], 404);
            }

            DB::connection($campus)
                ->table('sch_scholars')
                ->where('id', $scholarId)
                ->update([
                    'date_awarded' => $request->editDateAwarded,
                    'bank_account' => $request->editBankAccount,
                ]);

            return response()->json(['Error' => 0, 'Message' => 'Scholar updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'An error occurred while updating scholar.'], 400);
        }
    }

    public function deleteScholar(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection'], 400);
            }

            $scholarId = Crypt::decryptString($request->id);
            $scholar = DB::connection($campus)
                ->table('sch_scholars')
                ->where('id', $scholarId)
                ->first();

            if (!$scholar) {
                return response()->json(['Error' => 1, 'Message' => 'Scholar not found'], 404);
            }

            $scholar = Scholar::findOrFail($scholarId);
            $scholar->delete();

            return response()->json(['Error' => 0, 'Message' => 'Scholar deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'An error occurred while deleting scholar.'], 400);
        }
    }
}
