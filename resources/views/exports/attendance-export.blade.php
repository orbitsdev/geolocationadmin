<table align="left">
    <thead>
        <tr style="background-color: #106c3b; color: white">
            {{-- <th style="background-color: #106c3b; color: white;">ID</th> --}}
            <th style="background-color: #106c3b; color: white;">Name</th>
            <th style="background-color: #106c3b; color: white;">Position</th>
            <th style="background-color: #106c3b; color: white;">Event Name</th>
            <th style="background-color: #106c3b; color: white;">Time In</th>
            <th style="background-color: #106c3b; color: white;">Time Out</th>
            <th style="background-color: #106c3b; color: white;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($attendances as $attendance)
            <tr>
                {{-- <td align="left" width="40">{{ $attendance->id }}</td> --}}
                <td align="left" width="40">{{ $attendance?->councilPosition?->user?->fullName() ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $attendance?->councilPosition?->position ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $event?->title ?? 'N/A' }}</td>
                <td align="left" width="40">
                    {{ $attendance?->check_in_time ? $attendance->check_in_time->format('F j, Y, g:i A') : 'N/A' }}
                </td>
                <td align="left" width="40">
                    {{ $attendance?->check_out_time ? $attendance->check_out_time->format('F j, Y, g:i A') : 'N/A' }}
                </td>
                <td align="left" width="40">{{ ucfirst($attendance?->status ?? 'N/A') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
