@forelse ($scholarships as $scholarship)
    <tr>
        <td class="text-nowrap">{{ $loop->iteration }}</td>
        <td class="text-nowrap">{{ $scholarship->sch_name }}</td>
        <td class="text-nowrap">
            {{ GENERAL::ScholarshipsNew()[$scholarship->sch_type]['Description'] ?? 'Unknown' }}
        </td>
        <td class="text-nowrap">
            @if ($scholarship->sch_type == 1)
                N/A
            @else
                {{ GENERAL::ExternalSchType()[$scholarship->ext_type]['Description'] ?? 'Unknown' }}
            @endif
        </td>
        <td class="text-nowrap">
            <!-- Add Button Icon -->
            <i class="fa fa-plus-circle text-success me-2 addScholarView"
                data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                data-scholarship-name="{{ $scholarship->sch_name }}" style="cursor: pointer;" title="Add"></i>

            <!-- Edit Button Icon -->
            <i class="fa fa-edit text-warning me-2 editScholarship"
                data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}" style="cursor: pointer;"
                title="Edit"></i>

            <!-- Delete Button Icon -->
            <i class="fa fa-trash text-danger deleteScholarship"
                data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}" style="cursor: pointer;"
                title="Delete"></i>
        </td>
    </tr>
@empty
    @if (isset($isSearch) && $isSearch)
        <tr style="background-color:#033874; color: white;">
            <td colspan="5" class="text-center">No scholarships found</td>
        </tr>
    @else
        <tr style="background-color:#033874; color: white;">
            <td colspan="5" class="text-center">No scholarships available</td>
        </tr>
    @endif
@endforelse
