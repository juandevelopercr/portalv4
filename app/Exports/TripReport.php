<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Transaction;
use App\Models\TransactionCommission;

class TripReport extends BaseReport
{
  protected function columns(): array
  {
    return [
      ['label' => 'ID', 'field' => 'id', 'type' => 'integer', 'align' => 'left', 'width' => 10],
      ['label' => 'Tipo', 'field' => 'type', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Cliente', 'field' => 'company_name', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Fecha del viaje', 'field' => 'date_service', 'type' => 'string', 'align' => 'center', 'width' => 25],
      ['label' => 'Ciudad', 'field' => 'town_name', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Lugar de recogida', 'field' => 'pick_up', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Lugar de entrega', 'field' => 'destination', 'type' => 'string', 'align' => 'left', 'width' => 40],
      ['label' => 'Número de factura', 'field' => 'bill_number', 'type' => 'string', 'align' => 'left', 'width' => 30],
      ['label' => 'Número de pasajeros', 'field' => 'pax', 'type' => 'string', 'align' => 'left', 'width' => 15],
      ['label' => 'Cliente', 'field' => 'customer_name', 'type' => 'string', 'align' => 'left', 'width' => 20],
      ['label' => 'Rack Price', 'field' => 'rack_price', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Costo neto', 'field' => 'net_cost', 'type' => 'decimal', 'align' => 'right', 'width' => 20],
      ['label' => 'Estado', 'field' => 'status', 'type' => 'string', 'align' => 'left', 'width' => 20],
      ['label' => 'Comentarios', 'field' => 'others', 'type' => 'string', 'align' => 'left', 'width' => 60],
    ];
  }

  public function query(): \Illuminate\Database\Eloquent\Builder
  {
    $query = Trip::query()
        ->selectRaw("
            trips.id,
            contact_id,
            contacts.name as company_name,
            contacts.commercial_name as commercial_name,
            customer_name,
            consecutive,
            trips.contacto,
            trips.town_id,
            towns.name as town_name,
            trips.type,
            trips.pick_up,
            trips.destination,
            trips.bill_number,
            trips.pax,
            trips.rack_price,
            trips.net_cost,
            trips.date_service,
            trips.others,
            trips.status,
            trips.deleted_at
        ")
        ->join('contacts', 'trips.contact_id', '=', 'contacts.id')
        ->join('towns', 'trips.town_id', '=', 'towns.id')
        ->orderBy('trips.date_service', 'DESC');

    if (!empty($this->filters['filter_type'])) {
        $query->where('trips.type', '=', $this->filters['filter_type']);
    }

    if (!empty($this->filters['filter_status'])) {
        $query->where('trips.status', '=', $this->filters['filter_status']);
    }

    if (!empty($this->filters['filter_contact'])) {
        $query->where('trips.contact_id', '=', $this->filters['filter_contact']);
    }

    if (!empty($this->filters['filter_town'])) {
        $query->where('trips.town_id', '=', $this->filters['filter_town']);
    }

    if (!empty($this->filters['filter_date'])) {
        $range = explode(' to ', $this->filters['filter_date']);
        try {
            if (count($range) === 2) {
                $start = Carbon::createFromFormat('d-m-Y', trim($range[0]))->startOfDay();
                $end   = Carbon::createFromFormat('d-m-Y', trim($range[1]))->endOfDay();
                $query->whereBetween('trips.date_service', [$start, $end]);
            } else {
                $singleDate = Carbon::createFromFormat('d-m-Y', trim($this->filters['filter_date']));
                $query->whereDate('trips.date_service', $singleDate->format('Y-m-d'));
            }
        } catch (\Exception $e) {
            // manejar error
        }
    }

    return $query;
  }

}
