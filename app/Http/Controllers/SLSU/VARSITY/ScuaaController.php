<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use App\Models\VARSITY\Event;
use Illuminate\Http\Request;
use App\Models\VARSITY\Varsity;
use App\Models\VARSITY\Scuaa;
use App\Models\Student;
use App\Models\Employee;
use App\Models\VARSITY\ListVarsity;
use App\Models\VARSITY\CoachVarsity;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Http\Controllers\SLSU\Report\Varsity\ScuaaReport;
use App\Http\Controllers\SLSU\Report\Varsity\ChecklistReport;
use App\Http\Controllers\SLSU\Report\Varsity\EligibilityForm;
use General;

class ScuaaController extends Controller
{
    public function indexCoaches(Request $request)
    {
        $query = CoachVarsity::with(['event'])
            ->whereNull('deleted_at')
            ->orderBy('SchoolYear', 'desc');

        if ($request->has('filterEvent') && $request->filterEvent != '0') {
            $query->where('Event', $request->filterEvent);
        }
        
        if ($request->has('filterSchoolYear') && $request->filterSchoolYear != '0') {
            $query->where('SchoolYear', $request->filterSchoolYear);
        }
        
        if ($request->has('searchCoach') && !empty($request->searchCoach)) {
            $Emp = DB::connection('hrmis')
                ->table('employee')
                ->whereIn('campus', [1, 2, 3, 4, 5, 6])
                ->where(function ($query) use ($request) {
                    $query->where('LastName', 'LIKE', "%{$request->searchCoach}%")
                        ->orWhere('FirstName', 'LIKE', "%{$request->searchCoach}%")
                        ->orWhere('MiddleName', 'LIKE', "%{$request->searchCoach}%")
                        ->orWhere('id', 'LIKE', "%{$request->searchCoach}%");
                })
                ->whereNull('deleted_at');
        
            $query->whereIn('CoachID', $Emp->pluck('id'));
        }

        $rowsPerPage = $request->input('rowsPerPage', 5);
    
        $coachList = $query->paginate($rowsPerPage);
        
        // Fetch employee data from hrmis.employee
        $employeeData = DB::connection('hrmis')
            ->table('employee')
            ->whereIn('id', $coachList->pluck('CoachID'))
            ->whereIn('campus', [1, 2, 3, 4, 5, 6])
            ->get();
        
        $coachList->getCollection()->transform(function ($item) use ($employeeData) {
            $emp = $employeeData->firstWhere('id', $item->CoachID);
            
            return (object) [
                'SchoolYear' => $item->SchoolYear,
                'id'      => $item->id,
                'FirstName'  => $emp->FirstName ?? null,
                'LastName'   => $emp->LastName ?? null,
                'MiddleName' => $emp->MiddleName ?? null,
                'event_name' => $item->event->event ?? null,
            ];
        });

        $events = Event::select('id', 'event')->orderby('event')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('_partials.scuaa-coach-table', ['Coaches' => $coachList])->render()
            ]);
        }
        
        return view('slsu.varsity.VAR_scuaa.coaches_list', [
            'pageTitle' => "SCUAA 8 REGIONAL GAMES - " . date('Y'),
            'page' => "SCUAA",
            'title_coach' => "List of Coaches",
            'headerAction' => '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>',
            'Coaches' => $coachList,
            'Events' => $events,
            'rowsPerPage' => $rowsPerPage
        ]);
    }

    public function indexAthletes(Request $request)
    {
        $query = ListVarsity::with(['event'])
        ->whereNull('deleted_at')
        ->orderBy('SchoolYear', 'desc');
        
        if ($request->has('filterEvent') && $request->filterEvent != '0') {
            $query->where('Event', $request->filterEvent);
        }
        
        if ($request->has('filterSchoolYear') && $request->filterSchoolYear != '0') {
            $query->where('SchoolYear', $request->filterSchoolYear);
        }
        
        if ($request->has('searchAthletes') && !empty($request->searchAthletes)) {
            $studentNos = collect();
        
            // Search across multiple databases
            $connections = ['sg', 'mcc', 'to', 'bn', 'sj', 'hn'];
            foreach ($connections as $connection) {
                $students = Student::on($connection)
                    ->where('LastName', 'LIKE', "%{$request->searchAthletes}%")
                    ->orWhere('FirstName', 'LIKE', "%{$request->searchAthletes}%")
                    ->orWhere('MiddleName', 'LIKE', "%{$request->searchAthletes}%")
                    ->orWhere('StudentNo', 'LIKE', "%{$request->searchAthletes}%") // Get matching StudentNo
                    ->pluck('StudentNo');
        
                $studentNos = $studentNos->merge($students);
            }
        
            // Filter ListVarsity by matching StudentNo values
            $query->whereIn('StudentNo', $studentNos->unique());
        }

        $rowsPerPage = $request->input('rowsPerPage', 5);
        
        $varsityList = $query->paginate($rowsPerPage);
        
        // Fetch student data from multiple databases (batch query)
        $connections = ['sg','mcc', 'to', 'bn', 'sj', 'hn'];
        $allStudents = collect();
        foreach ($connections as $connection) {
            $allStudents = $allStudents->merge(
                Student::on($connection)->whereIn('StudentNo', $varsityList->pluck('StudentNo'))->get()
            );
        }
        
        $varsityList->getCollection()->transform(function ($item) use ($allStudents) {
            $student = $allStudents->firstWhere('StudentNo', $item->StudentNo);
            
            return (object) [
                'SchoolYear' => $item->SchoolYear,
                'id'         => $item->id,
                'FirstName'  => $student->FirstName ?? null,
                'LastName'   => $student->LastName ?? null,
                'MiddleName' => $student->MiddleName ?? null,
                'event_name' => $item->event->event ?? null,
            ];
        });

        $events = Event::select('id', 'event')->orderby('event')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('_partials.scuaa-table', ['Lists' => $varsityList])->render()
            ]);
        }
        
        return view('slsu.varsity.VAR_scuaa.athletes_list', [
            'pageTitle' => "SCUAA 8 REGIONAL GAMES - " . date('Y'),
            'page' => "SCUAA",
            'title_athlete' => "List of Athletes ",
            'headerAction' => '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>',
            'Lists' => $varsityList,
            'Events' => $events,
            'rowsPerPage' => $rowsPerPage
        ]);
    }
    
    public function setEvent(Request $request)
    {
        try {
            $total = $request->totalPart;
            $event = $request->filterEvent;
    
            // Count VarsityStudent records in var_scuaa that are linked to var_varsity by ID
            $EventCount = ListVarsity::where('Event', $event)->count();
            
            // Only update if the count does not match the total athletes
            if ($EventCount < $total) {
                Event::where('id', $event)
                ->update(['totalAtlhetes' => $total]);
                return response()->json(['success' => true, 'message' => 'Total athletes has been set successfully.']);
            }
    
            return response()->json(['Error' => \GENERAL::Error("You can't decrease the total athletes, Total Athletes " . $EventCount)], 400);
        } catch (DecryptException) {
            return response()->json(['Error' => \GENERAL::Error("Invalid encrypted ID.")], 400);
        } catch (Exception $e) {
            return response()->json(['Error' => \GENERAL::Error($e->getMessage())], 400);
        }
    }    

    public function setScuaa(Request $request)
    {
        try {
            // Validate required fields
            $title = $request->title ?? throw new Exception('Title is required');
            $university = $request->University ?? throw new Exception('University is required');
            $municipality = $request->Municipality ?? throw new Exception('Municipality is required');
            $province = $request->Province ?? throw new Exception('Province is required');
            $date = $request->Date ?? throw new Exception('Date is required');
            $theme = $request->Theme ?? throw new Exception('Theme is required');
            $dates = explode(" to ", $date);
            if (count($dates) != 2) {
                throw new Exception('Invalid date range format');
            }
            $startDate = date('F j', strtotime($dates[0]));
            $endDate = date('j, Y', strtotime($dates[1]));
            $formattedDate = $startDate . '-' . $endDate;
    
            // Prevent duplicate records
            $exists = Scuaa::where('Title', $title)->where('Date', $date)->exists();
            if ($exists) {
                throw new Exception('This Scuaa record already exists.');
            }
    
            // Validate file upload
            if (!$request->hasFile('ScuaaLogo')) {
                throw new Exception('Logo is required');
            }
    
            // Store file only if it doesn’t exist
            $file = $request->file('ScuaaLogo');
            $filePath = $file->store('scuaa_logos', 'public');
    
            // Combine Municipality & Province into Location
            $location = "{$municipality}, {$province}";
    
            // Save to Database
            $setScuaa = Scuaa::create([
                'Title' => $title,
                'Theme' => $theme,
                'ScuaaLogo' => $filePath, // Save file path instead of raw file
                'University' => $university,
                'Location' => $location,
                'Date' => $formattedDate,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Scuaa record created successfully!',
                'file_path' => asset('storage/' . $filePath), // Provide file URL
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function scuaaList(Request $request)
    {
        try{
            if(!$request->has('filterSchoolYear') || $request->filterSchoolYear == '0'){
                throw new Exception('Please select an school year.');
            }
            $date = $request->date;
            $dates = explode(" to ", $date);
            if (count($dates) != 2) {
                throw new Exception('Invalid date range format');
            }
            $startDate = date('F j', strtotime($dates[0]));
            $endDate = date('j, Y', strtotime($dates[1]));
            $formattedDate = $startDate . '-' . $endDate;

            $pdf = new ScuaaReport('P', 'cm', array(330.2, 215.9));
            $pdf->setSy($request->filterSchoolYear);
            $pdf->setEvent($request->filterEvent);
            $pdf->setScreen($formattedDate);
        
            // HEADER
        
        
            $pdf::setHeaderCallback(function($p) use ($pdf){
                $pdf->Header();
        
            });
        
            $pdf::AddPage('L', array(215.9, 330.2));
            $pdf::SetTopMargin(57);
            $pdf::SetAutoPageBreak(TRUE,20);
            $pdf->Body();
            $date = $pdf->getDate();
            $event = $pdf->getSport();
            $gender = $pdf->getGender();
        
            $fname = "scuaa-list-".$event."-".$gender."-".$date.".pdf";
        
            $public = "public";
            $directoryPath = 'varsity/scuaa-offcial-entry/';
            if (!Storage::exists($public."/".$directoryPath)) {
                Storage::makeDirectory($public."/".$directoryPath);
            }
            $filePath = storage_path("app/public/" . $directoryPath . $fname);
            $pdf::Output($filePath,'I');
        
            return response()->download($filePath)->deleteFileAfterSend(true);
        }
        catch(Exception $e){
            return response()->json(['Error' => $e->getMessage()], 400);
        }
    }

    public function scuaaChecklist(Request $request)
    {
        $pdf = new ChecklistReport('P', 'cm', array(330.2, 215.9));

        $date = $request->date;
        $dates = explode(" to ", $date);
        if (count($dates) != 2) {
            throw new Exception('Invalid date range format');
        }
        $startDate = date('F j', strtotime($dates[0]));
        $endDate = date('j, Y', strtotime($dates[1]));
        $formattedDate = $startDate . '-' . $endDate;

        $pdf->setScreen($formattedDate);
        $pdf->setSy($request->filterSchoolYear);
        $pdf->setEvent($request->filterEvent);
    
        // HEADER
    
        $pdf::setHeaderCallback(function($p) use ($pdf){
            $pdf->Header();
    
        });
    
        $pdf::setFooterCallback(function($p) use ($pdf){
            $pdf->Footer();
        });
    
        $pdf::AddPage('L', array(215.9, 330.2));
        $pdf::SetTopMargin(57);
        $pdf::SetAutoPageBreak(TRUE,20);
        $pdf->Body();
        $date = $pdf->getDate();
        $gender = $pdf->getGender();
        $event = $pdf->getSport();
    
        $fname = "checklist-".$event."-".$gender."-".$date.".pdf";
        $public = "public";
        $directoryPath = 'varsity/check-list/';
        if (!Storage::exists($public."/".$directoryPath)) {
            Storage::makeDirectory($public."/".$directoryPath);
        }
        $filePath = storage_path("app/public/" . $directoryPath . $fname);
        $pdf::Output($filePath,'F');
    
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function scuaaEligibility(Request $request)
    {
        try {
            $id = Crypt::decryptstring($request->id);
            $status = $request->status;
        } catch (DecryptException $e) {
            session(['ErrorBlob' => "Invalid Hash"]);
            return response()->json(['error' => "Invalid Hash"], 400);
        }
    
        $pdf = new EligibilityForm('P', 'cm', array(215.9, 330.2));
        $pdf->setId($id);
        $pdf->setStatus($status);
    
        // Set up the PDF
        $pdf::setHeaderCallback(function ($p) use ($pdf) {
            $pdf->Header();
        });
    
        $pdf::AddPage();
        $pdf::SetTopMargin(40);
        $pdf::SetLeftMargin(15);
        $pdf::SetRightMargin(15);
        $pdf::SetAutoPageBreak(TRUE, 10);
        $pdf->Body();
    
        $date = $pdf->getDate();
        $fname = $pdf->getName() . "-eligibility-" . $date . ".pdf";
        $directoryPath = 'varsity/scuaa-eligibility/';
        $public = "public";
    
        // Ensure the directory exists
        if (!Storage::exists($public . "/" . $directoryPath)) {
            Storage::makeDirectory($public . "/" . $directoryPath);
        }
    
        $filePath = storage_path("app/public/" . $directoryPath . $fname);
        $pdf::Output($filePath, 'F');
    
        // Return the file for download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function destroyAthletes(Request $request)
    {
        try {
            $athletesID = Crypt::decryptstring($request->id);

            // dd($athletesID);
            $scuaa = ListVarsity::findOrFail($athletesID);
            $scuaa->delete();
    
            return response()->json(['success' => true, 'message' => 'Scuaa record deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroyCoaches(Request $request)
    {
        try {
            $coachID = Crypt::decryptstring($request->id);;

            $coach = CoachVarsity::findOrFail($coachID);
            $coach->delete();
    
            return response()->json(['success' => true, 'message' => 'Athlete record deleted successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
