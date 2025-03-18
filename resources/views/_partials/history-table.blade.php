<!-- resources/views/partials/varsity-table.blade.php -->
@foreach ($ScuaaLists as $list)
    <tr class="varsity-row">
        <td class="text-nowrap">{{ isset($ctr) ? ++$ctr : ($ctr = 1) }}</td> <!-- Left-aligned with padding -->
        <td class="text-nowrap"> <!-- Left-aligned with padding -->
            <a href="" class="text-decoration-none text-gray">
                {{ strtoupper($list->Title) }}
                {{ preg_match('/\d{4}/', $list->Date, $matches) ? $matches[0] : '' }}
            </a>
        </td>
    </tr>
@endforeach

@if ($ScuaaLists->isEmpty())
    <tr>
        <td colspan="5" class="text-center table-warning text-warning fw-bold rounded-pill">No records found</td>
    </tr>
@endif
