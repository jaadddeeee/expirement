@forelse ($scholars as $scholar)
    <tr>
        <td class="text-nowrap">{{ $loop->iteration }}</td>
        <td class="text-nowrap">{{ $scholar->LastName }}, {{ $scholar->FirstName }} {{ $scholar->MiddleName }}</td>
        <td class="text-nowrap">{{ $scholar->date_awarded }}</td>
        <td class="text-nowrap">
            {{ \GENERAL::setSchoolYearLabel($scholar->SchoolYear, $scholar->Semester) }}
        </td>
        <td class="text-nowrap">
            {{ \GENERAL::Semesters()[$scholar->Semester]['Long'] }}
        </td>
        <td class="text-nowrap">
            <!-- Edit Button Icon -->
            <i class="fa fa-edit text-warning me-2 editScholar" data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                style="cursor: pointer;" title="Edit"></i>

            <!-- Delete Button Icon -->
            <i class="fa fa-trash text-danger deleteScholar" data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                style="cursor: pointer;" title="Delete"></i>
        </td>
    </tr>
@empty
    @if (isset($isSearch) && $isSearch)
        <tr style="background-color:#033874; color: white;">
            <td colspan="6" class="text-center">No scholars found</td>
        </tr>
    @else
        <tr style="background-color:#033874; color: white;">
            <td colspan="6" class="text-center">No scholars available</td>
        </tr>
    @endif
@endforelse
