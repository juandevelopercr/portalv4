@extends('layouts.blankLayout')

@section('content')
<div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
        <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center p-sm-12 p-6">
            <div class="w-px-400 mx-auto">
                <h4 class="mb-2">Seleccionar Departamento</h4>
                <p class="mb-4">Rol: <strong>{{ $role->name }}</strong></p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('department-selection.select') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="department" class="form-label">Departamento</label>
                        <select class="form-select" id="department" name="department" required>
                            <option value="">Seleccione un departamento</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
