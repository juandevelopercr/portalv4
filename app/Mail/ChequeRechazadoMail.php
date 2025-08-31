<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChequeRechazadoMail extends Mailable
{
  use Queueable, SerializesModels;

  public $movimientos;

  public function __construct($movimientos)
  {
    $this->movimientos = $movimientos;
  }

  public function build()
  {
    return $this->subject('Actualización en estado de revisiones Pendientes/Rechazadas')
      ->view('emails.cheque_rechazado');
  }
}
