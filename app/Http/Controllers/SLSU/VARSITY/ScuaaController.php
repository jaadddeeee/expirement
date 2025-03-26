<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use App\Models\VARSITY\Event;
use Illuminate\Http\Request;
use App\Models\VARSITY\Varsity;
use App\Models\VARSITY\Scuaa;
use App\Models\Student;
use App\Models\VARSITY\ListVarsity;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Http\Controllers\SLSU\Report\ScuaaReport;
use General;

class ScuaaController extends Controller
{
    public function index(Request $request)
    {
        $query = ListVarsity::with(['event' => function ($query) {
        }])
        ->whereNull('deleted_at')
        ->orderBy('SchoolYear', 'desc');
        
        if ($request->has('filterEvent') && $request->filterEvent != '0') {
            $query->where('Event', $request->filterEvent);
        }
        
        if ($request->has('filterSchoolYear') && $request->filterSchoolYear != '0') {
            $query->where('var_scuaa_list.SchoolYear', $request->filterSchoolYear);
        }
        
        if ($request->has('search') && !empty($request->search)) {
            $studentNos = collect();
        
            // Search across multiple databases
            $connections = ['sg', 'mcc', 'to', 'bn', 'sj', 'hn'];
            foreach ($connections as $connection) {
                $students = Student::on($connection)
                    ->where('LastName', 'LIKE', "%{$request->search}%")
                    ->orWhere('FirstName', 'LIKE', "%{$request->search}%")
                    ->orWhere('MiddleName', 'LIKE', "%{$request->search}%")
                    ->orWhere('StudentNo', 'LIKE', "%{$request->search}%") // Get matching StudentNo
                    ->pluck('StudentNo');
        
                $studentNos = $studentNos->merge($students);
            }
        
            // Filter ListVarsity by matching StudentNo values
            $query->whereIn('StudentNo', $studentNos->unique());
        }
        
        $varsityList = $query->paginate(10);
        
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
                'StudentNo'  => $item->StudentNo,
                'FirstName'  => $student->FirstName ?? null,
                'LastName'   => $student->LastName ?? null,
                'MiddleName' => $student->MiddleName ?? null,
                'event_name' => $item->event->event ?? null,
            ];
        });

        $scuaaLists = Scuaa::all(); // Fetch all records
        
    
        $events = Event::select('id', 'event')->orderby('event')->get();
    
        return view('slsu.varsity.VAR_scuaa.scuaa', [
            'pageTitle' => "SCUAA 8 REGIONAL GAMES - " . date('Y'),
            'page' => "SCUAA",
            'title' => "List of Athletes ",
            'headerAction' => '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>',
            'Lists' => $varsityList,
            'ScuaaLists' => $scuaaLists,
            'Events' => $events
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

    public function scuaaList(Request $request){
        $pdf = new ScuaaReport('P', 'cm', array(330.2, 215.9));
        $pdf->setId($request->DateOfGraduation);
        $pdf->setSy($request->SchoolYear);
        $pdf->setSem($request->Semester);
    
        // HEADER
    
    
        $pdf::setHeaderCallback(function($p) use ($pdf){
            $pdf->Header();
    
        });
    
        // $pdf::setFooterCallback(function($p) use ($pdf){
        //   $pdf->Footer();
        // });
    
        $pdf::AddPage('L', array(215.9, 330.2));
        $pdf::SetTopMargin(57);
        $pdf::SetAutoPageBreak(TRUE,20);
        $pdf->Body();
        $date = \Str::slug($request->DateOfGraduation);
    
        $fname = "scuaalist-".$date.".pdf";
    
        // $public = "public";
        // $directoryPath = 'prcgraduation/'.session('campus');
        // if (!Storage::exists($public."/".$directoryPath)) {
        //   Storage::makeDirectory($public."/".$directoryPath);
        // }
    
        $pdf::Output(storage_path($fname),'I');
    
        // return response()->download($fname);
    }
}
