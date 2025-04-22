<?php

namespace App\Http\Controllers\SLSU\Report\Varsity;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;
use App\Models\VARSITY\Scuaa;
use App\Models\VARSITY\CoachVarsity;
use App\Models\VARSITY\ListVarsity;

class EligibilityForm extends TCPDF
{
    protected $id;
    protected $sy;
    protected $event;
    protected $lists;
    public function __construct(){
        $this->letter = new LetterHead();
        $this->prefs = new Preference();
        $this->pref = $this->prefs->GetDefaults();
    }

    private function physician()
    {
        $defaultName = $this->prefs->GetDefaultValue($this->pref, "Physician");

        // Remove suffixes (anything after the first comma)
        $cleanedName = explode(',', $defaultName)[0];

        // Split into parts
        $nameParts = preg_split('/\s+/', trim($cleanedName));

        // Extract name components
        $lastName = strtoupper(array_pop($nameParts)); 
        $firstName = strtoupper(array_shift($nameParts));  
        $middleInitial = strtoupper(!empty($nameParts) ? strtoupper(substr($nameParts[0], 0, 1)) : null);
        
        $query = DB::connection('clinic')
            ->table('doctors')
            ->where('FirstName', $firstName)
            ->where(DB::raw("TRIM(SUBSTRING_INDEX(LastName, ',', 1))"), '=', $lastName)
            ->whereIn('campus', [1]);

        // Add MiddleName condition only if a middle initial is present
        if ($middleInitial) {
            $query->where(DB::raw('LEFT(MiddleName, 1)'), '=', $middleInitial);
        }

        // Execute the query
        $physician = $query->first();

        return $physician;

    }

    private function listVarsity()
    {
        $query = ListVarsity::with('event')
            ->whereNull('deleted_at')
            ->where('id', $this->getId())
            ->orderBy('SchoolYear', 'desc')
            ->get(); 
    
        $connections = ['sg', 'mcc', 'to', 'bn', 'sj', 'hn'];
        $studentNos = $query->pluck('StudentNo')->unique(); 
        $allStudents = collect();
    
        $medicalRecords = DB::connection('clinic')
            ->table('medicalrecord as m')
            ->join('health_history as hh', 'm.patientId', '=', 'hh.patientId')
            ->whereIn('m.patientId', $studentNos)
            ->whereIn('m.campus', [1, 2, 3, 4, 5, 6])
            ->get()
            ->keyBy('m.patientId');
    
        // Loop through each connection to get student details
        foreach ($connections as $connection) {
            $students = Student::on($connection)
                ->whereIn('students.StudentNo', $studentNos)
                ->get()
                ->map(function ($student) use ($connection) {
                    return $student;
                });
    
            $allStudents = $allStudents->merge($students);
        }
    
        $studentLookup = $allStudents->keyBy('StudentNo');
    
        // Merge student details into the varsity list
        $varsityList = $query->map(function ($item) use ($studentLookup, $medicalRecords) {
    
            $student = $studentLookup[$item->StudentNo] ?? null;
            $medical = $medicalRecords->firstWhere('patientId', $item->StudentNo);
    
            // Build full name, date of birth, address, and other details
            $fullName = ($student->LastName ?? 'N/A') . ', ' . ($student->FirstName ?? 'N/A') .
                (!empty($student->MiddleName) ? ' ' . $student->MiddleName[0] . '.' : '');
    
            $dob = !empty($student->BirthDate) 
                ? \DateTime::createFromFormat('d/m/Y', $student->BirthDate)?->format('F j, Y') ?? 'N/A'
                : 'N/A';
    
            $address = $student->p_street . ', ' . $student->p_municipality . ', ' . $student->p_province . ', ' . $student->p_zip ?? 'N/A';
    
            $age = date_diff(date_create($dob), date_create('today'))->y;

            $this->setName(\Str::slug($student->LastName.'-'.$student->FirstName));
    
            return [
                'FullName'  => strtoupper($fullName),
                'DOB'        => $dob ?? 'N/A',
                'Address'    => $address ?? 'N/A',
                'ContactNo'  => $student->ContactNo ?? 'N/A',
                'EmergencyName' => $student->emer_name ?? 'N/A',
                'EmergencyContact' => $student->emer_contact ?? 'N/A',
                'Age'      => $age,
                'BloodType'  => $medical->blood_type ?? 'N/A',
                'Weight'     => $medical->weight ?? 'N/A',
                'Height'     => $medical->height ?? 'N/A',
                'Allergies'  => $medical->al_detail ?? 'N/A',
                'Medication'  => $medical->med_detail ?? 'N/A',
            ];
        });
    
        // Sort the varsity list by full name and return it as an array
        $listAthletes = $varsityList->sortBy('FullName')->values()->toArray();
        return $listAthletes;
    }
    
    public function Header()
    {
        $startYear = date('Y'); // Get the current year

        // Fetch the ScuaaLogo where the Date column contains the current year
        $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $startYear . '%')
            ->select('*')
            ->first();

        $this->setDate(\Str::slug($scuaaList->Date));

        $this->listAthletes = $this->listVarsity();
        $this->physician = $this->physician();
        $this->letter->ScuaaHeader();

        $this::setXY(184, 2);
        $this::SetFont('calibri','',10);
        $this::Cell(0,5,'SCUAA Form 3',0,1,'L');
    }

    private function headerLable()
    {
        $physician = $this->physician ?? null;

        // dd($this->getStatus());

        if($this->getStatus() === '0'){
            $physician = null;
            $fullname = '';
        } else {
            $fullname = $physician->FirstName . ' ' . $physician->MiddleName . ' ' . $physician->LastName;
        }

        $startYear = date('Y'); // Get the current year

        // Fetch the ScuaaLogo where the Date column contains the current year
        $scuaaList = Scuaa::where('Date', 'LIKE', '%' . $startYear . '%')
            ->select('*')
            ->first();
        //top label
        $x = 15;
        $y = 8;

        $labels = [
            ["PARTICIPANT'S PERSONAL INFORMATION", 74, 45, 12, 'C'],
            ['NAME OF ATHLETE:', -82.5, 7, 11, 'L'],
            ['AGE:', 162.8, 0, 11, 'L'],
            ['DATE OF BIRTH:', -163, 7, 11, 'L'],
            ['WEIGHT (kg.):', 99, -0.5, 11, 'L'],
            ['HEIGHT (cm):', 47, 0, 11, 'L'],
            ['BLOOD TYPE:', -146, 7.7, 11, 'L'],
            ['ALLERGIES:', -158, 0, 11, 'L'],
            ['Medications(if any):', 70, 0, 11, 'L'],
            ['ADDRESS:', -122, 7, 11, 'L'],
            ['CONTACT NO:', 145, 0, 11, 'L'],
        ];

        foreach ($labels as $label) 
        {
            [$text, $xOffset, $yOffset, $fontSize, $align] = $label;
            $x += $xOffset;
            $y += $yOffset;
            $this::setXY($x, $y);
            $this::SetFont('calibri', '', $fontSize);
            $this::Cell(30, 10, $text, 0, 0, $align);
        }

        //center content
        $x = 6;
        $y = 54;
        $this::setXY($x + 34, $y + 39);
        $this::SetFont('lucidafaxdemib', 'U', 12);
        $this::Cell(30, 10, 'MEDICAL CERTIFICATE', 0, 0, 'C');

        $this::setXY($x + 3, $y + 48);
        $this::SetFont('calibri', '', 11);
        $this::Cell(30, 10, 'This is to certify that:', 0, 0, 'C');

        $this::SetFont('calibri', '', 10.5);
        $this::writeHTMLCell(0,0,$x + 0.2,$y + 64,'<span>is <strong>Physically Fit</strong> to participate in the;</span>',0, 1,false,true,'L');

        $this::SetFont('calibri', '', 10);
        $html ='<div style="text-align: justify;">
                    <p><span style="font-family:calibri;">[ √ ]</span> <span style="font-family:times new roman;"><strong><i>REGIONAL SCUAA GAMES '. $startYear .'</i></strong> on <strong><i>'. $scuaaList->Date .'</i></strong>
                    at <strong><i>'. $scuaaList->University .'</i></strong>, '. $scuaaList->Location .'.</span></p>
                </div>';
        $this::writeHTMLCell(97,0, $x, $y + 64,$html,0, 1,false,true,'L');

        $this::setXY($x - 2.5, $y + 85);
        $this::Cell(30, 10, 'Blood Pressure:', 0, 0, 'C');

        $this::setXY($x + 1, $y + 106.5);
        $this::Cell(30, 10, 'Date of Examination:', 0, 0, 'C');

        $this::setXY($x + 49, $y + 101.7);
        $this::SetFont('calibrib', '', 10);
        $this::Cell(30, 10, 'Name and Signature of Physician', 0, 0, 'C');

        $this::setXY($x + 49.5, $y + 96);
        $this::SetFont('calibrib', '', 14);
        $this::Cell(30, 10, $fullname, 0, 0, 'C');

        $this::setXY($x + 33, $y + 113);
        $this::SetFont('calibrib', '', 11);
        $this::Cell(30, 10, $physician->license ?? '', 0, 0, 'C');

        $this::setXY($x + 27, $y + 106.5);
        $this::SetFont('calibri', '', 10);
        $this::Cell(30, 10, 'License No.:', 0, 0, 'C');

        $this::setXY($x + 59.7, $y + 106.5);
        $this::Cell(30, 10, 'Validity Date:', 0, 0, 'C');

        $this::setXY($x + 134, $y + 39);
        $this::SetFont('lucidafaxdemib', 'U', 11);
        $this::Cell(30, 10, "ATHLETE'S WAIVER & RELEASE AGREEMENT", 0, 0, 'C');

        $this::AddFont('calibri', '', 'calibri.php');
        $this::AddFont('calibri', 'B', 'calibrib.php');
        $this::AddFont('calibri', 'I', 'calibrii.php');
        $this::AddFont('calibri', 'BI', 'calibribi.php');
        $this::SetFont('calibri', '', 10);
        $html = '<div style="text-align: justify;">
                    <p>In consideration of the acceptance of my entry, myself, my heirs, executors, administrators & assigns, do hereby release & discharge the organizers of the 
                    <strong><i>REGIONAL SCUAA GAMES '. $startYear .'</i></strong>, assisting groups of private or government agencies, the Commission on Higher Education and other concerned institutions, respective schools and officials, and other parties, individual or group, from all claims and damages, demands or actions whatsoever in any manner arising from of growing out of my participation in, or while traveling to and from the above-mentioned sports competition. 
                    I further attest and verify that I have obtained the necessary clearance from my medical doctor and guaranteed 
                    <strong><i>Physically Fit</i></strong> to participate in the said sports competition.</p>
                </div>';
        
        $this::writeHTMLCell(99, 0, $x + 100, $y + 40, $html, 0, 1, false, true, 'L');
        
        $this::setXY($x + 134, $y + 115);
        $this::SetFont('calibrib', '', 10);
        $this::Cell(30, 10, 'Printed Name and Signature of Athlete', 0, 0, 'C');

        //button label
        $this::setXY($x + 82, $y + 125);
        $this::SetFont('lucidafaxdemib', '', 12);   
        $this::Cell(30, 10, "PARENT/GUARDIAN PERMIT/CONSENT", 0, 0, 'C');

        $this::SetFont('calibri', '', 11);
        $html = '<div style="text-align: justify; word-wrap: break-word; line-height: 1.2;">
                    <p>This is to certify that I have full knowledge of and permission for my son/daughter/foster child to join and participate in the following competitions:</p>
                </div>';
        $this::writeHTMLCell(198, 0, $x , $y + 128, $html, 0, 1, false, true, 'L');
        
        $this::SetFont('calibri', '', 10);
        $html ='<div style="text-align: justify;">
                    <p><span style="font-family:calibri;">[ √ ]</span> <span style="font-family:times new roman;"><strong><i>REGIONAL SCUAA GAMES '. $startYear .'</i></strong> on <strong><i>'. $scuaaList->Date .'</i></strong>
                    at <strong><i>'. $scuaaList->University .'</i></strong>, '. $scuaaList->Location .'.</span></p>
                </div>';
        $this::writeHTMLCell(198,0, $x, $y + 146, $html,0, 1,false,true,'L');
        
        $this::SetFont('calibri', '', 11);
        $html = '<div style="text-align: justify; word-wrap: break-word; line-height: 1.2;">
                    <p>I concur and agree on the rules, policies and regulations being implemented by the concerned organizers.</p>
                </div>';
        $this::writeHTMLCell(198, 0, $x , $y + 158.5, $html, 0, 1, false, true, 'L');

        $this::setXY($x + 33, $y + 184);
        $this::SetFont('calibrib', '', 11);
        $this::Cell(30, 10, "Printed Name and Signature of Parent/Guardian", 0, 0, 'C');

        $this::setXY($x + 133, $y + 184);
        $this::Cell(30, 10, "Contact Number", 0, 0, 'C');

        $html = '<div style="text-align: justify; word-wrap: break-word; line-height: 1.2;">
                    SUBCRIBED AND SWORN TO before me this _______________________ in __________________________________ .
                </div>';
        $this::writeHTMLCell(198, 0, $x + 3 , $y + 195, $html, 0, 1, false, true, 'L');

        $this::setXY($x + 148, $y + 223);
        $this::Cell(30, 10, "Notary", 0, 0, 'C');
        $this::line($x + 140, $y + 225, $x + 185, $y + 225); // Add a top line below the text
    }

    private function dashLine($dashX, $dashY)
    {
        $columns = 95;
        $columnSpacing = 2;
        for ($j = 0; $j < $columns; $j++) 
        {
            $this::setXY($dashX, $dashY);
            $this::SetFont('calibri', '', 10);
            $this::Cell(10, 0, "=", 0, 0, 'C');
            $dashX += $columnSpacing;
        }
    }

    private function horizontalLayout()
    {
        //top line
        $startX = 6;
        $startY = 54;
        $lineSpacing = 7;
        $totalLines = 6;
        
        for ($i = 0; $i < $totalLines; $i++) {
            $this::line($startX, $startY, 204, $startY);
            $startY += $lineSpacing;
        }

        // center line
        $centerY = 93.5;

        $lines = [
            [6, $centerY, 103, $centerY],
            [204, $centerY, 106, $centerY],
            [6, $centerY + 47.2, 103, $centerY + 47.2], 
            [6, $centerY + 69.2, 103, $centerY + 69.2], 
            [38, $centerY + 64.2, 103, $centerY + 64.2], 
            [6, $centerY + 82.7, 103, $centerY + 82.7], 
            [204, $centerY + 82.7, 106, $centerY + 82.7],
            [204, $centerY + 77.7, 106, $centerY + 77.7], 
        ];
        
        // Loop through the configurations and draw the lines
        foreach ($lines as $line) {
            [$startX, $startY, $endX, $endY] = $line;
            $this::line($startX, $startY, $endX, $endY);
        }

        //bottom line
        $buttomX = 6;
        $buttomY = 180;

        $buttomLines = [
            [$buttomX, $buttomY, 204, $buttomY],
            [$buttomX, $buttomY + 27, 204, $buttomY + 27],
            [$buttomX, $buttomY + 40, 204, $buttomY + 40],
            [$buttomX, $buttomY + 48, 204, $buttomY + 48],
            [$buttomX, $buttomY + 60, 204, $buttomY + 60],
            [$buttomX, $buttomY + 65, 204, $buttomY + 65],
        ];

        foreach ($buttomLines as $line) {
            [$buttomX, $buttomY, $endX, $endY] = $line;
            $this::line($buttomX, $buttomY, $endX, $endY);
        }
    }

    public function verticalLayout()
    {
        //top line
        $startX = 6;
        $startY = 54;

        $lines = [
            [163, 0, 89], 
            [-64, 7, 68],   
            [99, 21, 68], 
            [-146, 0, 89],  
            [70, 28, 75], 
            [23, 28, 75],   
            [23, 35, 82], 
        ];
        
        // Loop through the lines and draw them
        foreach ($lines as $line) {
            [$xOffset, $yOffset, $endY] = $line;
            $this::line($startX, $startY + $yOffset, $startX, $endY);
            $startX += $xOffset;
        }

        //center line
        $centerX = 6;
        $centerY = 93.3;

        $centerLine = [
            [$centerX, $centerY, $centerX, 176.4],
            [$centerX + 32, $centerY + 47.6, $centerX + 32, 176.4],
            [$centerX + 64, $centerY + 69.6, $centerX + 64, 176.4],
            [$centerX + 100, $centerY, $centerX + 100, 176.4],
            [$centerX + 198, $centerY, $centerX + 198, 176.4],
            [$centerX + 97, $centerY, $centerX + 97, 176.4],
        ];

        foreach ($centerLine as $line) {
            [$centerX, $centerY, $endX, $endY] = $line;
            $this::line($centerX, $centerY, $endX, $endY);
        }

        //bottom line
        $buttomX = 6;
        $buttomY = 180;

        $this::line($buttomX, $buttomY, $buttomX, 245);

        $buttomX += 198;
        $this::line($buttomX, $buttomY, $buttomX, 245);
        
        $buttomX -= 99.6;
        $this::line($buttomX, $buttomY + 65, $buttomX, 228);


    }

    public function Body()
    {
        // dd($this->getStatus());
        $x = 15;
        $y = 8;
        $dashX = 6;
        $dashY = 89.3;

        $this->headerLable();
        $this->horizontalLayout();
        $this->verticalLayout();

        $this::dashLine($dashX, $dashY);

        $dashY += 86.6;
        $this::dashLine($dashX, $dashY);

        $dashY += 68.7;
        $this::dashLine($dashX, $dashY);

        $athlete = $this->listAthletes[0] ?? null;

        $this::setXY($x + 29, $y + 52);
        $this::SetFont('calibrib','',14);
        $this::Cell(30,10,$athlete['FullName'],0,0,'L');

        $this::setXY($x + 169, $y + 52);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['Age'],0,0,'L');

        $this::setXY($x + 24, $y + 59);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['DOB'],0,0,'L');

        $this::setXY($x + 119, $y + 59);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['Weight'],0,0,'L');
        
        $this::setXY($x + 164, $y + 59);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['Height'],0,0,'L');

        $this::setXY($x + 18, $y + 66);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['BloodType'],0,0,'L');

        $this::setXY($x + 67, $y + 66);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['Allergies'],0,0,'L');

        $this::setXY($x + 150, $y + 66);
        $this::SetFont('calibrib','',11);
        $this::Cell(30,10,$athlete['Medication'],0,0,'L');

        $this::setXY($x + 14, $y + 73);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['Address'],0,0,'L');

        $this::setXY($x + 162, $y + 73);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['ContactNo'],0,0,'L');

        $this::setXY($x + 125.6, $y + 155);
        $this::SetFont('calibrib','',14);
        $this::Cell(30,10,$athlete['FullName'],0,0,'C');

        $this::setXY($x + 25, $y + 224);
        $this::SetFont('calibrib','',14);
        $this::Cell(30,10,strtoupper($athlete['EmergencyName']),0,0,'C');

        $this::setXY($x + 124, $y + 224);
        $this::SetFont('calibrib','',12);
        $this::Cell(30,10,$athlete['EmergencyContact'],0,0,'C');


    }

    /**
   * Get the value of data
   */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of sy
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of sy
     *
     * @return  self
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get the value of sem
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of sem
     *
     * @return  self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
    
        /**
     * Get the value of sem
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of sem
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
