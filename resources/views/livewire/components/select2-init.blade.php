
@once
@script()
<script>
  $(document).ready(function() {
    // select2Config se debe definir en el blade

    // Inicialización de cada Select2
    Object.keys(select2Config).forEach(function(id) {
      const $select = $('#' + id);

      if (!$select.length) return;

      // Aplicar select2 si no está aplicado ya
      if (!$select.hasClass('select2-hidden-accessible')) {
        $select.select2();
        /*
        $select.select2({
          minimumResultsForSearch: 2,
          allowClear: true,
          width: '100%'
        });
        */
      }

      // Conexión con Livewire si se especifica
      if (select2Config[id] === true) {
        $select.off('change').on('change', function() {
          const value = $(this).val();
          console.log(`✨ #${id} => ${value}`);
          if (typeof $wire !== 'undefined') {
            $wire.set(id, value, true);
            $wire.id = value;
          }
        });
      }
      else{
        $select.off('change').on('change', function() {
          const value = $(this).val();
          console.log(`✨ #${id} => ${value}`);
          if (typeof $wire !== 'undefined') {
            $wire.set(id, value, false);
            $wire.id = value;
          }
        });
      }


    });

    // Funcíon global para reinicializar tras Livewire render
    window.initSelect2 = () => {
      Object.keys(select2Config).forEach(function(id) {
        const $select = $('#' + id);
        if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
          $select.select2();
          /*
          $select.select2({
            minimumResultsForSearch: 2,
            allowClear: true,
            width: '100%'
          });
          */
        }
      });
      console.log("🔁 Select2 reinicializado por Livewire");
    }

    Livewire.on('select2', () => {
      console.log("Se inicializa select2");
      setTimeout(() => {
        initSelect2();
      }, 200);
    });

  });
</script>
@endscript
@endonce
