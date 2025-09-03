<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProformaMail extends Mailable
{
  use Queueable, SerializesModels;

  public $data;
  public $attachmentPaths;
  public $logo = '';

  /**
   * Create a new message instance.
   */
  public function __construct($data, $attachmentPaths = [])
  {
    $business = Business::find(1);
    $logoFileName = $business->logo;
    if ($logoFileName && file_exists(public_path("storage/assets/img/logos/{$logoFileName}"))) {
      $this->logo = asset("storage/assets/img/logos/{$logoFileName}");
    } else {
      $this->logo = asset("storage/assets/default-image.png");
    }
    $this->data = $data;
    $this->attachmentPaths = $attachmentPaths;
  }

  public function build()
  {
    $email = $this->subject($this->data['subject'])
      ->view('emails.proforma')
      ->with(['data' => $this->data, 'logo' => $this->logo]); // Asegúrate de que $this->message sea una cadena de texto

    foreach ($this->attachmentPaths as $file) {
      $email->attach($file['path'], [
        'as'   => $file['name'],
        'mime' => $file['mime'],
      ]);
    }
    return $email;
  }
}
