<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProformaMail extends Mailable
{
  use Queueable, SerializesModels;

  public $data;
  public $attachmentPaths;

  /**
   * Create a new message instance.
   */
  public function __construct($data, $attachmentPaths = [])
  {
    $this->data = $data;
    $this->attachmentPaths = $attachmentPaths;
  }

  public function build()
  {
    $email = $this->subject($this->data['subject'])
      ->view('emails.proforma')
      ->with(['data' => $this->data]); // Asegúrate de que $this->message sea una cadena de texto

    foreach ($this->attachmentPaths as $file) {
      $email->attach($file['path'], [
        'as'   => $file['name'],
        'mime' => $file['mime'],
      ]);
    }
    return $email;
  }
}
