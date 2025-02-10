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

        $scholarships = ScholarshipNew::whereNull('deleted_at')
            ->orderBy('sch_type')
            ->orderBy('sch_name')
            ->get();

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
        } catch(DecryptException $e){
            return "Invalid hash.";
        }
    }

    public function fetchScholarships()
    {
        $scholarships = ScholarshipNew::whereNull('deleted_at')
            ->orderBy('sch_type')
            ->orderBy('sch_name')
            ->get();

        return view('_partials.scholarships-table', compact('scholarships'))->render();
    }

    public function edit($id)
    {
        try {
            $scholarshipId = Crypt::decryptString($id);
            $scholarship = ScholarshipNew::findOrFail($scholarshipId);

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
                'Message' => 'Scholarship not found or an error occurred: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $scholarship = ScholarshipNew::findOrFail($id);
            
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

            $existing = ScholarshipNew::where('sch_name', $ScholarshipName)->first();

            if ($existing) {
                return response()->json(['Error' => 1, "Message" => "Scholarship already exists."]);
            }
            
            $scholarship->update([
                'sch_name' => $ScholarshipName,
                'sch_type' => $ScholarshipType,
                'ext_type' => $ExternalSchType
            ]);

            return response()->json(['Error' => 0, "Message" => "$ScholarshipName updated successfully."]);

        } catch (\Exception $e) {
            return response()->json(['Error' => 1, "Message" => "An error occurred: " . $e->getMessage()], 400);
        }
    }

    public function delete($encryptedId)
    {
        try {
            $id = Crypt::decryptString($encryptedId);

            $scholarship = ScholarshipNew::findOrFail($id);
            $scholarship->delete(); 

            return response()->json(['Error' => 0, 'Message' => 'Scholarship deleted successfully.']);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['Error' => 1, 'Message' => 'Invalid ID.'], 400);
        } catch (\Exception $e) {
            return response()->json(['Error' => 1, 'Message' => 'Error deleting scholarship: ' . $e->getMessage()], 400);
        }
    }
}
