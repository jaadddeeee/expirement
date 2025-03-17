<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use App\Models\VARSITY\Event;
use Illuminate\Http\Request;
use App\Models\VARSITY\Varsity;
use App\Models\Student;
use App\Models\VARSITY\ListVarsity;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Exception;

class ScuaaController extends Controller
{
    public function index(Request $request)
    {
        $query = ListVarsity::with(['event' => function ($query) {
            $query->whereNull('deleted_at');; // Ensures only active events are included
        }])
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
        
    
        $events = Event::select('id', 'event')->orderby('event')->get();
    
        if ($request->ajax()) {
            return response()->json([
                'html' => view('_partials.scuaa-table', ['Lists' => $varsityList])->render()
            ]);
        }
    
        return view('slsu.varsity.VAR_scuaa.scuaa', [
            'pageTitle' => "SCUAA 8 REGIONAL GAMES - " . date('Y'),
            'page' => "SCUAA",
            'title' => "List of Athletes ",
            'headerAction' => '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>',
            'Lists' => $varsityList,
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
        // try {
        //     // Fetch all events
        //     $events = Event::all() ?? throw new Exception('No events found');

        //     // Encrypt event IDs before sending
        //     $events = $events->map(function ($event) {
        //         return [
        //             'id' => Crypt::encryptString($event->id),
        //             'event' => $event->event,
        //         ];
        //     });

        //     return response()->json($events);
        // } catch (Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], 400);
        // }

        try {
            // dd($request->id);
            $campus = auth()->user()->AllowSuper == 1 ? ($request->id ?? throw new Exception('Select campus')) : session('campus');
            // Fetch all events
            $events = DB::connection(strtolower($campus))
                ->table('var_event')
                ->select('id', 'event')
                ->orderby('event')
                ->get() ?? throw new Exception('No events found');

            // Encrypt event IDs before sending
            $events = $events->map(function ($event) {
                return [
                    'id' => Crypt::encryptString($event->id),
                    'event' => $event->event,
                ];
            });

            return response()->json($events);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
