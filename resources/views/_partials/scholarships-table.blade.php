@foreach ($scholarships as $scholarship)
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
            <i class="fa fa-plus-circle text-success me-2" style="cursor: pointer;" title="Add"></i>

            <!-- Edit Button Icon -->
            <i class="fa fa-edit text-warning me-2" style="cursor: pointer;"
                onclick="editScholar('{{ Crypt::encryptString($scholarship->id) }}')" title="Edit"></i>

            <!-- Delete Button Icon -->
            <i class="fa fa-trash text-danger" style="cursor: pointer;"
                onclick="deleteScholar('{{ $scholarship->id }}')" title="Delete"></i>
        </td>
    </tr>
@endforeach
