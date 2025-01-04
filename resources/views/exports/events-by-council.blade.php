<table align="left">
    <thead>
        <tr style="background-color: #106c3b; color: white">
            {{-- <th style="background-color: #106c3b; color: white;">Event ID</th> --}}
            <th style="background-color: #106c3b; color: white;">Title</th>
            <th style="background-color: #106c3b; color: white;">Description</th>
            <th style="background-color: #106c3b; color: white;">Start Time</th>
            <th style="background-color: #106c3b; color: white;">End Time</th>
            <th style="background-color: #106c3b; color: white;">Is Active</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($events as $event)
            <tr>
                {{-- <td align="left" width="40">{{ $event->id }}</td> --}}
                <td align="left" width="40">{{ $event->title ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $event->description ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $event->start_time ? $event->start_time->format('F j, Y, g:i A') : 'N/A' }}</td>
                <td align="left" width="40">{{ $event->end_time ? $event->end_time->format('F j, Y, g:i A') : 'N/A' }}</td>
                <td align="left" width="40">{{ $event->is_active ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
