<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
        </tr>
        @endforeach
    </tbody>
</table>