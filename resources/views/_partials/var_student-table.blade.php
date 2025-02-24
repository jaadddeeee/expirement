<!-- resources/views/partials/varsity-table.blade.php -->
@foreach($varsities as $varsity)
    <tr class="varsity-row">    
        <td class="text-nowrap">{{ (isset($ctr)?++$ctr:$ctr=1) }}</td>
        <td class="text-nowrap">
            {{ strtoupper($varsity->LastName . ', ' . $varsity->FirstName . (empty($varsity->MiddleName) ? '' : ' ' . $varsity->MiddleName[0] . '.')) }}
        </td>
        <td class="text-nowrap">
            {{-- Access the event relationship here --}}
            {{ $varsity->event->event }}
        </td>
        <td class="text-nowrap">
            {{ \GENERAL::setSchoolYearLabel($varsity->SchoolYear, $varsity->Semester) . " - " . \GENERAL::Semesters()[$varsity->Semester]['Long'] }}
        </td>
        <td class="text-nowrap">
            <a href="#" class="editVarsity" cid="{{ Crypt::encryptString($varsity->id) }}">
                <i class="bx bx-edit text-warning"></i>
            </a>
            &nbsp;
            <a href="#" class="deleteVarsity" cid="{{ Crypt::encryptString($varsity->id) }}">
                <i class="text-danger bx bx-trash"></i>
            </a>
        </td>
    </tr>
@endforeach

@if ($varsities->isEmpty())
    <tr>
        <td colspan="5" class="text-center table-warning  fw-bold rounded-pill">No records found</td>
    </tr>
@endif
