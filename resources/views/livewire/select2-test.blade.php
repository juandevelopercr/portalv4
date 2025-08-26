<div>
    <div>
        <label for="department_id">Departamento</label>
        <select wire:model="department_id" id="department_id" class="form-control select2">
            <option value="">Seleccione un departamento</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}">
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mt-3">
        <label for="bank_id">Banco</label>
        <select wire:model="bank_id" id="bank_id" class="form-control select2">
            <option value="">Seleccione un banco</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}">
                    {{ $bank->name }}
                </option>
            @endforeach
        </select>
    </div>

    <button wire:click='test'>Save</button>
</div>

@include('livewire.components.select2-init')

@script()
<script>

    window.select2Config = {
        department_id: true, // dispara evento a Livewire
        bank_id: false       // solo select2 sin evento
    };

</script>
@endscript
