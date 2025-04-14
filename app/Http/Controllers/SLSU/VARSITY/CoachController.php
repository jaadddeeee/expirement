<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VARSITY\Coach;
use App\Models\VARSITY\CoachVarsity;
use Illuminate\Support\Facades\DB;
use App\Models\VARSITY\Event;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use GENERAL;
use Exception;
    

class CoachController extends Controller
{
    public function index(Request $request)
{
    $campus = auth()->user()->AllowSuper == 1 ? ($request->filterCampus ?? "SG") : session('campus');

    $employees = 'db_hrmis.employee';

    $query = DB::connection(strtolower($campus))
    ->table('var_coaches')
    ->leftJoin('var_event', 'var_coaches.CoachEvent', '=', 'var_event.id')
    ->leftJoin("$employees as employee", 'var_coaches.EmpNo', '=', 'employee.id') // Use HRMIS DB dynamically
    ->whereNull('var_coaches.deleted_at')
    ->orderBy('employee.LastName', 'asc')
    ->select(
        'employee.FirstName as FirstName',
        'employee.MiddleName as MiddleName',
        'employee.LastName as LastName',
        'var_coaches.*',
        'var_event.event as event_name'
    );

    if ($request->has('filterCampus') && $request->filterCampus != '0') {
        $query = $query;
    }

    if ($request->has('filterCT') && $request->filterCT != '0') {
        $query->where('CoachType', $request->filterCT);
    }

    if ($request->has('search') && !empty($request->search)) {
        $query->where(function ($q) use ($request) {
            $q->where('LastName', 'LIKE', "%{$request->search}%")
                ->orWhere('FirstName', 'LIKE', "%{$request->search}%")
                ->orWhere('MiddleName', 'LIKE', "%{$request->search}%");
        });
    }

    $rowsPerPage = $request->input('rowsPerPage', 5);
    
    $coach = $query->paginate($rowsPerPage);

    $currentYear = date('Y');
    foreach ($coach as $item) { // Iterate through the paginated collection
        $item->alreadyExists = CoachVarsity::where('CoachID', $item->EmpNo)
            ->where('SchoolYear', $currentYear)
            ->first();
    }

    if ($request->ajax()) {
        return response()->json([
            'html' => view('_partials.coach-table', ['coaches' => $coach])->render()
        ]);
    }

    $pageTitle = "Manage Coaches";
    $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
    return view('slsu.varsity.VAR_coach.coach',[
        'pageTitle' => $pageTitle,
        'headerAction' => $headerAction,
        'coaches' => $coach,
        'Campus' => $campus,
        'rowPerPage' => $rowsPerPage,
        ]);
}   

    public function emplist(Request $request)
{
    try {
        $campus = auth()->user()->AllowSuper == 1 ? ($request->id ?? throw new Exception('Select Campus')) : session('campus');

        $campus = GENERAL::Campuses()[$campus]['ID'];
        $employees = DB::connection('hrmis')
            ->table('employee')
            ->whereNull('deleted_at')
            ->where('campus',  $campus)
            ->orderBy('FirstName')
            ->orderBy('LastName')
            ->get() ?? throw new Exception('No employees found.');

        $employees = $employees->map(function ($employee) {
            return [
                'id' => Crypt::encryptString($employee->id), 
                'LastName' => $employee->LastName, 
                'FirstName' => $employee->FirstName,  
                'MiddleName' => $employee->MiddleName,  
            ];
        });

        return response()->json($employees);
    } catch (Exception $e) {
        return response()->json(['errors' => $e->getMessage()], 400);
    }
}

    public function eventlist(Request $request)
{
    try {
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

    public function save(Request $request)
{
    try {
        $campus = auth()->user()->AllowSuper == 1 ? ($request->Campus ?? throw new Exception('Select campus')) : session('campus');
        
        $EmpNo = Crypt::decryptString($request->EmployeeID) ?? throw new Exception('Please select student'); // Decrypt student number
        $event = Crypt::decryptString($request->Event ?: throw new Exception("Please select event"));
        $ct = $request->coachType ?? throw new Exception("Please select coach type");

        if (!$request->hasFile('CoachImage')) {
            throw new Exception('Logo is required');
        }

        $picture = $request->file('CoachImage');
        
        $campusID = GENERAL::Campuses()[$campus]['ID'];

        // Check for duplicate entry
        $C = DB::connection(strtolower($campus))
            ->table('var_coaches')
            ->where("id", $EmpNo)
            ->exists() && throw new Exception('Duplicate entry detected for this coach');
        
        DB::connection('hrmis')
            ->table('employee')
            ->whereNull('deleted_at')
            ->where('campus',  $campusID)
            ->where('id', $EmpNo)
            ->first() ?? throw new Exception('This employee is not exist in this campus');

        // Check if the event already has a main coach in the same campus
        $mainCoach = Coach::on(strtolower($campus))
            ->where('CoachEvent', $event)
            ->where('CoachType', $ct)
            ->whereNull('deleted_at')
            ->exists();

            ($mainCoach && $ct == 1) && throw new Exception('This event already has a main coach');

        $imagePath = null;
        if ($picture) {
            $imagePath = $picture->store("coachphoto/".strtoupper($campus), 'public'); // Store in storage/app/public/coachphoto/{campus}
            // \Log::info('Image stored at: ' . $imagePath);
        }
        
        Coach::on(strtolower($campus))
            ->create([
                'EmpNo' => $EmpNo,
                'CoachType' => $ct,
                'CoachEvent' => $event,
                'Picture' => $imagePath,
            ]);

        return response()->json(['Error' => 0, "Message" => "Coach successfully added."]);
    } catch (DecryptException) {
        return response()->json(['Error' => GENERAL::Error("Invalid encrypted ID.")], 400);
    } catch (\Exception $e) {
        return response()->json(['Error' => General::Error($e->getMessage())], 400);
    }
}
    
    public function edit(Request $request)
{
    try {
        $id = Crypt::decryptString($request->id); 
        // Ensure the correct relationship
        $campus = auth()->user()->AllowSuper == 1 ? ($request->campus ?: throw new Exception('Select Campus')) : session('campus');
        $editCoach = DB::connection(strtolower($campus))
        ->table('var_coaches')
        ->join('var_event', 'var_coaches.CoachEvent', '=', 'var_event.id')
        ->join('db_hrmis.employee as employee', 'var_coaches.EmpNo', '=', 'employee.id')
        ->where('var_coaches.id', $id)
        ->select('var_coaches.*',
            'employee.FirstName as FirstName',
            'employee.MiddleName as MiddleName',
            'employee.LastName as LastName',
            'var_event.event as event'
        )
        ->first() ?? throw new Exception('Record not found.');
        // dd($editCoach);
        return response()->json([
            'id' => $editCoach->id,
            'campus' => $campus,
            'emp' => trim($editCoach->LastName . ', ' . $editCoach->FirstName . ($editCoach->MiddleName ? ', ' . $editCoach->MiddleName : '')),
            'coachType' => $editCoach->CoachType,
            'event' => $editCoach->event,
        ]);

    } catch (DecryptException $e) {
        return response()->json(['errors' => 'Invalid request.'], 400);
    } catch (\Exception $e) {
        return response()->json(['errors' => $e->getMessage()], 400);
    }
}

    public function update(Request $request)
{
    try {
        $decryptedId = Crypt::decryptString($request->hiddentID);
        $campus = auth()->user()->AllowSuper ? ($request->id ?: throw new Exception('Select campus')) : session('campus');

        //Get the coach base on campus
        $ch = DB::connection(strtolower($campus))
            ->table('var_coaches')
            ->join('var_event', 'var_coaches.CoachEvent', '=', 'var_event.id')
            ->where('var_coaches.id', $decryptedId)
            ->select('var_coaches.*', 'var_event.event as event')
            ->first() ?? throw new Exception('Coach record not found.');

        $ct = $request->updateCT ?? throw new Exception('Please select coach type');
        $event = Crypt::decryptString($request->updateEvent) ?? throw new Exception('Please select event');

        if ($ch->CoachType == $ct && $ch->CoachEvent == $event) 
            throw new Exception("Invalid, No changes detected.");

            //check if that event already has a main coach
            $mainCoachExists = Coach::on(strtolower($campus))
            ->where('CoachEvent', $event)
            ->where('CoachType', 1) 
            ->where('id', '!=', $decryptedId) 
            ->whereNull('deleted_at')
            ->exists();

        ($mainCoachExists && $ct == 1) && throw new Exception("This event already has a main coach");
        
        // Update coach details
        Coach::on(strtolower($campus))
            ->where('id', $decryptedId)
            ->update([
                'CoachType' => $ct,
                'CoachEvent' => $event
            ]);
        
        return response()->json(['Error' => 0, "Message" => "Coach successfully updated"]);
    } catch (DecryptException $e) {
        return response()->json(['Error' => General::Error("Invalid encrypted ID.")], 400);
    } catch (Exception $e) {
        return response()->json(['Error' => General::Error($e->getMessage())], 400);
    }
}
    
    public function deleteCoach(Request $request)
{
    try{
        $id = Crypt::decryptstring($request->id);

        $campus = auth()->user()->AllowSuper == 1 ? ($request->campus ?? throw new Exception('Select Campus')) : session('campus');

        // Find the Varsity record, delete record
        $varsity = Coach::on(strtolower($campus))
                    ->where('id', $id)
                    ->firstOrFail();

        $varsity->delete();

        return response()->json(['success' => true, 'message' => 'Varsity successfully deleted.']);
    }catch(Exception $e){
        return response()->json(['errors' => General::Error($e->getMessage())], 400);
    }catch(DecryptException $e){
        return response()->json(['errors' => General::Error('Invalid encrypted ID.')], 400);
    }
}

public function saveSelectedCoach(Request $request)
{
    try {
        $campus = auth()->user()->AllowSuper == 1 ? ($request->campus ?? throw new Exception('Select Campus')) : session('campus');

        // // Retrieve the selected varsity IDs from the request
        $selectedVarsities = $request->input('selectedCoach');

        $EmpNo = Crypt::decryptString($request->EmployeeID) ?? throw new Exception('Please select student'); // Decrypt student number
        // dd($EmpNo);
                //     // Find the varsity student and school year using a join query
        $coach = DB::connection(strtolower($campus))
            ->table('var_coaches')
            ->join('var_event', 'var_coaches.CoachEvent', '=', 'var_event.id')
            ->join('db_hrmis.employee as employee', 'var_coaches.EmpNo', '=', 'employee.id')
            ->where('var_coaches.EmpNo', $EmpNo)
            ->select('var_coaches.*',
                'employee.FirstName as FirstName',
                'employee.MiddleName as MiddleName',
                'employee.LastName as LastName',
                )
            ->first() ?? throw new Exception('Varsity student not found.');

        //     // Get the total participants allowed for the event
        $event = Event::where('id', $coach->CoachEvent)
        ->select('totalAtlhetes','event')
        ->first();

        if (!$event) {
            throw new Exception('Event not found.');
        }

        // // Count the number of existing varsity students for the event
        $existingVarsityCount = CoachVarsity::where('Event', $coach->CoachEvent)->count();

        // // Check if adding the new varsity student would exceed the total participants
        if ($existingVarsityCount >= $event->totalAtlhetes) {
            throw new Exception($event->event . ' event has reached the maximum number of participants.');
        }

        // // Check if the varsity student already exists for the current school year
        $exists = CoachVarsity::where([
            'CoachID' => $coach->EmpNo,
            'SchoolYear' => date('Y'),
        ])->exists();

        if ($exists) {
            throw new Exception("Varsity already exists for this school year.");
        }

        // // Save the varsity to the var_list table
        CoachVarsity::create([
            'CoachID' => $coach->EmpNo,
            'SchoolYear' => date('Y'),
            'Event' => $coach->CoachEvent,
        ]);
        // }

        return response()->json(['success' => true, 'message' => 'Selected coach successfully stored.']);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}
}
