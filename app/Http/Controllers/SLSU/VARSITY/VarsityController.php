<?php

namespace App\Http\Controllers\SLSU\VARSITY;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VARSITY\Varsity;
use App\Models\VARSITY\Event;
use App\Models\VARSITY\ListVarsity;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Exception;


class VarsityController extends Controller
{
    public function index(Request $request)
    {

        $campus = auth()->user()->AllowSuper == 1 ? ($request->filterCampus ?? "SG") : session('campus');

        $query = DB::connection(strtolower($campus))
            ->table('var_varsity')
            ->leftjoin('var_event', 'var_varsity.VarsityEvent', '=', 'var_event.id')
            ->leftjoin('students', 'var_varsity.StudentNo', '=', 'students.StudentNo')
            ->whereNull('var_varsity.deleted_at')
            ->orderBy('students.LastName', 'asc')
            ->select(
                'var_varsity.SchoolYear',
                'var_varsity.Semester',
                'var_varsity.id',
                'students.FirstName as FirstName',
                'students.MiddleName as MiddleName',
                'students.LastName as LastName',
                'var_event.event as event_name'
            );

        if ($request->has('filterEvent') && $request->filterEvent != '0') {
            $query->where('VarsityEvent', $request->filterEvent);
        }

        if ($request->has('filterSchoolYear') && $request->filterSchoolYear != '0') {
            $query->where('SchoolYear', $request->filterSchoolYear);
        }

        if ($request->has('filterSemester') && $request->filterSemester != '0') {
            $query->where('Semester', $request->filterSemester);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('students.LastName', 'LIKE', "%{$request->search}%")
                    ->orWhere('students.FirstName', 'LIKE', "%{$request->search}%")
                    ->orWhere('students.MiddleName', 'LIKE', "%{$request->search}%");
            });
        }

        $varsity = $query->paginate(10);

        $events = DB::connection(strtolower($campus))
            ->table('var_event')
            ->select('id', 'event')
            ->orderby('event')
            ->get() ?? throw new Exception('No events found');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('_partials.var_student-table', ['varsities' => $varsity])->render()
            ]);
        }

        $pageTitle = "Manage Varsity Student";
        $headerAction = '<a href="javascript:history.back()" class="btn btn-sm btn-primary" role="button">Back</a>';
        return view('slsu.varsity.VAR_student.student', [
            'pageTitle' => $pageTitle,
            'headerAction' => $headerAction,
            'varsities' => $varsity,
            'Campus' => $campus,
            'Events' => $events
        ]);
    }

    public function studlist(Request $request)
    {
        try {

            $campus = auth()->user()->AllowSuper == 1 ? ($request->id ?? throw new Exception('Select Campus')) : session('campus');

            $students = DB::connection(strtolower($campus))
                ->table('students')
                ->orderBy('FirstName')
                ->orderBy('LastName')
                ->get() ?? throw new Exception('No student found.');

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

    public function saveSelectedVarsities(Request $request)
    {
        try {
            $campus = auth()->user()->AllowSuper == 1 ? ($request->campus ?? throw new Exception('Select Campus')) : session('campus');

            // Retrieve the selected varsity IDs from the request
            $selectedVarsities = $request->input('selectedVarsities', []);

            if (empty($selectedVarsities)) {
                throw new Exception('No varsity selected.');
            }
            //  dd($selectedVarsities);
            foreach ($selectedVarsities as $varsityId) {
                // Find the varsity student and school year using a join query
                $varsity = DB::connection(strtolower($campus))
                    ->table('var_varsity')
                    ->join('var_event', 'var_varsity.VarsityEvent', '=', 'var_event.id')
                    ->join('students', 'var_varsity.StudentNo', '=', 'students.StudentNo')
                    ->where('var_varsity.id', $varsityId)
                    ->select('var_varsity.*',
                        'students.FirstName as FirstName',
                        'students.MiddleName as MiddleName',
                        'students.LastName as LastName',
                        )
                    ->first() ?? throw new Exception('Varsity student not found.');

                // Get the total participants allowed for the event
                $event = Event::where('id', $varsity->VarsityEvent)
                ->select('totalAtlhetes','event')
                ->first();

            if (!$event) {
                throw new Exception('Event not found.');
            }

            // Count the number of existing varsity students for the event
            $existingVarsityCount = ListVarsity::where('Event', $varsity->VarsityEvent)->count();

            // Check if adding the new varsity student would exceed the total participants
            if ($existingVarsityCount >= $event->totalAtlhetes) {
                throw new Exception($event->event . ' event has reached the maximum number of participants.');
            }

            // Check if the varsity student already exists for the current school year
            $exists = ListVarsity::where([
                'StudentNo' => $varsity->StudentNo,
                'SchoolYear' => date('Y'),
                ])
                ->select('id', 'deleted_at')
                ->first();
                // dd($exists);
                if ($exists) {
                    if ($exists->deleted_at !== null) {
                        // Restore the soft-deleted record manually
                        ListVarsity::where('id', $exists->id)
                            ->update(['deleted_at' => null]);
    
                        return response()->json(['success' => true, 'message' => 'Varsity restored successfully.']);
                    } else {
                        throw new Exception("Varsity already exists for this school year.");
                    }
                }

                // Save the varsity to the var_list table
                ListVarsity::create([
                    'StudentNo' => $varsity->StudentNo,
                    'SchoolYear' => date('Y'),
                    'Event' => $varsity->VarsityEvent,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Selected varsity students successfully stored.']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function save(Request $request)
    {
        try {
            $campus = auth()->user()->AllowSuper == 1 ? ($request->Campus ?? throw new Exception('Select campus')) : session('campus');

            $studentNo = Crypt::decryptString($request->StudentNo) ?? throw new Exception('Please select student'); // Decrypt student number
            $eventId = Crypt::decryptString($request->Event ?: throw new Exception("Please select event"));
            $sy = $request->SchoolYear ?? throw new Exception("Please select school year");
            $sem = $request->Semester ?? throw new Exception("Please select semester");

            $event = DB::connection(strtolower($campus))
                ->table('var_event')
                ->where('id', $eventId)
                ->first() ?? throw new Exception("Event not found.");

            // Validate student existence
            $student = DB::connection(strtolower($campus))
                ->table('students')
                ->where([
                    'StudentNo' => $studentNo,
                ])
                ->first() ?? throw new Exception("This student does not exist in this campus");

            // Validate duplicate entry
            DB::connection(strtolower($campus))
                ->table('var_varsity')
                ->where([
                    'StudentNo' => $studentNo,
                ])
                ->exists() && throw new Exception("Duplicate entry detected for this Student");

            $gender = $student->Sex;
            $eventGender = str_contains(strtolower($event->event), ' men') ? 'M' : (str_contains(strtolower($event->event), ' women') ? 'F' : null);

            if ($eventGender && $eventGender !== $gender) {
                throw new Exception("Student's gender does not match the event category.");
            }

            // Insert into var_varsity

            $inserted = Varsity::on(strtolower($campus))
                ->create([
                    'StudentNo' => $studentNo,
                    'VarsityEvent' => $eventId,
                    'SchoolYear' => $sy,
                    'Semester' => $sem,
                ]);

            return response()->json(['Error' => 0, "Message" => "Varsity student successfully added."]);
        } catch (DecryptException) {
            return response()->json(['Error' => \GENERAL::Error("Invalid encrypted ID.")], 400);
        } catch (Exception $e) {
            return response()->json(['Error' => \GENERAL::Error($e->getMessage())], 400);
        }
    }

    public function edit(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id);
            $campus = auth()->user()->AllowSuper == 1 ? ($request->campus ?: throw new Exception('Select Campus')) : session('campus');

            $editVarsity = DB::connection(strtolower($campus))
                ->table('var_varsity')
                ->leftjoin('var_event', 'var_varsity.VarsityEvent', '=', 'var_event.id')
                ->leftjoin('students', 'var_varsity.StudentNo', '=', 'students.StudentNo')
                ->where('var_varsity.id', $id)
                ->select('var_varsity.SchoolYear',
                    'var_varsity.Semester',
                    'var_varsity.id',
                    'students.FirstName as FirstName',
                    'students.MiddleName as MiddleName',
                    'students.LastName as LastName',
                    'var_event.event as event'
                )
                ->first() ?? throw new Exception('Record not found.');

            return response()->json([
                'id' => $editVarsity->id,
                'campus' => $campus,
                'stud' => trim($editVarsity->LastName . ', ' . $editVarsity->FirstName . ($editVarsity->MiddleName ? ', ' . $editVarsity->MiddleName : '')),
                'event' => $editVarsity->event,
                'sy' => $editVarsity->SchoolYear,
                'sem' => $editVarsity->Semester,
            ]);
        } catch (DecryptException $e) {
            return response()->json(['errors' => \GENERAL::Error('Invalid request.')], 400);
        } catch (Exception $e) {
            return response()->json(['Error' =>  \GENERAL::Error($e->getMessage())], 400);
        }
    }

    public function update(Request $request)
    {
        try {
            $decryptedId = Crypt::decryptString($request->hiddentID);
            $campus = auth()->user()->AllowSuper ? ($request->id ?: throw new Exception('Select campus')) : session('campus');

            // Get the varsity base on campus
            $var = DB::connection(strtolower($campus))
                ->table('var_varsity')
                ->join('var_event', 'var_varsity.VarsityEvent', '=', 'var_event.id')
                ->join('students', 'var_varsity.StudentNo', '=', 'students.StudentNo')
                ->where('var_varsity.id', $decryptedId)
                ->select('var_varsity.SchoolYear',
                    'var_varsity.Semester',
                    'var_varsity.VarsityEvent',
                    'var_varsity.id',
                    'students.FirstName as FirstName',
                    'students.MiddleName as MiddleName',
                    'students.LastName as LastName',
                )
                ->first() ?? throw new Exception("Varsity record not found.");
            //Validation for each info
            $sy = $request->updateSY ?? throw new Exception("Please select school year");
            $sem = $request->updateSem ?? throw new Exception("Please select semester");
            $eventId = Crypt::decryptString($request->updateEvent ?: throw new Exception("Please select event"));

            //Get event base on campus
            $event = DB::connection(strtolower($campus))
                ->table('var_event')
                ->where('id', $eventId)
                ->first() ?? throw new Exception("Event not found.");

            $student = DB::connection(strtolower($campus))
                ->table('students')
                ->where([
                    'FirstName' => $var->FirstName,
                    'MiddleName' => $var->MiddleName,
                    'LastName' => $var->LastName,
                ])
                ->first() ?? throw new Exception("Student does not exist in this campus");

            $gender = $student->Sex;
            $eventGender = str_contains(strtolower($event->event), ' men') ? 'M' : (str_contains(strtolower($event->event), ' women') ? 'F' : null);

            if ($eventGender && $eventGender !== $gender) {
                throw new Exception("Student's gender does not match the event category.");
            }

            if ($var->SchoolYear == $sy && $var->Semester == $sem && $var->VarsityEvent == $eventId) {
                throw new Exception("Invalid, No changes detected.");
            }
            
            Varsity::on(strtolower($campus))
                ->where('id', $decryptedId)
                ->update([
                    'SchoolYear' => $sy,
                    'Semester' => $sem,
                    'VarsityEvent' => $eventId,
                ]);

            return response()->json(['Error' => 0, "Message" => 'Varsity successfully updated.']);
        } catch (DecryptException) {
            return response()->json(['Error' => \GENERAL::Error("Invalid encrypted ID.")], 400);
        } catch (Exception $e) {
            return response()->json(['Error' => \GENERAL::Error($e->getMessage())], 400);
        }
    }

    public function deleteVar(Request $request)
    {
        try {
            $campus = auth()->user()->AllowSuper == 1 ? ($request->campus ?? throw new Exception('Select Campus')) : session('campus');

            $id = Crypt::decryptString($request->id);

            // Find the Varsity record, delete record
            $varsity = Varsity::on(strtolower($campus))
                ->where('id', $id)
                ->firstOrFail() ?? throw new Exception('Varsity record not found.');

            $varsity->delete();

            return response()->json(['success' => true, 'message' => 'Varsity successfully deleted.']);
        } catch (DecryptException) {
            return response()->json(['error' => General::Error('Invalid encrypted ID.')], 400);
        } catch (Exception $e) {
            return response()->json(['error' => General::Error($e->getMessage())], 400);
        }
    }
}
