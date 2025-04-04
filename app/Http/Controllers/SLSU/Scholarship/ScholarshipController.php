<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scholarship\Scholarship;


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
                return response()->json(['Error' => 1, 'Message' => "Empty Scholarship Name"]);
            }

            if (empty($ScholarshipAcronym)) {
                return response()->json(['Error' => 1, 'Message' => "Empty Scholarship Acronym"]);
            }

            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, 'Message' => "Please select scholarship type"]);
            }

            if ($ScholarshipType == 1) {
                $ExternalSchType = 'SLSU';
            } elseif (empty($ExternalSchType)) {
                return response()->json(['Error' => 1, 'Message' => "Please select external type"]);
            }

            $existing = Scholarship::where('sch_name', $ScholarshipName)->first();

            if ($existing) {
                return response()->json(['Error' => 1, 'Message' => "Scholarship already exists."]);
            }

            Scholarship::create([
                'sch_name' => $ScholarshipName,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType,
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
                'Message' => "An error occurred: " . $e->getMessage()
            ], 400);
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
                    'type' => $scholarship->sch_type,
                    'externalType' => $scholarship->ext_type,
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

            $ScholarshipName = trim($request->editScholarshipName);
            $ScholarshipType = $request->editScholarshipType;
            $ExternalSchType = $request->editExternalScholarshipType;

            if (empty($ScholarshipName)) {
                return response()->json(['Error' => 1, 'Message' => "Empty Scholarship Name"]);
            }

            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, 'Message' => "Please select scholarship type"]);
            }

            if ($ScholarshipType == 1) {
                $ExternalSchType = 'N/A';
            } elseif (empty($ExternalSchType)) {
                return response()->json(['Error' => 1, 'Message' => "Please select external type"]);
            }

            $existing = Scholarship::where('sch_name', $ScholarshipName)
                ->where('sch_type', $ScholarshipType)
                ->where('ext_type', $ExternalSchType)
                ->first();

            if ($existing) {
                return response()->json(['Error' => 1, 'Message' => "Scholarship already exists."]);
            }

            $scholarship->update([
                'sch_name' => $ScholarshipName,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType,
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

            $scholarship = Scholarship::findOrFail($id);
            $scholarship->delete();

            return response()->json(['Error' => 0, 'Message' => 'Scholarship deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'Error deleting scholarship: ' . $e->getMessage()], 400);
        }
    }
}
