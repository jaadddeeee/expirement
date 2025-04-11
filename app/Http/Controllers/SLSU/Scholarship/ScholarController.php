<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Models\Scholarship\ScholarDetail;
use App\Models\Scholarship\ScholarEnrollment;

class ScholarController extends Controller
{

    // scholars index
    public function index(Request $request)
    {
        $pageTitle = "Scholars";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';

        // get from url paramaters
        $id = $request->query('id');
        $scholarshipName = $request->query('scholarshipName');

        // decrypt scholarship id
        try {
            $scholarshipId = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Invalid scholarship ID'], 400);
            }
            return redirect()->back()->with('error', 'Invalid scholarship ID');
        }

        // get campus
        $campus = strtolower(session('campus'));

        // query scholars
        $query = DB::connection($campus)
            ->table('students')
            ->join('sch_scholar_details', 'students.StudentNo', '=', 'sch_scholar_details.student_no')
            ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
            ->whereNull('sch_scholar_details.deleted_at')
            ->where('sch_scholar_details.scholarship_id', $scholarshipId)
            ->orderByDesc('sch_scholar_enrollments.school_year')
            ->orderByDesc('sch_scholar_enrollments.semester');

        // search a scholar
        if ($request->filled('searchScholar')) {
            $searchQuery = $request->input('searchScholar');
            $query->where(function ($q) use ($searchQuery) {
                $q->where('students.StudentNo', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('students.FirstName', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('students.MiddleName', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('students.LastName', 'LIKE', "%{$searchQuery}%");
            });
        }

        // get filter parameters
        $schoolYear = $request->filterSchoolYear;
        $semester = $request->filterSemester;

        // filter scholars 
        if ($request->filled('filterSchoolYear')) {
            $query->where('sch_scholar_enrollments.school_year', $schoolYear);
        }

        if ($request->filled('filterSemester')) {
            $query->where('sch_scholar_enrollments.semester', $semester);
        }

        $entriesPerPage = $request->input('entriesPerPage', 10);

        $scholars = collect();

        if ($request->filled('filterSchoolYear') && $request->filled('filterSemester')) {
            $scholars = $query->paginate(
                $entriesPerPage,
                [
                    'sch_scholar_details.id',
                    'students.StudentNo',
                    'students.FirstName',
                    'students.MiddleName',
                    'students.LastName',
                    'students.Course',
                    'students.StudentYear',
                    'sch_scholar_details.date_awarded',
                    'sch_scholar_enrollments.school_year as SchoolYear',
                    'sch_scholar_enrollments.semester as Semester',
                    'sch_scholar_enrollments.id as enrollment_id',
                    'sch_scholar_details.contact_no'
                ]
            );

            // format middle name
            foreach ($scholars as $scholar) {
                $scholar->MiddleName = $scholar->MiddleName ? Str::limit($scholar->MiddleName, 1, '.') : '';
            }
        }

        // return json scholars table
        if ($request->ajax()) {
            return response()->json([
                'scholarsTable' => view('slsu.scholarships._partials._scholars-table', compact('scholars'))->render()
            ]);
        }

        // return scholars view
        return view('slsu.scholarships.scholars', compact(
            'pageTitle',
            'headerAction',
            'scholarshipName',
            'id',
            'scholars',
            'schoolYear',
            'semester'
        ));
    }

    // search student
    public function searchStudent(Request $request)
    {
        $search = $request->searchStudent;
        $id = $request->id;
        $schoolYear = $request->addSchoolYear;
        $semester = $request->addSemester;

        $scholarshipId = Crypt::decryptString($id);

        $campus = strtolower(session('campus'));

        $students = DB::connection($campus)
            ->table('students')
            ->leftJoin('sch_scholar_details', function ($join) use ($scholarshipId) {
                $join->on('students.StudentNo', '=', 'sch_scholar_details.student_no')
                    ->where('sch_scholar_details.scholarship_id', '=', $scholarshipId);
            })
            ->leftJoin('sch_scholar_enrollments', function ($join) use ($schoolYear, $semester) {
                $join->on('sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
                    ->where('sch_scholar_enrollments.school_year', '=', $schoolYear)
                    ->where('sch_scholar_enrollments.semester', '=', $semester);
            })
            ->where(function ($query) use ($search) {
                $query->where('students.StudentNo', 'LIKE', "%{$search}%")
                    ->orWhere('students.FirstName', 'LIKE', "%{$search}%")
                    ->orWhere('students.MiddleName', 'LIKE', "%{$search}%")
                    ->orWhere('students.LastName', 'LIKE', "%{$search}%");
            })
            ->select([
                'students.StudentNo',
                'students.FirstName',
                'students.MiddleName',
                'students.LastName',
                'sch_scholar_enrollments.id as enrollment_id'
            ])
            ->get();

        foreach ($students as $student) {
            $student->MiddleName = $student->MiddleName ? substr($student->MiddleName, 0, 1) . '.' : '';
            $student->alreadyExists = $student->enrollment_id ? true : false;
        }

        return response()->json($students);
    }

    // add scholar
    public function store(Request $request)
    {
        try {
            try {
                $id = $request->scholarship_id;
                $schId = Crypt::decryptString($id);
            } catch (DecryptException $e) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid or corrupted scholarship ID']);
            }

            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection']);
            }

            if (!$request->has('scholarship_id') || empty($id)) {
                return response()->json(['Error' => 1, 'Message' => 'Scholarship ID is missing or empty']);
            }

            if (empty($request->schoolYear)) {
                return response()->json(['Error' => 1, 'Message' => 'Please select a school year.']);
            }

            if (empty($request->semester)) {
                return response()->json(['Error' => 1, 'Message' => 'Please select a semester.']);
            }

            if (empty($request->students) || !is_array($request->students)) {
                return response()->json(['Error' => 1, 'Message' => 'Please select at least one student.']);
            }

            $request->merge(['scholarship_id' => $schId]);

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

            // Fetch already existing students for this scholarship in the target school year and semester
            $existingStudents = DB::connection($campus)
                ->table('sch_scholar_details')
                ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
                ->where('sch_scholar_details.scholarship_id', $schId)
                ->where('sch_scholar_enrollments.school_year', $schoolYear)
                ->where('sch_scholar_enrollments.semester', $semester)
                ->pluck('sch_scholar_details.student_no')
                ->toArray();

            // Filter out students that are already in the scholarship
            $newStudents = array_diff($studentNos, $existingStudents);

            if (empty($newStudents)) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Selected student/s already exist.'
                ]);
            }

            foreach ($newStudents as $studentNo) {
                // check if the scholar already exists in the sch_scholar_details table
                $existingScholarDetail = DB::connection($campus)
                    ->table('sch_scholar_details')
                    ->where('student_no', $studentNo)
                    ->where('scholarship_id', $schId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingScholarDetail) {
                    $scholarDetailId = $existingScholarDetail->id;
                } else {
                    $scholarDetailId = DB::connection($campus)->table('sch_scholar_details')->insertGetId([
                        'scholarship_id' => $schId,
                        'student_no' => $studentNo,
                        'date_awarded' => now(), // assume rani nga karon ang date
                        'bank_account' => null, // assume nga null sa ang bank account
                        'contact_no' => null, // assume rani nga contact no, maybe mo kuha ra sa students table
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                DB::connection($campus)->table('sch_scholar_enrollments')->insert([
                    'scholar_id' => $scholarDetailId,
                    'school_year' => $schoolYear,
                    'semester' => $semester,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'Error' => 0,
                'Message' => count($newStudents) . ' student/s added successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'An error occurred while adding scholars.'], 400);
        }
    }

    // edit scholar
    public function edit(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection']);
            }

            $scholarId = Crypt::decryptString($request->id);

            $scholar = DB::connection($campus)
                ->table('sch_scholar_details')
                ->join('students', 'sch_scholar_details.student_no', '=', 'students.StudentNo')
                ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
                ->where('sch_scholar_details.id', $scholarId)
                ->select('sch_scholar_details.*', 'students.FirstName', 'students.MiddleName', 'students.LastName')
                ->first();

            if (!$scholar) {
                return response()->json(['Error' => 1, 'Message' => 'Scholar not found']);
            }

            $studentName = $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '. ' : '') . $scholar->LastName;
            // $schoolYearLabel = \GENERAL::setSchoolYearLabel($scholar->school_year, $scholar->semester);
            // $semesterLabel = \GENERAL::Semesters()[$scholar->semester]['Long'];

            return response()->json([
                'Error' => 0,
                'Scholar' => [
                    'id' => Crypt::encryptString($scholar->id),
                    'student_no' => $scholar->student_no,
                    'student_name' => $studentName,
                    'date_awarded' => $scholar->date_awarded,
                    'bank_account' => $scholar->bank_account,
                    'contact_no' => $scholar->contact_no,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'Scholar not found or an error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

    // update scholar
    public function update(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection']);
            }

            $scholarId = Crypt::decryptString($request->id);

            $scholar = DB::connection($campus)
                ->table('sch_scholar_details')
                ->where('id', $scholarId)
                ->first();

            if (!$scholar) {
                return response()->json(['Error' => 1, 'Message' => 'Scholar not found']);
            }

            DB::connection($campus)
                ->table('sch_scholar_details')
                ->where('id', $scholarId)
                ->update([
                    'date_awarded' => $request->editDateAwarded,
                    'bank_account' => $request->editBankAccount,
                    'contact_no' => $request->editContactNo,
                    'updated_at' => now()
                ]);

            return response()->json(['Error' => 0, 'Message' => 'Scholar updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'message' => "An error occurred: " . $e->getMessage()], 400);
        }
    }

    // delete scholar
    public function destroy(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            $scholarId = Crypt::decryptString($request->id);

            $scholar = ScholarDetail::on($campus)->find($scholarId);

            $enrollments = ScholarEnrollment::on($campus)->where('scholar_id', $scholarId)->get();
            foreach ($enrollments as $enrollment) {
                $enrollment->delete();
            }

            $scholar->delete();

            return response()->json(['Error' => 0, 'Message' => 'Scholar deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'An error occurred while deleting scholar.'], 400);
        }
    }

    // copy selected scholars
    public function copyScholars(Request $request)
    {
        $scholarshipId = Crypt::decryptString($request->scholarship_id);
        $campus = strtolower(session('campus'));

        // Validate required fields
        $request->validate([
            'schoolYearFrom' => 'required',
            'semesterFrom' => 'required',
            'schoolYearTo' => 'required',
            'semesterTo' => 'required',
            'selected_scholars' => 'required|array|min:1',
        ]);

        $scholars = DB::connection($campus)
            ->table('sch_scholar_details')
            ->join('students', 'sch_scholar_details.student_no', '=', 'students.StudentNo')
            ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
            ->where('sch_scholar_details.scholarship_id', $scholarshipId)
            ->where('sch_scholar_enrollments.school_year', $request->schoolYearFrom)
            ->where('sch_scholar_enrollments.semester', $request->semesterFrom)
            ->whereIn('sch_scholar_details.id', $request->selected_scholars)
            ->whereNull('sch_scholar_details.deleted_at')
            ->select('sch_scholar_details.*', 'students.FirstName', 'students.MiddleName', 'students.LastName')
            ->get();

        $existingScholars = [];

        foreach ($scholars as $scholar) {
            $existingScholar = DB::connection($campus)
                ->table('sch_scholar_enrollments')
                ->join('sch_scholar_details', 'sch_scholar_enrollments.scholar_id', '=', 'sch_scholar_details.id')
                ->where('sch_scholar_details.student_no', $scholar->student_no)
                ->where('sch_scholar_details.scholarship_id', $scholar->scholarship_id)
                ->where('sch_scholar_enrollments.school_year', $request->schoolYearTo)
                ->where('sch_scholar_enrollments.semester', $request->semesterTo)
                ->whereNull('sch_scholar_details.deleted_at')
                ->first();

            if ($existingScholar) {
                $existingScholars[] = [
                    'student_no' => $scholar->student_no,
                    'name' => $scholar->LastName . ', ' . $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : ''),
                ];
            }
        }

        if (count($existingScholars) > 0) {
            return response()->json([
                'error' => 'Some scholars already exist in the target semester.',
                'existing' => $existingScholars,
            ]);
        }

        foreach ($scholars as $scholar) {
            $existingScholarDetail = DB::connection($campus)
                ->table('sch_scholar_details')
                ->where('student_no', $scholar->student_no)
                ->where('scholarship_id', $scholar->scholarship_id)
                ->whereNull('deleted_at')
                ->first();

            if ($existingScholarDetail) {
                $newScholarId = $existingScholarDetail->id;
            } else {
                $newScholarId = DB::connection($campus)
                    ->table('sch_scholar_details')
                    ->insertGetId([
                        'student_no' => $scholar->student_no,
                        'scholarship_id' => $scholar->scholarship_id,
                        'date_awarded' => $scholar->date_awarded,
                        'contact_no' => $scholar->contact_no,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            DB::connection($campus)
                ->table('sch_scholar_enrollments')
                ->insert([
                    'scholar_id' => $newScholarId,
                    'school_year' => $request->schoolYearTo,
                    'semester' => $request->semesterTo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['success' => 'Scholars copied successfully.']);
    }

    // delete selected scholars
    public function deleteScholars(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            // Validate scholar IDs
            $scholarIds = $request->input('scholars');
            if (empty($scholarIds) || !is_array($scholarIds)) {
                return response()->json(['Error' => 1, 'Message' => 'No scholars selected for deletion.'], 400);
            }

            // Iterate through each scholar ID
            foreach ($scholarIds as $scholarId) {
                // Soft delete scholar enrollments
                $enrollments = ScholarEnrollment::on($campus)->where('scholar_id', $scholarId)->get();
                foreach ($enrollments as $enrollment) {
                    $enrollment->delete();
                }

                // Soft delete scholar details
                $scholar = ScholarDetail::on($campus)->find($scholarId);
                if ($scholar) {
                    $scholar->delete();
                }
            }

            return response()->json(['Error' => 0, 'Message' => 'Selected scholars deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'An error occurred while deleting scholars.'], 500);
        }
    }
}
