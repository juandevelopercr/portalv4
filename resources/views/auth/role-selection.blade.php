@extends('layouts.blankLayout')

@section('content')
@if ($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<div class="authentication-wrapper authentication-cover">
    <div class="authentication-inner row m-0">
        <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center p-sm-12 p-6">
            <div class="w-px-400 mx-auto">
                <h4 class="mb-2">Select Role</h4>
                <form method="POST" action="{{ route('role-selection.select') }}">
                    @csrf
                    @foreach($roles as $role)
                        <div class="mb-3">
                            <button
                                type="submit"
                                name="role"
                                value="{{ $role->id }}"
                                class="btn btn-outline-primary w-100"
                            >
                                {{ $role->name }}
                            </button>
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
        <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
            <div class="w-100 d-flex justify-content-center">
                <img src="{{ asset('assets/img/illustrations/boy-with-rocket-light.png') }}"
                     class="img-fluid" alt="Login image" width="700">
            </div>
        </div>
    </div>
</div>
@endsection
