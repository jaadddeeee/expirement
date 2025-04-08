<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scholarship\Scholarship;
use App\Models\Scholarship\ScholarshipRequirements;
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

            $scholarship = Scholarship::findOrFail($id);

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

    // public function update(Request $request)
    // {
    //     try {
    //         $id = $request->id;
    //         $scholarship = Scholarship::findOrFail($id);

    //         $ScholarshipName = trim($request->editScholarshipName);
    //         $ScholarshipType = $request->editScholarshipType;
    //         $ExternalSchType = $request->editExternalScholarshipType;

    //         if (empty($ScholarshipName)) {
    //             return response()->json(['Error' => 1, 'Message' => "Empty Scholarship Name"]);
    //         }

    //         if (empty($ScholarshipType) || $ScholarshipType == 0) {
    //             return response()->json(['Error' => 1, 'Message' => "Please select scholarship type"]);
    //         }

    //         if ($ScholarshipType == 1) {
    //             $ExternalSchType = 'N/A';
    //         } elseif (empty($ExternalSchType)) {
    //             return response()->json(['Error' => 1, 'Message' => "Please select external type"]);
    //         }

    //         $existing = Scholarship::where('sch_name', $ScholarshipName)
    //             ->where('sch_type', $ScholarshipType)
    //             ->where('ext_type', $ExternalSchType)
    //             ->first();

    //         if ($existing) {
    //             return response()->json(['Error' => 1, 'Message' => "Scholarship already exists."]);
    //         }

    //         $scholarship->update([
    //             'sch_name' => $ScholarshipName,
    //             'sch_type' => $ScholarshipType,
    //             'ext_type' => $ExternalSchType,
    //             'updated_at' => now()
    //         ]);

    //         return response()->json(['Error' => 0, 'Message' => "Scholarship updated successfully."]);
    //     } catch (\Exception $e) {
    //         return response()->json(['Error' => 1, 'Message' => "An error occurred: " . $e->getMessage()], 400);
    //     }
    // }


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

            // Validate Scholarship Name
            if (empty($ScholarshipName)) {
                return response()->json(['Error' => 1, 'Message' => "Scholarship Name is required."]);
            }

            // Validate Scholarship Acronym
            if (empty($ScholarshipAcronym)) {
                return response()->json(['Error' => 1, 'Message' => "Scholarship Acronym is required."]);
            }

            // Validate Scholarship Type
            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, 'Message' => "Please select a Scholarship Type."]);
            }

            // Handle Internal Scholarship
            if ($ScholarshipType == 1) {
                $ExternalSchType = 0;

                $campuses = GENERAL::Campuses();
                $campusCode = strtoupper(session('campus'));
                $campusName = isset($campuses[$campusCode]) ? $campuses[$campusCode]['Campus'] : 'Unknown Campus';

                $ScholarshipProvider = 'SLSU - ' . $campusName;
            } elseif (empty($ExternalSchType)) {
                // Validate External Type for External Scholarships
                return response()->json(['Error' => 1, 'Message' => "Please select an External Type."]);
            }

            // Validate Scholarship Provider for External Scholarships
            if ($ScholarshipType != 1 && empty($ScholarshipProvider)) {
                return response()->json(['Error' => 1, 'Message' => "Please provide a Scholarship Provider."]);
            }

            // Check if scholarship already exists (excluding the current scholarship)
            $existing = Scholarship::where('sch_name', $ScholarshipName)
                ->where('sch_type', $ScholarshipType)
                ->where('ext_type', $ExternalSchType)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json(['Error' => 1, 'Message' => "A scholarship with the same name, type, and external type already exists."]);
            }

            // Update Scholarship
            $scholarship->update([
                'sch_name' => $ScholarshipName,
                'sch_acronym' => $ScholarshipAcronym,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType,
                'sch_provider' => $ScholarshipProvider,
                'updated_at' => now(),
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

            $scholarship = Scholarship::findOrFail($id);
            $scholarship->delete();

            return response()->json(['Error' => 0, 'Message' => 'Scholarship deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'Error deleting scholarship: ' . $e->getMessage()], 400);
        }
    }

    public function storeRequirements(Request $request)
    {
        try {
            $scholarshipId = $request->scholarship_id;
            $requirements = $request->requirements;
            $quantities = $request->quantities;

            if (!$scholarshipId || empty($requirements)) {
                return response()->json([
                    'Error' => 1,
                    'Message' => 'Please input at least one requirement.',
                ]);
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
}
