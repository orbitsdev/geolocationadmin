<table align="left">
    <thead>
        <tr style="background-color: #106c3b; color: white">
            {{-- <th style="background-color: #106c3b; color: white;">Task ID</th> --}}
            <th style="background-color: #106c3b; color: white;">Title</th>
            <th style="background-color: #106c3b; color: white;">Details</th>
            <th style="background-color: #106c3b; color: white;">Due Date</th>
            <th style="background-color: #106c3b; color: white;">Completed At</th>
            <th style="background-color: #106c3b; color: white;">Status</th>
            <th style="background-color: #106c3b; color: white;">Remarks</th>
            <th style="background-color: #106c3b; color: white;">Assigned To</th>
            <th style="background-color: #106c3b; color: white;">Approved By</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tasks as $task)
            <tr>
                {{-- <td align="left">{{ $task->id }}</td> --}}
                <td align="left" width="40">{{ $task->title }}</td>
                <td align="left" width="40">{{ $task->task_details ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $task->due_date ? $task->due_date->format('F j, Y, g:i A') : 'N/A' }}</td>
                <td align="left" width="40">{{ $task->completed_at ? $task->completed_at->format('F j, Y, g:i A') : 'N/A' }}</td>
                <td align="left" width="40">{{ ucfirst($task->status) }}</td>
                <td align="left" width="40">{{ $task->remarks ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $task->assignedCouncilPosition?->user?->fullName() ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $task->approvedByCouncilPosition?->user?->fullName() ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
