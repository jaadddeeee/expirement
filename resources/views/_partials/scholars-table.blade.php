@forelse ($scholars as $scholar)
    <tr>
        <td>
            <input style="cursor: pointer;" type="checkbox" class="select-scholar form-check-input"
                name="selected_scholars[]" value="{{ $scholar->id }}">
        </td>
        <td>{{ $scholar->LastName }}, {{ $scholar->FirstName }} {{ $scholar->MiddleName }}</td>
        <td>
            {{ GENERAL::setSchoolYearLabel($scholar->SchoolYear, $scholar->Semester) }}
        </td>
        <td>
            {{ GENERAL::Semesters()[$scholar->Semester]['Long'] }}
        </td>
        <td>
            <!-- Edit Scholar -->
            <a class="editScholar me-2 text-warning" data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                href="javascript:void(0);" title="Edit Scholar">
                <i class="bx bx-edit-alt me-1"></i></a>

            <!-- Delete Scholar -->
            <a class="deleteScholar me-2 text-danger" data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                href="javascript:void(0);">
                <i class="bx bx-trash me-1"></i></a>

            <!-- Generate Scholarship Certification -->
            <a class="generateSCHCert text-secondary" data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                data-enrollment-id="{{ Crypt::encryptString($scholar->enrollment_id) }}"
                data-school-year="{{ $scholar->SchoolYear }}" data-semester="{{ $scholar->Semester }}"
                href="javascript:void(0);">
                <i class="bx bxs-file-pdf me-1"></i></a>

        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No scholars found.</td>
    </tr>
@endforelse
