<?php

namespace App\Livewire\Movimientos;

use App\Models\CatalogoCuenta;
use App\Models\CentroCosto;
use App\Models\MovimientoCentroCosto;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MovimientosCentroCosto extends Component
{
  public $movimiento_id;
  public $rows = [];
  public $centrosCostos;
  // Es para poner como valor si solo hay una fila de centro de costos y es el total_general del movimiento
  public $total_general;

  #[Computed()]
  public function listcentrosCosto()
  {
    return CentroCosto::orderBy('codigo', 'ASC')->get();
  }

  #[Computed()]
  public function listcatalogoCuentas()
  {
    return CatalogoCuenta::orderBy('codigo', 'ASC')->get();
  }

  public function mount($movimiento_id = null)
  {
    $this->movimiento_id = $movimiento_id;

    if ($this->movimiento_id) {
      $this->loadRows();
    }

    // Si no hay filas, crear una vacía
    if (empty($this->rows)) {
      $this->rows = [['centro_costo_id' => 21, 'codigo_contable_id' => 75, 'amount' => 0.00]];
    }
  }

  /*
  #[On('getTotalCentroCosto')]
  public function getTotalCentroCosto()
  {
    $total = collect($this->rows)
      ->pluck('amount')
      ->map(fn($a) => floatval($a))
      ->sum();

    $this->dispatch('respuestaTotalCentroCosto', $total);
  }

  #[On('updateTotalGeneral')]
  public function updateTotalGeneral($value)
  {
    $this->total_general = (float) $value;
    if (count($this->rows) == 1)
      $rows[0]['amount'] = $this->total_general;
  }
  */

  public function loadRows()
  {
    $this->rows = MovimientoCentroCosto::where('movimiento_id', $this->movimiento_id)
      ->get()
      ->map(function ($item) {
        return [
          'id' => $item->id,
          'centro_costo_id' => $item->centro_costo_id,
          'codigo_contable_id' => $item->codigo_contable_id,
          'amount' => number_format((float)$item->amount, 2, '.', ''),
        ];
      })
      ->toArray();
  }

  public function rules()
  {
    $rules = [];
    foreach ($this->rows as $index => $row) {
      $rules["rows.$index.centro_costo_id"] = 'required|exists:centro_costos,id';
      $rules["rows.$index.codigo_contable_id"] = 'required|exists:catalogo_cuentas,id';
      $rules["rows.$index.amount"] = 'required|numeric|min:0.01';
    }

    return $rules;
  }

  public function messages()
  {
    return [
      'rows.*.centro_costo_id.required' => 'El centro de costo es obligatorio.',
      'rows.*.codigo_contable_id.required' => 'El código contable es obligatorio.',
      'rows.*.amount.required' => 'El monto es obligatorio.',
      'rows.*.amount.numeric' => 'El monto debe ser un número.',
      'rows.*.amount.min' => 'El monto debe ser mayor o igual a 0.',
    ];
  }

  public function validationAttributes()
  {
    return [
      'rows.*.centro_costo_id' => 'centro de costo',
      'rows.*.codigo_contable_id' => 'código contable',
      'rows.*.amount' => 'monto',
    ];
  }

  public function addRow()
  {
    $this->rows[] = ['centro_costo_id' => '', 'codigo_contable_id' => '', 'amount' => ''];
  }

  public function removeRow($index)
  {
    if (isset($this->rows[$index]['id'])) {
      MovimientoCentroCosto::find($this->rows[$index]['id'])?->delete();
    }

    unset($this->rows[$index]);
    $this->rows = array_values($this->rows);
    $this->dispatch("refreshCalculaMontos");
  }

  #[On('save-centros-costo')]
  public function saveCentrosCosto($data)
  {
    $this->movimiento_id = $data['id'];
    $this->save(); // este método ya valida y guarda
    $this->loadRows();
  }

  public function save()
  {
    $filasValidas = collect($this->rows)->filter(
      fn($row) =>
      isset($row['centro_costo_id'], $row['codigo_contable_id'], $row['amount']) &&
        is_numeric($row['centro_costo_id']) &&
        is_numeric($row['codigo_contable_id'])  &&
        $row['amount'] > 0
    );

    if ($filasValidas->isEmpty()) {
      $this->addError('rows_valido', 'Debe agregar al menos un centro de costo completo.');
      $this->dispatch('centrosGuardadosFail');
      return;
    }

    try {
      $this->validate();

      foreach ($this->rows as $row) {
        //$row['amount'] = floatval(str_replace(',', '', $row['amount']));
        if (isset($row['id'])) {
          MovimientoCentroCosto::where('id', $row['id'])->update([
            'centro_costo_id' => $row['centro_costo_id'],
            'codigo_contable_id' => $row['codigo_contable_id'],
            'amount' => $row['amount'],
          ]);
        } else {
          MovimientoCentroCosto::create([
            'movimiento_id' => $this->movimiento_id,
            'centro_costo_id' => $row['centro_costo_id'],
            'codigo_contable_id' => $row['codigo_contable_id'],
            'amount' => $row['amount'],
          ]);
        }
      }

      $this->dispatch('centrosGuardadosOk');
    } catch (\Throwable $e) {
      $this->addError('rows_valido', 'Error al guardar: ' . $e->getMessage());
      $this->dispatch('centrosGuardadosFail');
    }
  }

  #[On('validar-centros-costo')]
  public function validarDesdePadre()
  {
    try {
      /*
      // 🔧 Limpiar separadores de miles en todos los amounts
      foreach ($this->rows as $i => $row) {
        if (isset($row['amount'])) {
          $this->rows[$i]['amount'] = floatval(str_replace(',', '', $row['amount']));
        }
      }
      */

      $this->validate(); // y cualquier validación manual adicional
      $this->dispatch('respuesta-validacion-centros', true);
    } catch (\Throwable $e) {
      // ✅ Mostrar mensaje exacto
      /*
      logger()->error('Error al validar centros de costo', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString(),
      ]);
      */
      $this->dispatch('respuesta-validacion-centros', false);
    }
  }

  public function getTotalAmountProperty()
  {
    return collect($this->rows)
      ->pluck('amount')
      ->map(fn($a) => floatval($a))
      ->sum();
  }

  public function render()
  {
    return view('livewire.movimientos.movimientos-centro-costo');
  }
}
