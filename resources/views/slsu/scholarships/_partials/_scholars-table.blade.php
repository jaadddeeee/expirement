@if ($scholars->isEmpty())
    <div class="text-center py-5">
        <p class="text-muted mb-0">
            @if (request('filterSchoolYear') && request('filterSemester'))
                No scholars found for the selected filters.
            @else
                Please select a school year and semester to view scholars.
            @endif
        </p>
    </div>
@else
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <td style="width: 5px;">
                    <input style="cursor: pointer;" class="form-check-input" type="checkbox" id="selectAllScholars">
                </td>
                <th>Scholar Name</th>
                <th>Student No.</th>
                <th>Course</th>
                <th>School Year</th>
                <th>Semester</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($scholars as $scholar)
                <tr>
                    <td>
                        <input style="cursor: pointer;" type="checkbox" class="select-scholar form-check-input"
                            name="selected_scholars[]" value="{{ $scholar->id }}"
                            data-school-year="{{ $scholar->SchoolYear }}" data-semester="{{ $scholar->Semester }}">
                    </td>
                    <td>{{ $scholar->LastName }}, {{ $scholar->FirstName }} {{ $scholar->MiddleName }}</td>
                    <td>{{ $scholar->StudentNo }}</td>
                    <td>{{ $scholar->Course }}&nbsp;-&nbsp;{{ $scholar->StudentYear }}</td>
                    <td>
                        {{ GENERAL::setSchoolYearLabel($scholar->SchoolYear, $scholar->Semester) }}
                    </td>
                    <td>
                        {{ GENERAL::Semesters()[$scholar->Semester]['Long'] }}
                    </td>
                    <td>
                        <div class="dropdown">
                            <button
                                class="btn btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                style="width: 25px; height: 25px;">
                                <i class="bx bx-dots-vertical-rounded fs-6"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <!-- Edit Scholar -->
                                <li>
                                    <a class="dropdown-item editScholar text-warning"
                                        data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                                        href="javascript:void(0);" title="Edit Scholar">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                </li>

                                <!-- Delete Scholar -->
                                <li>
                                    <a class="dropdown-item deleteScholar text-danger"
                                        data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                                        href="javascript:void(0);" title="Delete Scholar">
                                        <i class="bx bx-trash me-1"></i> Delete
                                    </a>
                                </li>

                                <!-- Generate Scholarship Certificate -->
                                <li>
                                    <a class="dropdown-item generateSCHCert text-secondary"
                                        data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                                        data-enrollment-id="{{ Crypt::encryptString($scholar->enrollment_id) }}"
                                        data-school-year="{{ $scholar->SchoolYear }}"
                                        data-semester="{{ $scholar->Semester }}" href="javascript:void(0);"
                                        title="Generate Scholarship Certificate">
                                        <i class="bx bxs-file-pdf me-1"></i> SCH Certificate
                                    </a>
                                </li>

                                <!-- Generate Scholarship Profile Form -->
                                <li>
                                    <a class="dropdown-item generateSCHProfileForm text-secondary"
                                        data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                                        data-enrollment-id="{{ Crypt::encryptString($scholar->enrollment_id) }}"
                                        data-school-year="{{ $scholar->SchoolYear }}"
                                        data-semester="{{ $scholar->Semester }}" href="javascript:void(0);"
                                        title="Generate Profile Form">
                                        <i class="bx bxs-file-pdf me-1"></i> SCH Profile Form
                                    </a>
                                </li>

                                <!-- Generate Scholarship Application Form -->
                                <li>
                                    <a class="dropdown-item generateSCHApplicationForm text-secondary"
                                        data-scholar-id="{{ Crypt::encryptString($scholar->id) }}"
                                        data-enrollment-id="{{ Crypt::encryptString($scholar->enrollment_id) }}"
                                        data-school-year="{{ $scholar->SchoolYear }}"
                                        data-semester="{{ $scholar->Semester }}" href="javascript:void(0);"
                                        title="Generate Application Form">
                                        <i class="bx bxs-file-pdf me-1"></i> SCH Application Form
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No scholars found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination and Entries Per Page -->
    <div class="pagination-container d-flex justify-content-between align-items-center mt-4 mb-2">
        <div>
            <label for="entriesPerPage" class="me-2">Rows per page:</label>
            <select id="entriesPerPage" class="form-select form-select-sm d-inline-block w-auto">
                <option value="10" {{ request('entriesPerPage') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('entriesPerPage') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('entriesPerPage') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('entriesPerPage') == 100 ? 'selected' : '' }}>100</option>
                <option value="200" {{ request('entriesPerPage') == 200 ? 'selected' : '' }}>200</option>
                <option value="250" {{ request('entriesPerPage') == 250 ? 'selected' : '' }}>250</option>
            </select>
        </div>

        <div class="d-flex align-items-center">
            @if ($scholars->firstItem() == null && $scholars->lastItem() == null)
                <span class="me-2">Showing 0 entries</span>
            @else
                <span class="me-2">Showing {{ $scholars->firstItem() }} to {{ $scholars->lastItem() }} of
                    {{ $scholars->total() }} entries</span>
            @endif

            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    {{ $scholars->appends(request()->query())->links() }}
                </ul>
            </nav>
        </div>
    </div>
@endif



<style>
    .highlight {
        background-color: #f0f8ff;
    }
</style>
