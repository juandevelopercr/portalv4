<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Users Report') }}</title>
    <!-- Vincula Bootstrap 5 para el formato de PDF -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .img-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="container my-4">
        <!-- Título del reporte -->
        <h3 class="text-center mb-4">{{ __('Users Report') }}</h3>

        <!-- Fecha del reporte -->
        <p class="text-end"><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>

        <!-- Tabla de usuarios -->
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Photo') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>
                        @php
                        $listaroles = $user->getRoleNames();
                        foreach ($listaroles as $r)
                        $roles = $r."<br>";
                        @endphp
                        <span class="emp_name text-truncate">{{ $user->name }}</span><br>
                        <small class="emp_post text-truncate text-muted">{!! $roles !!}</small>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->profile_photo_path)
                        @php
                        $path = public_path('storage/assets/img/avatars/' . $user->profile_photo_path);
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        @endphp
                        <img src="{{ $base64 }}" class="img-thumbnail" alt="Foto de Perfil">
                        @else
                        No disponible
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>