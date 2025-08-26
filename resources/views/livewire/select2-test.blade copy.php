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

@script()
<script>
  $(document).ready(function() {
    $('#department_id').select2();
    $('#bank_id').select2();

    $('#department_id').on('change', function() {
      let data = $(this).val();
      $wire.set('department_id', data, true);
      //$wire.department_id = data;
      @this.department_id = data;
      console.log(data);
    });

    $('#bank_id').on('change', function() {
      let data = $(this).val();
      $wire.set('bank_id', data, false);
      //$wire.bank_id = data;
      @this.bank_id = data;
      console.log(data);
    });


    window.initSelect2 = () => {
      $('#department_id').select2();
      $('#bank_id').select2();
      console.log("Se reinició el select2");
    }

    initSelect2();
    Livewire.on('select2', () => {
      setTimeout(() => {
        initSelect2();
      }, 200); // Retraso para permitir que el DOM se estabilice
    });

  })
</script>
@endscript