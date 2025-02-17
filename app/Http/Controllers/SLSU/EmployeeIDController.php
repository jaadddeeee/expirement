<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student2;
use App\Models\Registration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Services\EmployeeID;

class EmployeeIDController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = "Employee ID";

        $query = $request->get('search');

        $employee = DB::connection('hrmis', strtolower(session('campus')))
        ->table('employee')
        ->when($query, function ($queryBuilder) use ($query) {
            return $queryBuilder->where('AgencyNumber', 'like', '%' . $query . '%')
                                ->orWhere('FirstName', 'like', '%' . $query . '%')
                                ->orWhere('LastName', 'like', '%' . $query . '%');
        })
        ->paginate(10);

        if ($request->ajax()) {
            return view('_partials.employeeid.employee-table', ['employee' => $employee])->render();
        }

        return view('slsu.employeeid.index', [
            'pageTitle' => $pageTitle,
            'employee' => $employee
        ]);
    }
    
    public function getprocessid(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->emid);
    
        $employee = DB::connection('hrmis', strtolower(session('campus')))
        ->table('employee')
        ->where('id', $decrypted_id)
        ->first();

        $employee2 = DB::connection('hrmis', strtolower(session('campus')))
        ->table('emergencycontact')
        ->where('empId', $decrypted_id)
        ->first();

        $pageTitle = "Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';
       

        return view('slsu.employeeid.processid', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'employee' => $employee,
            'employee2' => $employee2,
        ]);
    }

    public function update(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->emid);

        $validated = $request->validate([
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg',
            'blood_type' => 'nullable|string',
            'allergy' => 'nullable|string',
            'contact_name' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'barangay' => 'nullable|string',
            'municipality' => 'nullable|string',
            'province' => 'nullable|string',
            'or_number' => 'required|string',
            'date_paid' => 'nullable|date',
        ]);

        $campusConnection = DB::connection('hrmis', strtolower(session('campus')));

        $employeeData = [
            'BloodType' => $request->blood_type,
            'Allergies' => $request->allergy,
        ];

        if ($request->hasFile('profilePicture')) {
            $image = $request->file('profilePicture');
            $filename = $decrypted_id . '.' . $image->getClientOriginalExtension();
            $imagePath = 'storage/employee_id_picture/' . $filename;
            $image->move(public_path('storage/employee_id_picture'), $filename);
            $employeeData['profilephoto'] = $imagePath;
        }

        $campusConnection->table('employee')->where('id', $decrypted_id)->update($employeeData);    

        if ($request->hasFile('signature')) {
            $signature = $request->file('signature');
            $signatureFilename = $decrypted_id . '.' . $signature->getClientOriginalExtension();
            $signature->move(public_path('storage/student_id_signature'), $signatureFilename);
        }

        // $campusConnection->table('emergencycontact')->where('empId', $decrypted_id)->update([
        //     'address' => $validated['barangay'] . ', ' . $validated['municipality'] . ', ' . $validated['province'],
        // ]);
        

        return response()->json([
            'success' => true,
            'message' => 'Student ID updated successfully.',
            'encryptedEmployeeID' => Crypt::encryptString($decrypted_id),
        ]);
    }

    public function getprintpreview(Request $request, EmployeeID $pdfService)
    {
        $decrypted_id = Crypt::decryptString($request->emid);

        $defaultValues = DB::connection(strtolower(session('campus')))
        ->table('defaultvalue')
        ->whereIn('DefaultName', ['CampusString', 'SchoolAddress', 'PresidentName', 'SchoolWebsite'])
        ->pluck('DefaultValue', 'DefaultName');

        $employee = DB::connection('hrmis', strtolower(session('campus')))
        ->table('employee')
        ->where('id', $decrypted_id)
        ->first();

        $employee2 = DB::connection('hrmis', strtolower(session('campus')))
        ->table('emergencycontact')
        ->where('empId', $decrypted_id)
        ->first();

        $pageTitle = "Preview Process ID";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button"><i class="bx bx-chevron-left me-1" ></i><span>Back</span></a>';

        $pdfService->generatePDF($decrypted_id, $employee, $employee2);
         
        return view('slsu.employeeid.printpreview', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'employee' =>  $employee,
            'employee2' =>  $employee2,
            'defaultValues' => $defaultValues,
        ]);
    }   

    public function print(Request $request)
    {
        $decrypted_id = Crypt::decryptString($request->emid);

        $fileName = $decrypted_id . '.pdf';
        $pdfPath = public_path('storage/student_id/'. $fileName);
        $printerName = 'Evolis Primacy'; 

        $command = "lp -d $printerName $pdfPath";
        exec($command);

        return response()->json(['message' => 'Printing started']);
    }
}
