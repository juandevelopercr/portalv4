<?php

namespace App\Livewire\Movimientos;

use App\Livewire\Transactions\TransactionManager;
use App\Models\DataTableConfig;
use App\Models\MovimientoFactura;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class MovimientosFacturas extends TransactionManager
{
  public $movimientoId;

  public $filters = [
    'filter_proforma_no' => NULL,
    'filter_consecutivo' => NULL,
    'filter_customer_name' => NULL,
    'filter_department_name' => NULL,
    'filter_user_name' => NULL,
    'filter_transaction_date' => NULL,
    'filter_fecha_solicitud_factura' => NULL,
    'filter_issuer_name' => NULL,
    'filter_codigosContables' => NULL,
    'filter_numero_caso' => NULL,
    'filter_referencia' => NULL,
    'filter_oc' => NULL,
    'filter_migo' => NULL,
    'filter_bank_name' => NULL,
    'filter_currency_code' => NULL,
    'filter_proforma_type' => NULL,
    'filter_status' => NULL,
    'filter_totalComprobante' => NULL,
    'filter_total_usd' => NULL,
    'filter_total_crc' => NULL,
    'filter_action' => NULL,
  ];

  public function mount()
  {
    $this->document_type = ['PR', 'FE', 'TE'];
    parent::mount();
    // Aquí puedes agregar lógica específica para proformas
  }

  public function refresDatatable()
  {
    $config = DataTableConfig::where('user_id', Auth::id())
      ->where('datatable_name', 'movimientos-facturas-datatable')
      ->first();

    if ($config) {
      // Verifica si ya es un array o si necesita decodificarse
      $columns = is_array($config->columns) ? $config->columns : json_decode($config->columns, true);
      $this->columns = array_values($columns); // Asegura que los índices se mantengan correctamente
      $this->perPage = $config->perPage  ?? 10; // Valor por defecto si viene null
    } else {
      $this->columns = $this->getDefaultColumns();
      $this->perPage = 10;
    }
  }

  public function getDefaultColumns(): array
  {
    $this->defaultColumns = [
      [
        'field' => 'proforma_no',
        'orderName' => 'proforma_no',
        'label' => __('No. Proforma'),
        'filter' => 'filter_proforma_no',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'consecutivo',
        'orderName' => 'consecutivo',
        'label' => __('Consecutive'),
        'filter' => 'filter_consecutivo',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'customer_name',
        'orderName' => 'contacts.name',
        'label' => __('Customer'),
        'filter' => 'filter_customer_name',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'department_name',
        'orderName' => 'departments.name',
        'label' => __('Department'),
        'filter' => 'filter_department_name',
        'filter_type' => 'select',
        'filter_sources' => 'departments',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'user_name',
        'orderName' => 'users.name',
        'label' => __('User'),
        'filter' => 'filter_user_name',
        'filter_type' => 'select',
        'filter_sources' => 'users',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'transaction_date',
        'orderName' => 'transactions.transaction_date',
        'label' => __('Emmision Date'),
        'filter' => 'filter_transaction_date',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'fecha_solicitud_factura',
        'orderName' => 'transactions.fecha_solicitud_factura',
        'label' => __('Application Date'),
        'filter' => 'filter_fecha_solicitud_factura',
        'filter_type' => 'date',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'date',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'issuer_name',
        'orderName' => 'business_locations.name',
        'label' => __('Issuer'),
        'filter' => 'filter_issuer_name',
        'filter_type' => 'select',
        'filter_sources' => 'issuers',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '<span class="emp_name text-truncate">',
        'closeHtmlTab' => '</span>',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'codigosContables',
        'orderName' => 'codigo_contables.codigo',
        'label' => __('Accounting Code'),
        'filter' => 'filter_codigosContables',
        'filter_type' => 'select',
        'filter_sources' => 'codigosContables',
        'filter_source_field' => 'descrip',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'numero_caso',
        'orderName' => '',
        'label' => __('Case Number'),
        'filter' => 'filter_numero_caso',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'referencia',
        'orderName' => '',
        'label' => __('Case/Reference'),
        'filter' => 'filter_referencia',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'oc',
        'orderName' => 'oc',
        'label' => __('O.C'),
        'filter' => 'filter_oc',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'migo',
        'orderName' => 'migo',
        'label' => __('MIGO'),
        'filter' => 'filter_migo',
        'filter_type' => 'input',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'bank_name',
        'orderName' => 'banks.name',
        'label' => __('Bank'),
        'filter' => 'filter_bank_name',
        'filter_type' => 'select',
        'filter_sources' => 'banks',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'currency_code',
        'orderName' => 'currencies.code',
        'label' => __('Currency'),
        'filter' => 'filter_currency_code',
        'filter_type' => 'select',
        'filter_sources' => 'currencies',
        'filter_source_field' => 'code',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'proforma_type',
        'orderName' => 'transactions.proforma_type',
        'label' => __('Type of Notarial Act'),
        'filter' => 'filter_proforma_type',
        'filter_type' => 'select',
        'filter_sources' => 'proformaTypes',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'status',
        'orderName' => 'transactions.proforma_status',
        'label' => __('Status'),
        'filter' => 'filter_status',
        'filter_type' => 'select',
        'filter_sources' => 'statusOptions',
        'filter_source_field' => 'name',
        'columnType' => 'string',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getHtmlStatus',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'totalComprobante',
        'orderName' => '',
        'label' => __('Total'),
        'filter' => 'filter_totalComprobante',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => '',
        'parameters' => [],
        'sumary' => 'tComprobante',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'total_usd',
        'orderName' => '',
        'label' => __('Total USD'),
        'filter' => 'filter_total_usd',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['USD', true],
        'sumary' => 'tComprobanteUsd',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'total_crc',
        'orderName' => '',
        'label' => __('Total CRC'),
        'filter' => 'filter_total_crc',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'decimal',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getTotalComprobante',
        'parameters' => ['CRC', true], // Parámetro a pasar a la función
        'sumary' => 'tComprobanteCrc',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ],
      [
        'field' => 'action',
        'orderName' => '',
        'label' => __('Actions'),
        'filter' => '',
        'filter_type' => '',
        'filter_sources' => '',
        'filter_source_field' => '',
        'columnType' => 'action',
        'columnAlign' => '',
        'columnClass' => '',
        'function' => 'getMovimientoFacturasHtmlColumnAction',
        'parameters' => [],
        'sumary' => '',
        'openHtmlTab' => '',
        'closeHtmlTab' => '',
        'width' => NULL,
        'visible' => true,
      ]
    ];

    return $this->defaultColumns;
  }

  protected function getFilteredQuery()
  {
    $query = Transaction::search($this->search, $this->filters)
      ->whereIn('document_type', $this->document_type)
      ->whereIn('transactions.id', function ($subquery) {
        $subquery->select('transaction_id')
          ->from('movimientos_facturas')
          ->where('movimiento_id', $this->movimientoId);
      });

    $allowedRoles = User::ROLES_ALL_DEPARTMENTS;
    if (in_array(Session::get('current_role_name'), $allowedRoles)) {
      $query->whereIn('proforma_status', [Transaction::FACTURADA, Transaction::RECHAZADA, Transaction::ANULADA]);
    } else {
      // Obtener departamentos y bancos de la sesión
      $departments = Session::get('current_department', []);

      // Filtrar por departamento y banco
      if (!empty($departments)) {
        $query->whereIn('transactions.department_id', $departments);
      }
      $query->whereIn('proforma_status', [Transaction::FACTURADA, Transaction::RECHAZADA, Transaction::ANULADA]);
    }

    return $query;
  }

  public function render()
  {
    $query = $this->getFilteredQuery();

    // Ordenamiento y paginación final
    $records = $query
      ->orderBy($this->sortBy, $this->sortDir)
      ->paginate($this->perPage);

    return view('livewire.movimientos.movimientos-facturas', [
      'records' => $records,
    ]);
  }

  public function beforedelete()
  {
    $this->confirmarAccion(
      null,
      'delete',
      '¿Está seguro que desea eliminar este registro?',
      'Después de confirmar, el registro será eliminado',
      __('Sí, proceed')
    );
  }

  #[On('deleteFacturaMovimiento')]
  public function delete($recordId)
  {
    try {
      // Verifica que el registro exista
      $transaction = Transaction::findOrFail($recordId);

      // Asegura que el campo se escriba correctamente: null en minúscula
      $transaction->fecha_deposito_pago = null;
      $transaction->numero_deposito_pago = null;
      $transaction->save();

      MovimientoFactura::where('transaction_id', $recordId)
        ->where('movimiento_id', $this->movimientoId)
        ->delete();

      $this->selectedIds = array_filter(
        $this->selectedIds,
        fn($selectedId) => $selectedId != $recordId
      );

      // Opcional: limpiar "seleccionar todo" si ya no aplica
      if (empty($this->selectedIds)) {
        $this->selectAll = false;
      }

      // Emitir actualización
      $this->dispatch('updateSelectedIds', $this->selectedIds);

      // Actualiza el saldo a cancelar del componente principal de movimiento
      $this->dispatch('updateSaldoCancelar');

      // Actualiza el listado de facturas no pagadas

      $this->dispatch('actualizarFacturasNoPagadas');
      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The record has been deleted')
      ]);
    } catch (\Illuminate\Database\QueryException $e) {
      if ($e->getCode() == '23000') {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The record cannot be deleted because it is related to other data.')
        ]);
      } else {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage()
        ]);
      }
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while deleting the record') . ' ' . $e->getMessage()
      ]);
    }
  }

  #[On('actualizarFacturasMovimientos')]
  public function refreshFacturas()
  {
    $this->resetPage(); // opcional: vuelve a la primera página
    $this->dispatch('$refresh'); // fuerza render
  }
}
