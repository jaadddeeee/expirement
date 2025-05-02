<table class="table table-sm table-hover">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Scholarship Name</th>
            <th>Acronym</th>
            <th>Type</th>
            <th>Provider</th>
            <th>Requirements</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($scholarships as $scholarship)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $scholarship->sch_name }}</td>
                <td>{{ $scholarship->sch_acronym }}</td>
                <td>
                    @if ($scholarship->sch_type == 1)
                        Internal
                    @else
                        External - {{ GENERAL::ExternalSchType()[$scholarship->ext_type]['Description'] ?? 'Unknown' }}
                    @endif
                </td>
                <td>{{ $scholarship->sch_provider }}</td>
                <td>
                    <!-- Add Requirements Modal -->
                    <a class="addRequirements me-2 text-success"
                        data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                        data-scholarship-name="{{ $scholarship->sch_name }}"
                        data-scholarship-acronym="{{ $scholarship->sch_acronym }}" href="javascript:void(0);"
                        title="Add Requirements">
                        <i class="bx bxs-plus-circle"></i>
                    </a>

                    <!-- Edit Requirements -->
                    <a class="editRequirements me-2 text-warning"
                        data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}" href="javascript:void(0);"
                        title="Edit Requirements">
                        <i class="bx bx-edit-alt"></i>
                    </a>
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input toggle-status" type="checkbox" style="cursor: pointer;"
                            data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                            {{ $scholarship->status ? 'checked' : '' }}>
                    </div>
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-primary btn-icon rounded-circle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="width: 25px; height: 25px;">
                            <i class="bx bx-dots-vertical-rounded fs-5"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <!-- View Scholars -->
                            <li>
                                <a class="dropdown-item viewScholars text-info"
                                    href="{{ route('scholars.index', ['id' => Crypt::encryptString($scholarship->id), 'scholarshipName' => $scholarship->sch_name]) }}"
                                    title="View Scholars" data-loading-text="Loading Scholars...">
                                    <i class="bx bx-show me-1"></i> View Scholars
                                </a>
                            </li>

                            <!-- Set Schedule for Releasing of Stipends -->
                            <li>
                                <a class="dropdown-item setScheduleRelease text-secondary"
                                    data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                                    href="javascript:void(0);" title="Release Stipend Schedule" data-bs-toggle="modal"
                                    data-bs-target="#setReleaseScheduleModal">
                                    <i class="bx bx-calendar me-1"></i> Set Release Schedule
                                </a>
                            </li>

                            <!-- Edit Scholarship -->
                            <li>
                                <a class="dropdown-item editScholarship text-warning"
                                    data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                                    href="javascript:void(0);" title="Edit Scholarship">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                            </li>

                            <!-- Delete Scholarship -->
                            <li>
                                <a class="dropdown-item deleteScholarship text-danger"
                                    data-scholarship-id="{{ Crypt::encryptString($scholarship->id) }}"
                                    href="javascript:void(0);" title="Delete Scholarship">
                                    <i class="bx bx-trash me-1"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No scholarships found.</td>
            </tr>
        @endforelse
    </tbody>
</table>


<!-- Pagination -->
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
        @if ($scholarships->firstItem() == null && $scholarships->lastItem() == null)
            <span class="me-2">Showing 0 entries</span>
        @else
            <span class="me-2">Showing {{ $scholarships->firstItem() }} to {{ $scholarships->lastItem() }} of
                {{ $scholarships->total() }} entries</span>
        @endif

        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                {{ $scholarships->links() }}
            </ul>
        </nav>
    </div>
</div>
