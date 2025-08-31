<table>
    <thead>
        <tr>
            <th>ID</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td>{{ $record->id }}</td>
        </tr>
        @endforeach
    </tbody>
</table>