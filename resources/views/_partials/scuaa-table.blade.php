<!-- resources/views/partials/varsity-table.blade.php -->
@foreach ($Lists as $list)
    <tr class="varsity-row">
        <td class = "text-nowrap">{{ isset($ctr) ? ++$ctr : ($ctr = 1) }}</td>
        <td class="text-nowrap">
            {{ strtoupper($list->LastName . ', ' . $list->FirstName . (empty($list->MiddleName) ? '' : ' ' . $list->MiddleName[0] . '.')) }}
        </td>
        <td class="text-nowrap">
            {{-- Access the event relationship here --}}
            {{ $list->event_name }}
        </td>
        <td class="text-nowrap">
            {{ $list->SchoolYear }}
        </td>
        <td class="text-nowrap">
            {{-- <a href="#" class="editVarsity" cid="{{ Crypt::encryptString($varsity->id) }}">
                <i class="bx bx-edit text-warning"></i>
            </a> --}}
            &nbsp;
            <a href="#" class="deleteVarsity" cid="{{ Crypt::encryptString($list->StudentNo) }}">
                <i class="text-danger bx bx-trash"></i>
            </a>
        </td>
    </tr>
@endforeach

@if ($Lists->isEmpty())
    <tr>
        <td colspan="5" class="text-center table-warning text-warning  fw-bold rounded-pill">No records found</td>
    </tr>
@endif
