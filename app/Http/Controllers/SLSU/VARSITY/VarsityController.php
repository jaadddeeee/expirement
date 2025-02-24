<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    
use App\Models\VARSITY\Event;
use App\Models\VARSITY\Varsity;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Crypt;
use GENERAL;
use Exception;


class VarsityController extends Controller
{
    public function index(Request $request)
    {
        $query = Varsity::whereNull('deleted_at')
            ->orderBy('LastName', 'asc');
    
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('LastName', 'LIKE', "%{$request->search}%")
                  ->orWhere('FirstName', 'LIKE', "%{$request->search}%")
                  ->orWhere('MiddleName', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->has('filterSchoolYear') && $request->filterSchoolYear != '0') {
            $query->where('SchoolYear', $request->filterSchoolYear);
        }

        if ($request->has('filterSemester') && $request->filterSemester != '0') {
            $query->where('Semester', $request->filterSemester);
        }
    
        if ($request->has('filterCampus') && $request->filterCampus != '0') {
            $query->where('Campus', $request->filterCampus);
        }
    
        $varsity = $query->paginate(10);
    
        if ($request->ajax()) {
            return response()->json([
                'html' => view('_partials.varsity-table', ['varsities' => $varsity])->render()
            ]);
        }
    
        $pageTitle = "Manage Varsity Student";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        return view('slsu.varsity.VAR_student.student', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'varsities' => $varsity
        ]);
    }

    public function studlist(Request $request){
        try {
            $campus = session('campus');

            if (auth()->user()->AllowSuper == 1){
                if (empty($request->id)){
                    throw new Exception('Select campus');
                }

                $campus = $request->id;
            }

            //1 - SG
            //2 - MCC
            //3 = TO
            //4 - BN
            //5 - SJ    
            //6 - HN

            $students = DB::connection(strtolower($campus))
                ->table('students')
                ->orderBy('FirstName')
                ->orderBy('LastName')
                ->get();
    
            if (count($students) <= 0) {
                throw new Exception('No student found.');
            }

            $students = $students->map(function ($student) {
                return [
                    'id' => Crypt::encryptString($student->StudentNo), 
                    'LastName' => $student->LastName,
                    'FirstName' => $student->FirstName,   
                    'MiddleName' => $student->MiddleName,  
                ];
            });

            return response()->json($students);
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
            $stud = $request->Stud;
            $ev = $request->Event;
            $sy = $request->SchoolYear;
            $sem = $request->Semester;

            // dd($camCode);

            // Validate inputs
            if (empty($campus))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid Campus")]);
            if (empty($stud))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select student")]);
            if (empty($ev))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select event")]);
            if (empty($sy))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select school year")]);
            if (empty($sem))
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select semester")]);

            $eventId = Crypt::decryptString($ev);

            $event = Event::find($eventId);


            // Split using comma separator
            $studentParts = explode(",", $stud);

            // Trim spaces from each part
            $lastName = trim($studentParts[0]); 
            $firstName = trim($studentParts[1]); 
            $middleName = isset($studentParts[2]) ? trim($studentParts[2]) : null; 

            // Format output with comma if middle name exists
            $formattedName = "{$lastName}, {$firstName}";
            if (!empty($middleName)) {
                $formattedName .= ", {$middleName}";
            }


            $campusCode = [
                "SG" => 1,
                "MCC" => 2,
                "TO" => 3,
                "BN" => 4,
                "SJ" => 5,
                "HN" => 6
            ];

            $camCode = $campusCode[$campus];

            // Check for duplicate entry
            $existingStud = Varsity::where("FirstName", $firstName)
                ->where("MiddleName", $middleName)
                ->where("LastName", $lastName)
                ->first();

            $studentExists = DB::connection(strtolower($campus))
                ->table('students')
                ->where("FirstName", $firstName)
                ->where("MiddleName", $middleName)
                ->where("LastName", $lastName)
                ->first();

            if (!$studentExists) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("This student is not exist in this campus")]);

            if ($existingStud) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Duplicate entry detected for this Student")]);


            // Retrieve Student Gender
            $gender = $studentExists->Sex ?? null;

            // Retrieve Event Category
            $eventCategory = strtolower($event->event);
            $eventGender = null;

            // Determine the event's gender category
            if (strpos($eventCategory, ' men') !== false) { 
                $eventGender = 'M'; 
            } elseif (strpos($eventCategory, ' women') !== false) { 
                $eventGender = 'F'; 
            }

            // Validate Gender and Event Category
            if (($eventGender === 'M' && $gender !== 'M') || 
                ($eventGender === 'F' && $gender !== 'F')) 
                return response()->json([
                    'Error' => 1, 
                    "Message" => \GENERAL::Error("Student's gender does not match the event category.")
                ]);
            

            // Save coach details
            $data = [
                'FirstName' => $firstName,
                'MiddleName' => $middleName,
                'LastName' => $lastName,
                'VarsityEvent' => $eventId,
                'SchoolYear'=> $sy,
                'Semester' => $sem,
                'Campus' => $camCode
            ];

            // dd($data);

            $insertedStudent = Varsity::create($data);

            if ($insertedStudent) {
                return response()->json(['Error' => 0, "Message" => \GENERAL::success("Varsity student successfully added.")]);
            }

            return response()->json(['Error' => 1, "Message" => "Failed to insert student."]);

        } catch (\Exception $e) {
            return response()->json(['Error' => 1, "Message" => $e->getMessage()], 400);
        }
    }

     public function edit(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id); 
    
            // Ensure the correct relationship
            $editVarsity = Varsity::with('event')->findOrFail($id);
    
            return response()->json([
                'id' => $editVarsity->id,
                'campus' => $editVarsity->Campus,
                'stud' => trim($editVarsity->LastName . ', ' . $editVarsity->FirstName . ($editVarsity->MiddleName ? ', ' . $editVarsity->MiddleName : '')),
                'event' => $editVarsity->event ? $editVarsity->event->event : null, // Return event ID instead of name
                'sy' => $editVarsity->SchoolYear,
                'sem' => $editVarsity->Semester,
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
            $var = Varsity::find($decryptedId);

            $campus = $request->updateCampus;
            $sy = $request->updateSY;
            $sem = $request->updateSem;
            $ev = $request->updateEvent;

            // Validate inputs
            if (!$sy) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select school year")]);
            if (!$sem) 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select semester")]);
            if (empty($ev) || $ev == "0") 
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Please select event")]);
            
            $eventId = Crypt::decryptString($ev);

            $event = Event::find($eventId);

            $campusCode = [
                1 => "SG",
                2 => "MCC",
                3 => "TO",
                4 => "BN",
                5 => "SJ",
                6 => "HN"
            ];

            $camCode = $campusCode[$var->Campus];

            $studentExists = DB::connection(strtolower($camCode))
            ->table('students')
            ->first();

            // Retrieve Student Gender
            $gender = $studentExists->Sex ?? null;

            // Retrieve Event Category
            $eventCategory = strtolower($event->event);
            $eventGender = null;

            // Determine the event's gender category
            if (strpos($eventCategory, ' men') !== false) { 
                $eventGender = 'M'; 
            } elseif (strpos($eventCategory, ' women') !== false) { 
                $eventGender = 'F'; 
            }

            // Validate Gender and Event Category
            if (($eventGender === 'M' && $gender !== 'M') || 
                ($eventGender === 'F' && $gender !== 'F')) {
                return response()->json([
                    'Error' => 1, 
                    "Message" => \GENERAL::Error("Student's gender does not match the event category.")
                ]);
            }

            // Check if there are no changes
            if ($var->SchoolYear == $sy && $var->Semester == $sem && $var->VarsityEvent == $eventId) {
                return response()->json(['Error' => 1, "Message" => \GENERAL::Error("Invalid, No changes detected.")]);
            }

            // Update coach details
            $var->SchoolYear = $sy;
            $var->Semester = $sem;
            $var->VarsityEvent = $eventId;
            $updated = $var->save(); // Save the changes
  
            if ($updated) {
                return response()->json(['Error' => 0, "Message" => \GENERAL::Success("Varsity successfully updated")]);
            }
        } catch (DecryptException $e) {
            return response()->json(['Error' => 1, "Message" => "Invalid encrypted ID."], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json(['Error' => 1, "Message" => "Coach not found."], 404);
        } catch (Exception $e) {
            return response()->json(['Error' => 1, "Message" => $e->getMessage()], 500);
        }
    }

    public function deleteVar(Request $request){
        try{
          $id = Crypt::decryptstring($request->id);
  
          $one = Varsity::find($id);
          if (empty($one))
            throw new Exception("Varsity not found.");
  
          $data = [
            'deleted_at' => now()
          ];
          $del = Varsity::where("id", $id)
            ->update($data);
          if (!$del)
          throw new Exception("Unable to delete varsity.");
        }catch(Exception $e){
          return response()->json(['errors' => $e->getMessage()], 400);
        }catch(DecryptException $e){
          return response()->json(['errors' => $e->getMessage()], 400);
        }
      }
}
