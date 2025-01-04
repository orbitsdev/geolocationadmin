<table align="left">
    <thead>
        <tr style="background-color: #106c3b; color: white">
            <th style="background-color: #106c3b; color: white;">Collection ID</th>
            <th style="background-color: #106c3b; color: white;">Title</th>
            <th style="background-color: #106c3b; color: white;">Type</th>
            <th style="background-color: #106c3b; color: white;">Description</th>
            <th style="background-color: #106c3b; color: white;">Is Published</th>
            <th style="background-color: #106c3b; color: white;">Items</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($collections as $collection)
            <tr>
                <td align="left" width="40">{{ $collection->id }}</td>
                <td align="left" width="40">{{ $collection->title }}</td>
                <td align="left" width="40">{{ $collection->type }}</td>
                <td align="left" width="40">{{ $collection->description ?? 'N/A' }}</td>
                <td align="left" width="40">{{ $collection->is_publish ? 'Yes' : 'No' }}</td>
                <td align="left" width="40">
                    <ul>
                        @foreach ($collection->collectionItems as $item)
                            <li>
                                {{ $item->label }}: {{ number_format($item->amount, 2) }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
