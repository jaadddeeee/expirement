<table class="table table-sm table-hover">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Scholarship Name</th>
            <th>Acronym</th>
            <th>Type</th>
            <th>Provider</th>
            <th>Requirements</th>
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
                    <a class="addRequirements me-2 text-success" data-scholarship-id="{{ $scholarship->id }}"
                        data-scholarship-name="{{ $scholarship->sch_name }}"
                        data-scholarship-acronym=" {{ $scholarship->sch_acronym }}" href="javascript:void(0);"
                        title="Add Requirements">
                        <i class="bx bxs-plus-circle"></i>
                    </a>

                    <!-- Edit Requirements -->
                    <a class="editRequirements me-2 text-warning" data-scholarship-id="{{ $scholarship->id }}"
                        href="javascript:void(0);" title="Edit Requirements">
                        <i class="bx bx-edit-alt"></i>
                    </a>
                </td>
                <td>
                    <!-- View Scholars -->
                    <a class="viewScholars me-2 text-info"
                        href="{{ route('scholars.index', ['id' => Crypt::encryptString($scholarship->id), 'scholarshipName' => $scholarship->sch_name]) }}"
                        title="View Scholars">
                        <i class="bx bx-show"></i>
                    </a>

                    <!-- Edit Scholarship -->
                    <a class="editScholarship me-2 text-warning" data-scholarship-id="{{ $scholarship->id }}"
                        href="javascript:void(0);" title="Edit Scholarship">
                        <i class="bx bx-edit-alt"></i>
                    </a>

                    <!-- Delete Scholarship -->
                    <a class="deleteScholarship text-danger" data-scholarship-id="{{ $scholarship->id }}"
                        href="javascript:void(0);" title="Delete Scholarship">
                        <i class="bx bx-trash"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No scholarships found.</td>
            </tr>
        @endforelse
    </tbody>
</table>


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
