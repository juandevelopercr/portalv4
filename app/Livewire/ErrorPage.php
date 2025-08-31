<?php

namespace App\Livewire;

use Livewire\Component;

class ErrorPage extends Component
{
  public $code;
  public $message;
  public $image;
  public $buttonText;
  public $buttonAction;

  public function mount($code, $message, $buttonText = "Volver al inicio", $buttonAction = "/")
  {
    $this->code = $code;
    $this->message = $message;
    $this->buttonText = $buttonText;
    $this->buttonAction = $buttonAction;
    $this->setErrorImage();
  }

  protected function setErrorImage()
  {
    $errorImages = [
      403 => asset('images/errors/403.svg'),
      404 => asset('images/errors/404.svg'),
      419 => asset('images/errors/419.svg'),
      500 => asset('images/errors/500.svg'),
      503 => asset('images/errors/503.svg'),
    ];

    $this->image = $errorImages[$this->code] ?? asset('images/errors/generic.svg');
  }

  /*
  protected function setErrorImage()
  {
    $this->image = match ($this->code) {
      403 => asset('images/errors/403.svg'),
      404 => asset('images/errors/404.svg'),
      419 => asset('images/errors/419.svg'),
      500 => asset('images/errors/500.svg'),
      503 => asset('images/errors/503.svg'),
      default => asset('images/errors/generic.svg'),
    };
  }
   */

  public function render()
  {
    return view('livewire.error-page');
  }
}
