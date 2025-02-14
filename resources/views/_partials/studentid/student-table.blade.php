<table class="table table-striped datatable mb-4">
    <thead>
        <tr>
            <th>ID</th>
            <th>STUDENT ID</th>
            <th>STUDENT NAME</th>
            <th>SEX</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody class="table-border-bottom-0">
        @php $id = 1; @endphp
        @if ($student->isEmpty())
            <tr>
                <td colspan="5" class="text-center">No data found</td>
            </tr>
        @else
            @foreach ($student as $students)
                <tr>
                    <td>{{ $id++ }}</td>
                    <td>{{ $students->StudentNo }}</td>
                    <td>
                        {{ $students->FirstName }}
                        {{ $students->MiddleName ? Str::substr($students->MiddleName, 0, 1) . '.' : '' }}
                        {{ $students->LastName }}
                    </td>
                    <td>{{ $students->Sex ? Str::substr($students->Sex, 0, 1) : '' }}</td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item d-flex align-items-center"
                                    href="{{ route('process-id', ['stuid' => Crypt::encryptString($students->StudentNo)]) }}">
                                    <i class="bx bxs-id-card me-2"></i>
                                    <span>Process ID</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div class="container">
    <div id="pagination" class="d-flex justify-content-end">
        {{ $student->links() }}
    </div>
</div>
