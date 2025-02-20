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
                        <a class="btn btn-transparent btn-sm"
                            href="{{ route('process-id', ['stuid' => Crypt::encryptString($students->StudentNo)]) }}">
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
        {{ $student->links() }}
    </div>
</div>
