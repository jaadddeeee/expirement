<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scholarship\Scholarship;
use App\Models\Scholarship\ScholarshipRequirements;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


use GENERAL;


class ScholarshipController extends Controller
{

    public function index(Request $request)
    {
        try {

            $pageTitle = "Scholarships";
            $headerAction = '<a href="' . url()->previous() . '" class="btn btn-sm btn-primary" role="button">Back</a>';

            $query = Scholarship::whereNull('deleted_at')
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

            if ($request->ajax()) {
                return response()->json([
                    'scholarshipTable' => view('slsu.scholarships._partials._scholarships-table', compact('scholarships'))->render()
                ]);
            }

            return view('slsu.scholarships.index', compact('pageTitle', 'headerAction', 'scholarships', 'entriesPerPage'));
        } catch (\Exception $e) {
            return response()->json([
                'Error' => 1,
                'Message' => 'An error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

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

    // public function storeRequirements(Request $request)
    // {
    //     try {
    //         $scholarshipId = $request->scholarship_id;
    //         $requirements = $request->requirements;
    //         $quantities = $request->quantities;

    //         if (!$scholarshipId || empty($requirements)) {
    //             return response()->json([
    //                 'Error' => 1,
    //                 'Message' => 'Please input at least one requirement.',
    //             ]);
    //         }

    //         foreach ($quantities as $index => $quantity) {
    //             if (empty($quantity) || $quantity <= 0) {
    //                 return response()->json([
    //                     'Error' => 1,
    //                     'Message' => 'Please provide a valid quantity for all requirements.',
    //                 ]);
    //             }
    //         }

    //         $hasValidRequirement = false;
    //         foreach ($requirements as $r) {
    //             if (trim($r) !== '') {
    //                 $hasValidRequirement = true;
    //                 break;
    //             }
    //         }

    //         if (!$hasValidRequirement) {
    //             return response()->json([
    //                 'Error' => 1,
    //                 'Message' => 'Please enter at least one valid requirement.',
    //             ]);
    //         }

    //         // Fetch existing requirements from the database
    //         $existingRequirements = ScholarshipRequirements::where('scholarship_id', $scholarshipId)
    //             ->pluck('sch_requirements')
    //             ->toArray();

    //         // Check for duplicates within the new inputs
    //         $newRequirements = [];
    //         foreach ($requirements as $index => $requirement) {
    //             $requirement = trim($requirement);

    //             if (empty($requirement)) {
    //                 continue; // Skip empty requirements
    //             }

    //             if (in_array($requirement, $newRequirements)) {
    //                 return response()->json([
    //                     'Error' => 1,
    //                     'Message' => "Duplicate requirement found in your input: '$requirement'.",
    //                 ]);
    //             }

    //             $newRequirements[] = $requirement;
    //         }

    //         // Check for duplicates between new inputs and existing requirements
    //         foreach ($newRequirements as $requirement) {
    //             if (in_array($requirement, $existingRequirements)) {
    //                 return response()->json([
    //                     'Error' => 1,
    //                     'Message' => "The requirement '$requirement' already exists in the database.",
    //                 ]);
    //             }
    //         }

    //         foreach ($requirements as $index => $requirement) {
    //             if (!empty($requirement)) {
    //                 ScholarshipRequirements::create([
    //                     'scholarship_id' => $scholarshipId,
    //                     'quantity' => $quantities[$index] ?? 1,
    //                     'sch_requirements' => $requirement,
    //                 ]);
    //             }
    //         }

    //         return response()->json([
    //             'Error' => 0,
    //             'Message' => 'Requirements saved successfully!',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'Error' => 1,
    //             'Message' => $e->getMessage(),
    //         ]);
    //     }
    // }


    public function editRequirements(Request $request)
    {
        try {
            $id = $request->id;
            $scholarship = Scholarship::findOrFail($id);

            $requirements = ScholarshipRequirements::where('scholarship_id', $id)->get();

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


    public function updateRequirements(Request $request)
    {
        try {
            $scholarshipId = $request->scholarship_id_edit;
            $requirementIds = $request->requirement_ids ?? [];
            $quantities = $request->quantities_edit ?? [];
            $requirements = $request->requirements_edit ?? [];
            $deletedIds = $request->deleted_requirement_ids ?? [];

            // Validate input
            if (!$scholarshipId) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Scholarship ID is required.',
                ]);
            }

            // Fetch existing requirements for comparison
            $existingRequirements = ScholarshipRequirements::where('scholarship_id', $scholarshipId)->get();

            $hasChanges = false;

            // Delete removed requirements
            if (!empty($deletedIds)) {
                ScholarshipRequirements::whereIn('id', $deletedIds)->delete();
                $hasChanges = true;
            }

            // Update existing or create new requirements
            foreach ($requirements as $index => $requirementText) {
                $quantity = $quantities[$index] ?? null;
                $requirementId = $requirementIds[$index] ?? null;

                if (empty($requirementText)) {
                    continue;
                }

                // Check for duplicates in the database
                $duplicate = ScholarshipRequirements::where('scholarship_id', $scholarshipId)
                    ->where('sch_requirements', $requirementText)
                    ->where('id', '!=', $requirementId) // Exclude the current requirement being updated
                    ->exists();

                if ($duplicate) {
                    return response()->json([
                        'Error' => 1,
                        'Message' => "The requirement '{$requirementText}' already exists in the database.",
                    ]);
                }

                if ($requirementId) {
                    // Update existing requirement
                    $requirement = ScholarshipRequirements::find($requirementId);

                    if ($requirement && ($requirement->quantity != $quantity || $requirement->sch_requirements != $requirementText)) {
                        $requirement->update([
                            'quantity' => $quantity,
                            'sch_requirements' => $requirementText,
                        ]);
                        $hasChanges = true;
                    }
                } else {
                    // Create new requirement
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
}
