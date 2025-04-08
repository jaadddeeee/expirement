<!-- resources/views/partials/varsity-table.blade.php -->

@foreach ($Coaches as $coach)
    <tr class="varsity-row">
        <td class = "text-nowrap">{{ isset($ctr) ? ++$ctr : ($ctr = 1) }}</td>
        <td class="text-nowrap">
            {{ strtoupper($coach->LastName . ', ' . $coach->FirstName . (empty($coach->MiddleName) ? '' : ' ' . $coach->MiddleName[0] . '.')) }}
        </td>
        <td class="text-nowrap">
            {{-- Access the event relationship here --}}
            {{ $coach->event_name}}
        </td>
        <td class="text-nowrap">
            {{ $coach->SchoolYear }}
        </td>
        <td class="text-nowrap">
            {{-- <a href="#" class="editVarsity" cid="{{ Crypt::encryptString($varsity->id) }}">
                <i class="bx bx-edit text-warning"></i>
            </a> --}}
            &nbsp;
            <a href="#" id="deleteCoaches" cid="{{ Crypt::encryptString($coach->id) }}">
                <i class="text-danger bx bx-trash"></i>
            </a>
        </td>
    </tr>
@endforeach

@if ($Coaches->isEmpty())
    <tr>
        <td colspan="5" class="text-center table-warning text-warning  fw-bold rounded-pill">No records found</td>
    </tr>
@endif
