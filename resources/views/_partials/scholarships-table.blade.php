@forelse ($scholarships as $scholarship)
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $scholarship->sch_name }}</td>
        <td>
            {{ GENERAL::ScholarshipsNew()[$scholarship->sch_type]['Description'] ?? 'Unknown' }}
        </td>
        <td>
            @if ($scholarship->sch_type == 1)
                N/A
            @else
                {{ GENERAL::ExternalSchType()[$scholarship->ext_type]['Description'] ?? 'Unknown' }}
            @endif
        </td>
        <td>
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                    data-bs-toggle="dropdown" aria-expanded="false"><i class="bx bx-dots-vertical-rounded"></i></button>
                <div class="dropdown-menu">
                    <!-- Add Scholar -->
                    <a class="dropdown-item addScholarView"
                        data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                        data-scholarship-name="{{ $scholarship->sch_name }}" href="javascript:void(0);">
                        <i class="bx bx-show me-1"></i> View Scholars</a>

                    <!-- Edit Scholarship -->
                    <a class="dropdown-item editScholarship"
                        data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}" href="javascript:void(0);">
                        <i class="bx bx-edit-alt me-1"></i> Edit Scholarship</a>

                    <!-- Delete Scholarship -->
                    <a class="dropdown-item deleteScholarship"
                        data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}" href="javascript:void(0);">
                        <i class="bx bx-trash me-1"></i> Delete Scholarship</a>
                </div>
            </div>
        </td>
    </tr>
@empty
    @if (isset($isSearch) && $isSearch)
        <tr>
            <td colspan="5" class="text-center">No scholarships found</td>
        </tr>
    @else
        <tr>
            <td colspan="5" class="text-center">No scholarships available</td>
        </tr>
    @endif
@endforelse
