<?php

namespace App\Exports;

use App\Models\Contact;
use Carbon\Carbon;

class CustomersReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Nombre', 'field' => 'contact_name', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Nombre Comercial', 'field' => 'commercial_name', 'type' => 'string', 'align' => 'left', 'width' => 60],
      ['label' => 'Tipo de Identificación', 'field' => 'identification_type', 'type' => 'string', 'align' => 'left', 'width' => 20],
      ['label' => 'Identificación', 'field' => 'identification', 'type' => 'string', 'align' => 'left', 'width' => 20],
      ['label' => 'Correo Electrónico', 'field' => 'email', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Correo Electrónico de copia', 'field' => 'email_cc', 'type' => 'string', 'align' => 'left', 'width' => 100],
      ['label' => 'Condición de venta', 'field' => 'condition_sale', 'type' => 'string', 'align' => 'left', 'width' => 20],
      ['label' => 'Fecha de creado', 'field' => 'created_at', 'type' => 'date', 'align' => 'center', 'width' => 15],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Contact::query()
      ->join('identification_types', 'contacts.identification_type_id', '=', 'identification_types.id')
      ->join('condition_sales', 'contacts.condition_sale_id', '=', 'condition_sales.id')
      ->select(
        'contacts.id',
        'contacts.name as contact_name',
        'contacts.commercial_name',
        'contacts.identification',
        'identification_types.name as identification_type',
        'contacts.email',
        'contacts.email_cc',
        'condition_sales.name as condition_sale',
        'contacts.created_at'
      );
    return $query;
  }
}
