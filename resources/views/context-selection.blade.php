<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Seleccionar Contexto de Trabajo</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('set-context') }}">
                @csrf

                <!-- Selección de Rol -->
                <div class="mb-4">
                    <label class="form-label h5">Rol</label>
                    <div class="row">
                        @foreach($roles as $role)
                        <div class="col-md-4 mb-3">
                            <div class="form-check card card-hover">
                                <input class="form-check-input" type="radio"
                                       name="role_id" id="role-{{ $role->id }}"
                                       value="{{ $role->id }}">
                                <label class="form-check-label card-body" for="role-{{ $role->id }}">
                                    <h5 class="card-title">{{ $role->name }}</h5>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Selección de Departamento -->
                <div class="mb-4">
                    <label class="form-label h5">Departamento</label>
                    <div class="row">
                        @foreach($departments as $department)
                        <div class="col-md-4 mb-3">
                            <div class="form-check card card-hover">
                                <input class="form-check-input" type="radio"
                                       name="department_id" id="dept-{{ $department->id }}"
                                       value="{{ $department->id }}">
                                <label class="form-check-label card-body" for="dept-{{ $department->id }}">
                                    <h5 class="card-title">{{ $department->name }}</h5>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    Continuar
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.card-hover:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
</style>
