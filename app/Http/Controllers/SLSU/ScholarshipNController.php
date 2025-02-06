<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

use App\Models\ScholarshipNew;
use GENERAL;

class ScholarshipNController extends Controller
{
    public function index()
    {
        $pageTitle = "Scholarships";
        $headerAction = '<a href="' . url()->previous() . '" class="btn btn-sm btn-primary" role="button">Back</a>';

        $scholarships = ScholarshipNew::orderBy('sch_type')
            ->orderBy('sch_name')->get();

        return view('slsu.scholarshipnew.index', compact('pageTitle', 'headerAction', 'scholarships'));
    }

    public function save(Request $request)
    {
        try {
            $ScholarshipName = trim($request->ScholarshipName);
            $ScholarshipType = $request->ScholarshipType;
            $ExternalSchType = $request->ExternalScholarshipType;

            if (empty($ScholarshipName)) {
                return response()->json(['Error' => 1, "Message" => "Empty Scholarship Name"]);
            }
    
            if (empty($ScholarshipType) || $ScholarshipType == 0) {
                return response()->json(['Error' => 1, "Message" => "Please select scholarship type"]);
            }
    
            if ($ScholarshipType == 1) {
                $ExternalSchType = 'N/A';
            } elseif (empty($ExternalSchType)) {
                return response()->json(['Error' => 1, "Message" => "Please select external type"]);
            }

            // Check for duplicate entries
            $existing = ScholarshipNew::where('sch_name', $ScholarshipName)->first();

            if ($existing) {
                return response()->json(['Error' => 1, "Message" => "Scholarship already exists."]);
            }

            $scholarship = new ScholarshipNew();
            $scholarship->sch_name = $ScholarshipName;
            $scholarship->sch_type = $ScholarshipType;
            $scholarship->ext_type = $ExternalSchType;
            $scholarship->save();

            return response()->json(['Error' => 0, "Message" => "$ScholarshipName successfully inserted."]);
    
        } catch (\Exception $e){
            return response()->json(['Error' => 1, "Message" => "An error occurred: " . $e->getMessage()], 400);
        }
    }

    // mo fetch ang data sa table automatically after saving or updating
    public function getScholarships()
    {
        $scholarships = ScholarshipNew::orderBy('sch_type')
            ->orderBy('sch_name')->get();

        return view('_partials.scholarships-table', compact('scholarships'))->render();
    }

    // edit
    public function edit($id)
    {
        try{
            $decryptedId = Crypt::decryptString($id);
            $scholarship = ScholarshipNew::findOrFail($decryptedId);
    
            return response()->json([
                'Error' => 0,
                'Scholarship' => $scholarship
            ]);

        } catch (\Exception $e){
            return response()->json([
                'Error' => 1,
                'Message' => 'Failed to fetch scholarship data.'
            ], 400);
        }
    }

    // update
    public function update(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decryptString($id);
            $scholarship = ScholarshipNew::findOrFail($decryptedId);

            $scholarship->sch_name = trim($request->ScholarshipName);
            $scholarship->sch_type = $request->ScholarshipType;
            $scholarship->ext_type = $request->ExternalScholarshipType ?? 'N/A';
            $scholarship->save();

            return response()->json(['Error' => 0, 'Message' => 'Scholarship updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'Failed to update scholarship.'], 400);
        }
    }
}
