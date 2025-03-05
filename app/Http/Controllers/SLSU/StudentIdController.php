<?php

namespace App\Http\Controllers\SLSU;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\StudentId;
use Intervention\Image\Facades\Image;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Auth;

class StudentIdController extends Controller
{

    public function index(Request $request)
    {
        $pageTitle = "School ID";

        $query = $request->get('search');

        $student = DB::connection(strtolower(session('campus')))
            ->table('students')
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('StudentNo', 'like', '%' . $query . '%')
                    ->orWhere('FirstName', 'like', '%' . $query . '%')
                    ->orWhere('LastName', 'like', '%' . $query . '%');
            })
            ->paginate(10);


        if ($request->ajax()) {
            return view('_partials.studentid.student-table', ['student' => $student])->render();
        }

        return view('slsu.studentid.index', [
            'pageTitle' => $pageTitle,
            'student' => $student
        ]);
    }

    public function getprocessid(Request $request)
    {
        $pageTitle = "Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';

        $decrypted_id = Crypt::decryptString($request->stuid);

        $student = DB::connection(strtolower(session('campus')))
            ->table('students')
            ->where('StudentNo', $decrypted_id)
            ->first();

        $student2 = DB::connection(strtolower(session('campus')))
            ->table('students2')
            ->where('StudentNo', $decrypted_id)->first();

        $registration = DB::connection(strtolower(session('campus')))
            ->table('registration')
            ->where('StudentNo', $decrypted_id)
            ->orderBy('SchoolYear', 'desc')
            ->orderBy('Semester', 'desc')
            ->first();

        return view('slsu.studentid.processid', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' => $student,
            'student2' => $student2,
            'registration' => $registration,
        ]);
    }

    public function update(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);
    
        // Determine if the student is a first-year student
        $isFirstYear = $request->input('is_first_year', false);
    
        // Set validation rules
        $rules = [
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg',
            'blood_type' => 'nullable|string',
            'allergy' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'barangay' => 'nullable|string',
            'municipality' => 'nullable|string',
            'province' => 'nullable|string',
            'is_first_year' => 'nullable|boolean',
        ];
    
        // Add OR No. and Date Paid validation rules if not a first-year student
        if (!$isFirstYear) {
            $rules['or_number'] = 'required|string';
            $rules['date_paid'] = 'required|date';
        } else {
            $rules['or_number'] = 'nullable|string';
            $rules['date_paid'] = 'nullable|date';
        }
    
        // Validate the request
        $validated = $request->validate($rules);
    
        $campusConnection = DB::connection(strtolower(session('campus')));
    
        $studentData = [
            'emer_name' => $validated['contact_name'],
            'emer_contact' => $validated['contact_number'],
            'p_street' => $validated['barangay'],
            'p_municipality' => $validated['municipality'],
            'p_province' => $validated['province'],
        ];
    
        if ($request->hasFile('profilePicture')) {
            $image = $request->file('profilePicture');
            $filename = $decrypted_id . '.' . $image->getClientOriginalExtension();
            $imagePath = 'storage/student_id_picture/' . $filename;
            $image->move(public_path('storage/student_id_picture'), $filename);
            $studentData['Picture'] = $imagePath;
        }
    
        $campusConnection->table('students')->updateOrInsert(
            ['StudentNo' => $decrypted_id],
            $studentData
        );
    
        if ($request->hasFile('signature')) {
            $signature = $request->file('signature');
            $signatureFilename = $decrypted_id . '.' . $signature->getClientOriginalExtension();
    
            $signatureImage = Image::make($signature);
            $signatureImage->resize(279, 114);
    
            $signatureImage->save(public_path('storage/student_id_signature/' . $signatureFilename));
        }
    
        $campusConnection->table('students2')->updateOrInsert(
            ['StudentNo' => $decrypted_id],
            [
                'BloodType' => $validated['blood_type'],
                'Allergy' => $validated['allergy'],
            ]
        );
    
        $paymentData = [
            'StudentNo' => $decrypted_id,
            'free_tuition' => $validated['is_first_year'],
        ];
    
        if (!$validated['is_first_year']) {
            $paymentData['or_no'] = $validated['or_number'];
            $paymentData['date_of_payment'] = $validated['date_paid'];
        }
    
        $campusConnection->table('stuid_payment')->updateOrInsert(
            ['StudentNo' => $decrypted_id],
            $paymentData
        );
    
        return response()->json([
            'success' => true,
            'message' => 'Student ID updated successfully.',
            'encryptedStudentNo' => Crypt::encryptString($decrypted_id),
        ]);
    }

    public function getprintpreview(Request $request, StudentId $pdfService)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);

        $defaultValues = DB::connection(strtolower(session('campus')))
            ->table('defaultvalue')
            ->whereIn('DefaultName', ['CampusString', 'SchoolAddress', 'PresidentName', 'SchoolWebsite'])
            ->pluck('DefaultValue', 'DefaultName');

        $student = DB::connection(strtolower(session('campus')))
            ->table('students')
            ->where('StudentNo', $decrypted_id)
            ->first();

        $student2 = DB::connection(strtolower(session('campus')))
            ->table('students2')
            ->select('BloodType', 'Allergy')
            ->where('StudentNo', $decrypted_id)
            ->first();

        $registration = Registration::where('StudentNo', $decrypted_id)
            ->orderBy('SchoolYear', 'desc')
            ->orderBy('Semester', 'desc')
            ->first();

        $pdfService->generatePDF($decrypted_id, $student, $student2, $registration);

        $pageTitle = "Preview Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1"></i><span>Back</span></a>';

        return view('slsu.studentid.printpreview', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'student' => $student,
            'student2' => $student2,
            'registration' => $registration,
            'defaultValues' => $defaultValues,
        ]);
    }

    public function print(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->stuid);
        $fileName = $decrypted_id . '.pdf';
        $filePath = public_path('storage/student_id/' . $fileName);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fileUrl = asset('storage/student_id/' . $fileName);

        $student = DB::connection(strtolower(session('campus')))
            ->table('students')
            ->where('StudentNo', $decrypted_id)
            ->first();

        DB::connection(strtolower(session('campus')))
        ->table('prints_log')->insert([
            'student_id' => $decrypted_id,
            'student_name' => $student->FirstName . ' '. $student->MiddleName . '. '. $student->LastName, 
            'printed_by' => Auth::user()->UserName,
            'printed_at' => now(),
            'file_name' => $fileName,
        ]);

        return response()->json([
            'success' => true,
            'file_url' => $fileUrl
        ]);
    }
}
