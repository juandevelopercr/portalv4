<?php

namespace App\Livewire\Transactions;

use App\Mail\CustomEmail;
use App\Mail\ProformaMail;
use App\Models\Proforma;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Helpers\Helpers;

class SendEmailModal extends Component
{
  public $showModal = false;
  public $transactionId;
  //public $transaction;
  public $recipientEmail;
  public $recipientName;
  public $fromEmail;
  public $ccEmails;
  public $subject;
  public $message;
  public $type;
  public $filename;
  public $is_proforma = false;

  public $canview;
  public $cancreate;
  public $canedit;
  public $candelete;
  public $canexport;
  public $documentType;
  public $has_documents = false;

  protected $listeners = ['openEmailModal'];

  public function mount($documentType, $canview, $cancreate, $canedit, $candelete, $canexport)
  {
    $this->documentType = $documentType;
    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;
  }

  public function openEmailModal($transactionId)
  {
    $this->resetErrorBag(); // Limpia los errores de validación previos
    $this->resetValidation(); // También puedes reiniciar los valores previos de val
    $this->transactionId = $transactionId['transactionId'];
    $transaction = Transaction::where('id', $this->transactionId)->first();

    if ($transaction) {
      //$this->fromEmail = !is_null($transaction->location) ? trim($transaction->location->email) : '';
      $this->recipientEmail = $transaction->contact->email;
      $this->recipientName = $transaction->contact->name;
      $this->ccEmails = $transaction->email_cc;

      $this->has_documents = $transaction->documents->isNotEmpty();

      $this->is_proforma = (empty($transaction->key) || empty($transaction->consecutivo));
      if ($this->is_proforma) {
        $this->type = 'FE'; // We keep FE for the helper to call the right method, but we will change labels in blade
      } else {
        $this->type = 'FE';
      }

      $prefijo_nombre = '';
      $prefijo_asunto = '';
      $titulo = '';

      if ($this->documentType == 'PROFORMA' || $this->documentType == 'COTIZACION') {
        if ($transaction->proforma_status == Transaction::FACTURADA) {
          $consecutivo = $transaction->consecutivo;
          $prefijo_asunto = 'Factura';
        } else {
          $consecutivo = $transaction->proforma_no;
          $prefijo_asunto = 'Proforma';
        }

        $titulo = $transaction->customer_name;

        $this->subject = $prefijo_asunto . ' No.' . $consecutivo . '-' . $titulo;
        $this->message = "Estimado/a " . $this->recipientName . ",\n\nAdjunto encontrará la proforma con los detalles solicitados.\n\nSaludos cordiales.";
      } else {
        // Documento electrónico
        $titulo = $transaction->customer_name;

        $typeComprobante = Helpers::getPdfTitle($transaction->document_type, $transaction);
        $consecutivo = (empty($transaction->key) || empty($transaction->consecutivo)) ? $transaction->proforma_no : $transaction->consecutivo;

        $subject = $typeComprobante . ' No.' . $consecutivo . '-' . $titulo;

        $this->subject = $subject;
        if ($this->is_proforma) {
          $this->message = "Estimado/a " . $this->recipientName . ",\n\nAdjunto encontrará la proforma con los detalles solicitados.\n\nSaludos cordiales.";
        } else {
          $this->message = "Estimado/a " . $this->recipientName . ",\n\nAdjunto encontrará los documentos asociados.\n\nSaludos cordiales.";
        }
      }

      $this->fromEmail = env('MAIL_USERNAME');
    }

    $this->showModal = true;
  }

  public function sendEmail()
  {
    $this->validate([
      'fromEmail'      => 'required|email',
      'recipientEmail' => 'required|email',
      'ccEmails'       => 'nullable|string',
      'subject'        => 'required|string|max:255',
      'message'        => 'required|string',
      'type'           => 'required|string',
    ]);

    $attachments = [];

    if ($this->type == 'PRS' || $this->type == 'PRD') {
      $type = ($this->type == 'PRS') ? 'sencillo' : 'detallado';
      $filePdf = Helpers::generateProformaPdf($this->transactionId, $type, 'file');
      $transaction = Transaction::find($this->transactionId);
      $attachments[] = [
        'path' => $filePdf,
        'name' => ($this->filename ?? 'proforma_' . $transaction->proforma_no) . '.pdf',
        'mime' => 'application/pdf',
      ];
    } else {
      // FE/Invoice logic
      $transaction = Transaction::find($this->transactionId);
      $filePathPdf = Helpers::generateComprobanteElectronicoPdf($this->transactionId, 'file');
      $attachments[] = [
        'path' => $filePathPdf,
        'name' => ($this->is_proforma ? 'proforma_' . $transaction->proforma_no : $transaction->key) . '.pdf',
        'mime' => 'application/pdf',
      ];

      // Only attach XMLs if it's NOT a proforma (i.e., it has key and consecutivo)
      if (!$this->is_proforma) {
        // 2. Adjuntar XML de factura
        $filePathXml = Helpers::generateComprobanteElectronicoXML($transaction, false, 'file');
        $attachments[] = [
          'path' => $filePathXml,
          'name' => $transaction->key . '.xml',
          'mime' => 'application/xml',
        ];

        // 3. Adjuntar XML de respuesta de Hacienda
        $xmlDirectory = storage_path("app/public/");
        $xmlResponsePath = $xmlDirectory . $transaction->response_xml;

        if (file_exists($xmlResponsePath)) {
          $filenameResponse = $transaction->key . '_respuesta.xml';
          $attachments[] = [
            'path' => $xmlResponsePath,
            'name' => $filenameResponse,
            'mime' => 'application/xml',
          ];
        }
      }
    }

    // Obtener los documentos adjuntos
    $transaction = Transaction::where('id', $this->transactionId)->first();
    $mediaAttachments = $transaction->media
      ->filter(fn($media) => $media->getCustomProperty('attach_to_email', false) === true)
      ->map(fn($media) => [
        'path' => $media->getPath(), // Ruta del archivo
        //'name' => $media->file_name, // Nombre del archivo
        'name' => Str::slug($media->name) . '.' . pathinfo($media->file_name, PATHINFO_EXTENSION), // Nombre del archivo
        'mime' => $media->mime_type, // Tipo MIME
      ])
      ->values() // Esto reinicia las claves del array
      ->toArray();

    // **Fusionamos los adjuntos sin sobrescribir `$attachments`**
    $attachments = array_merge($attachments, $mediaAttachments);

    $data = [
      'id'      => $this->transactionId,
      'from'    => $this->fromEmail,
      'nombre'  => $this->recipientName,
      'subject' => $this->subject,
      'message' => $this->message,
      'type'    => $this->type,
    ];

    // Procesar múltiples CCs separados por , o ;
    $rawCcList = collect(preg_split('/[,;]+/', $this->ccEmails ?? ''))
      ->map(fn($email) => trim($email))
      ->filter(fn($email) => $email !== '');

    $invalidCcEmails = $rawCcList
      ->reject(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
      ->toArray();

    $ccList = $rawCcList
      ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
      ->unique()
      ->toArray();

    try {
      // Enviar el correo con los archivos adjuntos
      $mail = Mail::to($this->recipientEmail);

      if (!empty($ccList)) {
        $mail->cc($ccList);
      }

      $mail->send(new ProformaMail($data, $attachments));

      $this->dispatch('show-notification', [
        'type' => 'success',
        'message' => __('The email has been sent successfully')
      ]);

      if (count($invalidCcEmails)) {
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The following CC emails are invalid:') . ' ' . implode(', ', $invalidCcEmails),
        ]);
        return; // Detener el envío
      }
    } catch (\Exception $e) {
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while sending the email:') . ' ' . $e->getMessage()
      ]);
      Log::error('Error sending email: ' . $e->getMessage());
    }

    $this->showModal = false;
  }

  public function render()
  {
    return view('livewire.transactions.send-email-modal');
  }
}
