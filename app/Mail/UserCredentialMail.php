<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Email;

class UserCredentialMail extends Mailable
{
  use Queueable, SerializesModels;

  public $data;

  /**
   * Create a new message instance.
   */
  public function __construct($data)
  {
    $this->data = $data;
  }

  public function build()
  {
    $logoPath = $this->data['logo_path'];

    if (!file_exists($logoPath)) {
      $this->data['logo_path'] = false;
    }

    return $this->subject($this->data['subject'])
      ->view('emails.user-credentials')
      ->with(['data' => $this->data]);
  }
}
