<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VARSITY\Coach;
use Illuminate\Support\Facades\DB;
use App\Models\VARSITY\Event;
use Illuminate\Contracts\Encryption\DecryptException;
use Crypt;
use GENERAL;
use Exception;
    

class CoachController extends Controller
{
    public function index(){
        $coaches = Coach::with('event')->whereNull('deleted_at')->paginate(10);

        $pageTitle = "Manage Coaches";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        return view('slsu.varsity.coach',[
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'coaches' => $coaches
            ]);
    }   

    public function search(Request $request)
    {
        $str = $request->search;
    
        $coaches = Coach::with('event') // Ensure event relationship is loaded
            ->whereNull('deleted_at')
            ->where(function ($query) use ($str) {
                $query->where("LastName", "LIKE", "%{$str}%")
                      ->orWhere("FirstName", "LIKE", "%{$str}%")
                      ->orWhere("MiddleName", "LIKE", "%{$str}%");
            })
            ->paginate(10); // Ensure pagination works
    
        return response()->json([
            'html' => view('_partials.coach-table', compact('coaches'))->render()
        ]);
    } 

    public function emplist(Request $request){
        try {
            $campus = $request->id;

            //1 - SG
            //2 - MCC
            //3 = TO
            //4 - BN
            //5 - SJ
            //6 - HN

            $campus = GENERAL::Campuses()[$campus]['ID'];
            $employees = DB::connection('hrmis')
                ->table('employee')
                ->whereNull('deleted_at')
                ->where('campus',  $campus)
                ->orderBy('FirstName')
                ->orderBy('LastName')
                ->get();
    
            if (count($employees) <= 0) {
                throw new Exception('No employees found.');
            }

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

    public function eventlist(){
        try {
            // Fetch all events
            $events = Event::select('id', 'event')
                ->orderBy('event')
                ->get();
    
            if ($events->isEmpty()) {
                return response()->json(['message' => 'No events found.'], 200);
            }
    
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
            $campus = $request->Campus; 
            $coach = $request->Emp;
            $ct = $request->coachType;
            $ev = $request->Event;

            // Validate inputs
            if (empty($campus))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid Campus")]);
            if (empty($coach))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select a coach")]);
            if (empty($ct))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select a coach type")]);
            if (empty($ev))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select an event")]);

            $event = Crypt::decryptString($ev);

            $campus = GENERAL::Campuses()[$campus]['ID'];

            $coachParts = explode(",", $coach);

            // Validate if we have at least 2 parts (Last Name and First Name)
            if (count($coachParts) < 2) {
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid student format.")]);
            }

            // Trim spaces from each part
            $lastName = trim($coachParts[0]);  
            $firstName = trim($coachParts[1]);
            $middleName = isset($coachParts[2]) ? trim($coachParts[2]) : null;

            // Format output with comma if middle name exists
            $formattedName = "{$lastName}, {$firstName}";
            if (!empty($middleName)) {
                $formattedName .= ", {$middleName}";
            }
            

            // Check for duplicate entry
            $existingCoach = Coach::where("FirstName", $firstName)
                ->where("MiddleName", $middleName)
                ->where("LastName", $lastName)
                ->first();

            $employees = DB::connection('hrmis')
                ->table('employee')
                ->whereNull('deleted_at')
                ->where('campus',  $campus) 
                ->where('FirstName', $firstName)
                ->where('MiddleName', $middleName)
                ->where('LastName', $lastName)
                ->first();

            if ($existingCoach && $existingCoach->deleted_at !== null)
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Coach does not exist")]);

            if (!$employees) 
            return response()->json(['Error' => 1, "Message" => \GENERAL::Error("This employee is not exist in this campus")]);

            if ($existingCoach) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Duplicate entry detected for this coach")]);

            // Save coach details
            $data = [
                'FirstName' => $firstName,
                'MiddleName' => $middleName,
                'LastName' => $lastName,
                'CoachType' => $ct,
                'CoachEvent' => $event,
                'Campus' => $campus
            ];

            $insertedCoach = Coach::create($data);

            if ($insertedCoach) {
                return response()->json(['Error' => 0, "Message" => \GENERAL::success("Coach successfully added.")]);
            }

            return response()->json(['Error' => 1, "Message" => "Failed to insert coach."]);

        } catch (\Exception $e) {
            return response()->json(['Error' => 1, "Message" => $e->getMessage()], 400);
        }
    }
    
    public function edit(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id); 
    
            // Ensure the correct relationship
            $editCoach = Coach::with('event')->findOrFail($id);

        //     // Map campus IDs to campus names
        // $campusNames = [
        //     1 => 'Main Campus',
        //     2 => 'Maasin City Campus',
        //     3 => 'Tomas Oppus Campus',
        //     4 => 'Bontoc Campus',
        //     5 => 'San Juan Campus',
        //     6 => 'Hinunangan Campus'
        // ];

        // // Get campus name based on ID, default to 'Unknown Campus' if not found
        // $campusName = $campusNames[$editCoach->Campus] ?? 'Unknown Campus';
    
            return response()->json([
                'id' => $editCoach->id,
                'campus' => $editCoach->Campus,
                'emp' => trim($editCoach->LastName . ', ' . $editCoach->FirstName . ($editCoach->MiddleName ? ', ' . $editCoach->MiddleName : '')),
                'coachType' => $editCoach->CoachType,
                'event' => $editCoach->event ? $editCoach->event->event : null 
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
            $ch = Coach::find($decryptedId);

            $ct = $request->updateCT;
            $ev = $request->updateEvent;

            // Validate inputs
            if (!$ct) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select a coach type")]);
            if (empty($ev) || $ev == "0") 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select event")]);

            $event = Crypt::decryptString($ev);

            if ($ch->CoachType == $ct && $ch->CoachEvent == $event) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid, No changes detected.")]);
            
            // Update coach details
            $ch->CoachType = $ct;
            $ch->CoachEvent = $event;
            $updated = $ch->save(); 
  
            if ($updated) {
                return response()->json(['Error' => 0, "Message" => \GENERAL::Success("Coach successfully updated")]);
            }
        } catch (DecryptException $e) {
            return response()->json(['Error' => 1, "Message" => "Invalid encrypted ID."], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['Error' => 1, "Message" => "Coach not found."], 404);
        } catch (Exception $e) {
            return response()->json(['Error' => 1, "Message" => $e->getMessage()], 500);
        }
    }
    
    public function deleteCoach(Request $request){
        try{
          $id = Crypt::decryptstring($request->id);
  
          $one = Coach::find($id);
          if (empty($one))
            throw new Exception("Coach not found.");
  
          $data = [
            'deleted_at' => now()
          ];
          $del = Coach::where("id", $id)
            ->update($data);
          if (!$del)
          throw new Exception("Unable to delete coach.");
        }catch(Exception $e){
          return response()->json(['errors' => $e->getMessage()], 400);
        }catch(DecryptException $e){
          return response()->json(['errors' => $e->getMessage()], 400);
        }
      }
}
