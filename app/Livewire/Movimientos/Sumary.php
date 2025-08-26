<?php

namespace App\Livewire\Movimientos;

use App\Helpers\Helpers;
use App\Models\Cuenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class Sumary extends Component
{
  public $saldo_inicial_colones;
  public $saldo_inicial_dolares;

  public $debitos_colones;
  public $debitos_dolares;

  public $en_transito_colones;
  public $en_transito_dolares;

  public $creditos_colones;
  public $creditos_dolares;

  public $bloqueado_colones;
  public $bloqueado_dolares;

  public $saldo_final_colones;
  public $saldo_final_dolares;

  public $firstDate;
  public $lastDate;


  public function mount($date, $cuentas, $status)
  {
    $dataDate  = Helpers::getDateStartAndDateEnd($date, true);
    if (!empty($cuentas))
      $cuentasId = $cuentas;
    else
      $cuentasId = Cuenta::pluck('id')->toArray();
    $data = Helpers::calculaBalance($cuentasId, $dataDate, $status);
    $this->setData($data);
  }

  #[On('updateSummary')]
  public function updateSummary($data)
  {
    $cuentasId = $data['cuentasid'];
    $dataDate  = Helpers::getDateStartAndDateEnd($data['dateRange'], true);
    $status    = $data['status'];

    Log::debug('updateSummary recibido', $data);

    if (is_null($cuentasId) || empty($cuentasId)) {
      $cuentasId = Cuenta::pluck('id')->toArray();
    }

    $data = Helpers::calculaBalance($cuentasId, $dataDate, $status);

    $this->setData($data);
  }

  public function render()
  {
    return view('livewire.movimientos.sumary');
  }

  public function setdata($data)
  {
    $this->saldo_inicial_colones = $data['saldo_inicial_crc'];
    $this->saldo_inicial_dolares = $data['saldo_inicial_usd'];

    $this->debitos_colones = $data['debito_crc'];
    $this->debitos_dolares = $data['debito_usd'];

    $this->en_transito_colones = $data['transito_crc'];
    $this->en_transito_dolares = $data['transito_usd'];

    $this->creditos_colones = $data['credito_crc'];
    $this->creditos_dolares = $data['credito_usd'];

    $this->bloqueado_colones = $data['bloqueado_crc'];
    $this->bloqueado_dolares = $data['bloqueado_usd'];

    $this->saldo_final_colones = $data['saldo_final_crc'];
    $this->saldo_final_dolares = $data['saldo_final_usd'];

    $this->dispatch('saldoActualizado', [
      'saldoColones' => $this->saldo_final_colones,
      'saldoDolares' => $this->saldo_final_dolares
    ]);
  }
}
