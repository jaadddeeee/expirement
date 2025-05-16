<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scholarship\Scholarship;
use App\Models\Scholarship\ScholarshipRequirements;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Course;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


use GENERAL;


class ScholarshipController extends Controller
{

    // index scholarship page
    public function index(Request $request)
    {
        try {
            $pageTitle = "Scholarships";
            $headerAction = '<a href="' . url()->previous() . '" class="btn btn-sm btn-primary" role="button">Back</a>';

            // Using with() relationship instead of join for cleaner code
            $query = Scholarship::whereNull('deleted_at')
                ->with(['applications' => function ($query) {
                    $query->select('id', 'scholarship_id', 'status', 'start_date', 'deadline_date', 'slots');
                }])
                ->when($request->filled('searchScholarship'), function ($q) use ($request) {
                    $q->where('sch_name', 'LIKE', "%{$request->searchScholarship}%");
                })
                ->when($request->filled('scholarshipType'), function ($q) use ($request) {
                    $q->where('sch_type', $request->scholarshipType);
                })
                ->when($request->filled('externalType'), function ($q) use ($request) {
                    $q->where('ext_type', $request->externalType);
                })
                ->orderBy('created_at', 'desc');

            $entriesPerPage = $request->input('entriesPerPage', 10);
            $scholarships = $query->paginate($entriesPerPage);

            $courses = Course::with('majors')->get();
            $courseTitles = $courses->pluck('course_title');

            if ($request->ajax()) {
                return response()->json([
                    'scholarshipTable' => view('slsu.scholarships._partials._scholarships-table', compact('scholarships'))->render()
                ]);
            }

            return view('slsu.scholarships.index', compact('pageTitle', 'headerAction', 'scholarships', 'entriesPerPage', 'courses', 'courseTitles'));
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'An error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

    // store scholarship
    public function store(Request $request)
    {
        try {
            $ScholarshipName = trim($request->ScholarshipName);
            $ScholarshipAcronym = trim($request->SchAcronym);
            $ScholarshipType = $request->ScholarshipType;
            $ExternalSchType = $request->ExternalScholarshipType;
            $ScholarshipProvider = $request->SchProvider;

            if (empty($ScholarshipName)) {
                throw new \Exception("Empty Scholarship Name");
            }

            if (empty($ScholarshipAcronym)) {
                throw new \Exception("Empty Scholarship Acronym");
            }

            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                throw new \Exception("Please select scholarship type");
            }

            if ($ScholarshipType == 1) {
                $ExternalSchType = 0;

                $campuses = GENERAL::Campuses();
                $campusCode = strtoupper(session('campus'));
                $campusName = isset($campuses[$campusCode]) ? $campuses[$campusCode]['Campus'] : 'Unknown Campus';

                $ScholarshipProvider = 'SLSU - ' . $campusName;
            } elseif (empty($ExternalSchType)) {
                throw new \Exception("Please select external type");
            }

            if ($ScholarshipType != 1 && empty($ScholarshipProvider)) {
                throw new \Exception("Please provide a scholarship provider");
            }

            // check if scholarship already exists
            $existing = Scholarship::where('sch_name', $ScholarshipName)->first();
            if ($existing) {
                throw new \Exception("Scholarship already exists.");
            }

            // Create Scholarship
            Scholarship::create([
                'sch_name' => $ScholarshipName,
                'sch_acronym' => $ScholarshipAcronym,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType,
                'sch_provider' => $ScholarshipProvider,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'Error' => 0,
                'Message' => "$ScholarshipName created successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => $e->getMessage()
            ]);
        }
    }

    // edit scholarship
    public function edit(Request $request)
    {
        try {
            $id = $request->id;
            $dId = Crypt::decryptString($id);

            $scholarship = Scholarship::findOrFail($dId);

            return response()->json([
                'Error' => 0,
                'Scholarship' => [
                    'id' => $scholarship->id,
                    'name' => $scholarship->sch_name,
                    'acronym' => $scholarship->sch_acronym,
                    'type' => $scholarship->sch_type,
                    'externalType' => $scholarship->ext_type,
                    'provider' => $scholarship->sch_provider,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'Unable to fetch scholarship details. Please try again later.' . $e->getMessage()
            ], 400);
        }
    }

    // update scholarship
    public function update(Request $request)
    {
        try {
            $id = $request->id;
            $scholarship = Scholarship::findOrFail($id);

            $ScholarshipName = trim($request->ScholarshipName);
            $ScholarshipAcronym = trim($request->SchAcronym);
            $ScholarshipType = $request->ScholarshipType;
            $ExternalSchType = $request->ExternalScholarshipType;
            $ScholarshipProvider = $request->SchProvider;

            if (empty($ScholarshipName)) {
                return response()->json(['Error' => 1, 'Message' => "Scholarship Name is required."]);
            }

            if (empty($ScholarshipAcronym)) {
                return response()->json(['Error' => 1, 'Message' => "Scholarship Acronym is required."]);
            }

            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, 'Message' => "Please select a Scholarship Type."]);
            }

            if ($ScholarshipType == 1) {
                $ExternalSchType = 0;

                $campuses = GENERAL::Campuses();
                $campusCode = strtoupper(session('campus'));
                $campusName = isset($campuses[$campusCode]) ? $campuses[$campusCode]['Campus'] : 'Unknown Campus';

                $ScholarshipProvider = 'SLSU - ' . $campusName;
            } elseif (empty($ExternalSchType)) {
                return response()->json(['Error' => 1, 'Message' => "Please select an External Type."]);
            }

            if ($ScholarshipType != 1 && empty($ScholarshipProvider)) {
                return response()->json(['Error' => 1, 'Message' => "Please provide a Scholarship Provider."]);
            }

            $hasChanges = false;

            if (
                $scholarship->sch_name !== trim($ScholarshipName) ||
                $scholarship->sch_acronym !== trim($ScholarshipAcronym) ||
                $scholarship->sch_type != $ScholarshipType || // <- note loose comparison
                $scholarship->ext_type != $ExternalSchType || // <- note loose comparison
                $scholarship->sch_provider !== trim($ScholarshipProvider)
            ) {
                $hasChanges = true;
            }

            if (!$hasChanges) {
                return response()->json(['Error' => 1, 'Message' => "No updates were made because no changes were detected."]);
            }

            $existing = Scholarship::where('sch_name', $ScholarshipName)
                ->where('sch_type', $ScholarshipType)
                ->where('ext_type', $ExternalSchType)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json(['Error' => 1, 'Message' => "A scholarship with the same name, type, and external type already exists."]);
            }

            $scholarship->update([
                'sch_name' => $ScholarshipName,
                'sch_acronym' => $ScholarshipAcronym,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType,
                'sch_provider' => $ScholarshipProvider,
                'updated_at' => now()
            ]);

            return response()->json(['Error' => 0, 'Message' => "Scholarship updated successfully."]);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => "An error occurred: " . $e->getMessage()], 400);
        }
    }

    // delete scholarship
    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $dId = Crypt::decryptString($id);

            $scholarship = Scholarship::findOrFail($dId);
            $scholarship->delete();

            return response()->json(['Error' => 0, 'Message' => 'Scholarship deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'Error deleting scholarship: ' . $e->getMessage()], 400);
        }
    }

    // store requirements for scholarship application
    public function storeRequirements(Request $request)
    {
        try {

            $id = $request->id;
            $scholarshipId = Crypt::decryptString($id);
            $requirements = $request->requirements;
            $quantities = $request->quantities;

            if (!$scholarshipId || empty($requirements)) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Please input at least one requirement.',
                ]);
            }

            foreach ($quantities as $index => $quantity) {
                if (empty($quantity) || $quantity <= 0) {
                    return response()->json([
                        'Error' => 1,
                        'Message' => 'Please provide a valid quantity for all requirements.',
                    ]);
                }
            }

            $hasValidRequirement = false;
            foreach ($requirements as $r) {
                if (trim($r) !== '') {
                    $hasValidRequirement = true;
                    break;
                }
            }

            if (!$hasValidRequirement) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Please enter at least one valid requirement.',
                ]);
            }

            // Fetch existing requirements from the database
            $existingRequirements = ScholarshipRequirements::where('scholarship_id', $scholarshipId)
                ->pluck('sch_requirements')
                ->toArray();

            // check for duplicates
            $newRequirements = [];
            foreach ($requirements as $index => $requirement) {
                $requirement = trim($requirement);

                if (empty($requirement)) {
                    continue; // skip ang empty requirements
                }

                if (in_array($requirement, $newRequirements)) {
                    return response()->json([
                        'Error' => 1,
                        'Message' => "Duplicate requirement found in your input: '$requirement'.",
                    ]);
                }

                $newRequirements[] = $requirement;
            }

            // check for duplicates between new inputs and existing requirements
            foreach ($newRequirements as $requirement) {
                if (in_array($requirement, $existingRequirements)) {
                    return response()->json([
                        'Error' => 1,
                        'Message' => "The requirement '$requirement' already exists in the database.",
                    ]);
                }
            }

            foreach ($requirements as $index => $requirement) {
                if (!empty($requirement)) {
                    ScholarshipRequirements::create([
                        'scholarship_id' => $scholarshipId,
                        'quantity' => $quantities[$index] ?? 1,
                        'sch_requirements' => $requirement,
                    ]);
                }
            }

            return response()->json([
                'Error' => 0,
                'Message' => 'Requirements saved successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => $e->getMessage(),
            ]);
        }
    }

    // edit requirements for scholarship application
    public function editRequirements(Request $request)
    {
        try {

            $id = $request->id;
            $scholarshipId = Crypt::decryptString($id);
            $scholarship = Scholarship::findOrFail($scholarshipId);

            $requirements = ScholarshipRequirements::where('scholarship_id', $scholarshipId)->get();

            return response()->json([
                'Error' => 0,
                'Scholarship' => [
                    'id' => $scholarship->id,
                    'name' => $scholarship->sch_name,
                ],
                'Requirements' => $requirements,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'Unable to fetch scholarship requirements. Please try again later.' . $e->getMessage(),
            ], 400);
        }
    }

    // update requirements for scholarship application
    public function updateRequirements(Request $request)
    {
        try {
            $scholarshipId = $request->scholarship_id_edit;
            $requirementIds = $request->requirement_ids;
            $quantities = $request->quantities_edit;
            $requirements = $request->requirements_edit;
            $deletedIds = $request->deleted_requirement_ids;

            if (!$scholarshipId) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Scholarship ID is required.',
                ]);
            }

            $hasChanges = false;

            if (!empty($deletedIds)) {
                ScholarshipRequirements::whereIn('id', $deletedIds)->delete();
                $hasChanges = true;
            }

            foreach ($requirements as $index => $requirementText) {
                $quantity = $quantities[$index] ?? null;
                $requirementId = $requirementIds[$index] ?? null;

                if (empty($requirementText)) {
                    continue;
                }

                $duplicate = ScholarshipRequirements::where('scholarship_id', $scholarshipId)
                    ->where('sch_requirements', $requirementText)
                    ->where('id', '!=', $requirementId)
                    ->exists();

                if ($duplicate) {
                    return response()->json([
                        'Error' => 1,
                        'Message' => "The requirement '{$requirementText}' already exists in the database.",
                    ]);
                }

                if ($requirementId) {
                    // update existing requirement
                    $requirement = ScholarshipRequirements::find($requirementId);

                    if ($requirement && ($requirement->quantity != $quantity || $requirement->sch_requirements != $requirementText)) {
                        $requirement->update([
                            'quantity' => $quantity,
                            'sch_requirements' => $requirementText,
                        ]);
                        $hasChanges = true;
                    }
                } else {
                    ScholarshipRequirements::create([
                        'scholarship_id' => $scholarshipId,
                        'quantity' => $quantity,
                        'sch_requirements' => $requirementText,
                    ]);
                    $hasChanges = true;
                }
            }

            if (!$hasChanges) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'No changes detected.',
                ]);
            }

            return response()->json([
                'Error' => 0,
                'Message' => 'Requirements updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'An error occurred: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function getCoursesWithMajors()
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection']);
            }

            $courses = Course::on($campus)
                ->with(['majors' => function ($query) {
                    $query->select('id', 'CourseID', 'course_major');
                }])
                ->get(['id', 'course_title']);

            return response()->json([
                'Error' => 0,
                'courses' => $courses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'An error occurred: ' . $e->getMessage()
            ], 400);
        }
    }


    public function storeApplication(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                return response()->json(['Error' => 1, 'Message' => 'Invalid campus database connection']);
            }
            $id = $request->scholarship_id;
            $scholarshipId = Crypt::decryptString($id);

            // extract applications
            $slots = $request->input('slots');
            $dateRange = $request->input('dateRange');
            $eligibleCourses = $request->input('eligible_courses');
            $eligibleMajors = $request->input('eligible_majors');
            $eligibleYearLevels = $request->input('eligible_year_levels');
            $schoolYear = $request->input('sch_application_sy');
            $semester = $request->input('sch_application_sem');

            // parse date range
            [$start_date, $deadline_date] = explode(' to ', $dateRange);

            // Find existing application or create a new one
            $application = ScholarshipApplication::on($campus)
                ->updateOrCreate(
                    ['scholarship_id' => $scholarshipId],
                    [
                        'slots' => $slots,
                        'start_date' => $start_date,
                        'deadline_date' => $deadline_date,
                        'eligible_courses' => json_encode($eligibleCourses),
                        'eligible_majors' => json_encode($eligibleMajors),
                        'eligible_yearLevel' => json_encode($eligibleYearLevels),
                        'school_year' => $schoolYear,
                        'semester' => $semester,
                        'status' => 1,
                    ]
                );

            $scholarship = Scholarship::on($campus)->findOrFail($scholarshipId);
            $scholarship->status = 1;
            $scholarship->save();

            return response()->json([
                'Error' => 0,
                'Message' => 'Scholarship application details saved successfully!',
                'Application' => $application,
                'Status' => $application->status,
                'ScholarshipStatus' => $scholarship->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'An error occurred: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function scholarshipApplication()
    {
        $pageTitle = "SCHOLARSHIP APPLICATION | SLSU";

        $scholarships = Scholarship::where('status', 1)
            ->with('requirements')
            ->get();

        return view('slsu.scholarships.application', compact('pageTitle', 'scholarships'));
    }

    public function deactivateApplication(Request $request)
    {
        try {
            $campus = strtolower(session('campus'));
            $scholarshipId = Crypt::decryptString($request->scholarship_id);

            $scholarship = Scholarship::on($campus)->findOrFail($scholarshipId);
            $scholarship->status = 0;
            $scholarship->save();

            $application = ScholarshipApplication::on($campus)
                ->where('scholarship_id', $scholarshipId)
                ->first();

            if ($application) {
                $application->status = 0;
                $application->save();
            }

            return response()->json([
                'Error' => 0,
                'Message' => 'Scholarship successfully deactivated!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'An error occurred: ' . $e->getMessage()
            ], 400);
        }
    }
}
