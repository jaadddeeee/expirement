<table class="table table-striped datatable mb-4">
    <thead>
        <tr>
            <th>ID</th>
            <th>Employee ID</th>
            <th>Employee NAME</th>
            <th>SEX</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody class="table-border-bottom-0" id="employee-table-body">
        @php $id = 1; @endphp
        @if ($employee->isEmpty())
            <tr>
                <td colspan="5" class="text-center">No data found</td>
            </tr>
        @else
            @foreach ($employee as $employees)
                <tr>
                    <td>{{ $id++ }}</td>
                    <td>{{ $employees->AgencyNumber ? $employees->AgencyNumber : 'N/A' }}</td>
                    <td>
                        {{ $employees->FirstName }}
                        {{ $employees->MiddleName ? Str::substr($employees->MiddleName, 0, 1) . '.' : '' }}
                        {{ $employees->LastName }}
                    </td>
                    <td>{{ $employees->Sex ? Str::substr(AES::decrypt($employees->Sex), 0, 1) : 'N/A' }}</td>
                    <td>
                        <a class="btn btn-transparent btn-sm"
                            href="{{ route('emp_process-id', ['emid' => Crypt::encryptString($employees->id)]) }}">
                            <i class="bx bxs-id-card me-2 text-primary fs-3"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div class="container mt-5">
    <div id="pagination" class="d-flex justify-content-end">
        {{ $employee->appends(request()->query())->links() }}
    </div>
</div>
