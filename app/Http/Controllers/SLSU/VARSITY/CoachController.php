<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VARSITY\Coach;
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

    $query = DB::connection(strtolower($campus))
        ->table('var_coaches')
        ->leftJoin('var_event', 'var_coaches.CoachEvent', '=', 'var_event.id') 
        ->whereNull('var_coaches.deleted_at')
        ->orderBy('var_coaches.FirstName')
        ->orderBy('var_coaches.LastName', 'asc')
        ->select('var_coaches.*', 'var_event.event as event_name');

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
    
    $coach = $query->paginate(10);

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
        'Campus' => $campus
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

        $employeeParts = array_map('trim', explode(",", $request->Emp ?? throw new Exception('Please select coach'))); // Extract employee name
        [$lastName, $firstName, $middleName] = array_pad($employeeParts, 3, null);
        $event = Crypt::decryptString($request->Event ?: throw new Exception("Please select event"));
        $ct = $request->coachType ?? throw new Exception("Please select coach type");

        $campusID = GENERAL::Campuses()[$campus]['ID'];

        // Check for duplicate entry
        $C = DB::connection(strtolower($campus))
            ->table('var_coaches')
            ->where("FirstName", $firstName)
            ->where("MiddleName", $middleName)
            ->where("LastName", $lastName)
            ->exists() && throw new Exception('Duplicate entry detected for this coach');
        
        DB::connection('hrmis')
            ->table('employee')
            ->whereNull('deleted_at')
            ->where('campus',  $campusID)
            ->where('FirstName', $firstName)
            ->where('MiddleName', $middleName)
            ->where('LastName', $lastName)
            ->first() ?? throw new Exception('This employee is not exist in this campus');

        // Check if the event already has a main coach in the same campus
        $mainCoach = Coach::on(strtolower($campus))
            ->where('CoachEvent', $event)
            ->where('CoachType', $ct)
            ->whereNull('deleted_at')
            ->exists();

            ($mainCoach && $ct == 1) && throw new Exception('This event already has a main coach');
        
        Coach::on(strtolower($campus))
            ->create([
                'FirstName' => $firstName,
                'MiddleName' => $middleName,
                'LastName' => $lastName,
                'CoachType' => $ct,
                'CoachEvent' => $event,
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
        ->where('var_coaches.id', $id)
        ->select('var_coaches.*', 'var_event.event as event')
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
            ->update(['deleted_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Varsity successfully deleted.']);
    }catch(Exception $e){
        return response()->json(['errors' => General::Error($e->getMessage())], 400);
    }catch(DecryptException $e){
        return response()->json(['errors' => General::Error('Invalid encrypted ID.')], 400);
    }
}
}
