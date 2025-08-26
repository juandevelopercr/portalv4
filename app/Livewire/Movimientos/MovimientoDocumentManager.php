<?php

namespace App\Livewire\Movimientos;

use App\Models\Movimiento;
use Illuminate\Database\QueryException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MovimientoDocumentManager extends Component
{
  use WithFileUploads;

  //public $transaction;
  public $movimiento_id;
  public $file;
  public $title;
  public $documents = [];
  public $editingDocumentId = null;
  public $onlyview;

  public $canview;
  public $cancreate;
  public $canedit;
  public $candelete;
  public $canexport;

  protected $rules = [
    'file' => 'required|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:5120',  // 5MB max
    'title' => 'required|string|max:100',
  ];

  public function mount($movimiento_id, $onlyview = false, $canview, $cancreate, $canedit, $candelete, $canexport)
  {
    $this->movimiento_id = $movimiento_id;
    $this->onlyview = $onlyview;
    $this->canview = $canview;
    $this->cancreate = $cancreate;
    $this->canedit = $canedit;
    $this->candelete = $candelete;
    $this->canexport = $canexport;
    $this->loadDocuments();
  }

  public function render()
  {
    return view('livewire.movimientos.movimientos-documents-manager', [
      'canview' => $this->canview,
      'cancreate' => $this->cancreate,
      'canedit' => $this->canedit,
      'candelete' => $this->candelete,
      'canexport' => $this->canexport
    ]);
  }

  public function loadDocuments()
  {
    $movimiento = Movimiento::findOrFail($this->movimiento_id);
    $this->documents = $movimiento->getMedia('documents')->map(function ($doc) {
      return [
        'id' => $doc->id,
        'name' => $doc->name,
        'size' => $doc->size,
        'mime_type' => $doc->mime_type,
        'title' => $doc->getCustomProperty('title', ''),
        'created_at' => $doc->created_at->format('d/m/Y H:i'),
        'url' => $doc->getUrl(),
      ];
    })->toArray();
  }

  public function saveDocument()
  {
    $this->validate();

    try {
      $movimiento = Movimiento::findOrFail($this->movimiento_id);
      $media = $movimiento
        ->addMedia($this->file->getRealPath())
        ->usingName($this->title)
        ->toMediaCollection('documents');

      $media->setCustomProperty('title', $this->title);
      $media->save();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);
    } catch (FileUnacceptableForCollection $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('The file type is not accepted') . ' ' . $e->getMessage()]);
    } catch (\Exception $e) {
      $this->dispatch('show-notification', ['type' => 'error', 'message' => __('An error occurred while updating the registro') . ' ' . $e->getMessage()]);
    }

    // Resetear formulario
    $this->reset(['file', 'title']);
    $this->loadDocuments();
  }

  public function editDocument($documentId)
  {
    $movimiento = Movimiento::findOrFail($this->movimiento_id);
    $document = $movimiento->getMedia('documents')->find($documentId);

    if ($document) {
      $this->editingDocumentId = $documentId;
      $this->title = $document->getCustomProperty('title', $document->name);
    }
  }

  public function updateDocument()
  {
    if ($this->editingDocumentId) {
      $document = Media::find($this->editingDocumentId);
      $document->name = $this->title;
      $document->setCustomProperty('title', $this->title);
      $document->save();

      $this->editingDocumentId = null;
      $this->reset(['title']);
      $this->loadDocuments();

      $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been updated')]);
    }
  }

  public function confirmarAccion($recordId, $metodo, $titulo, $mensaje, $textoBoton)
  {
    // static::getName() devuelve automáticamente el nombre del componente Livewire actual, útil para dispatchTo.
    $this->dispatch('show-confirmation-dialog', [
      'recordId' => $recordId,
      'componentName' => static::getName(), // o puedes pasarlo como string
      'methodName' => $metodo,
      'title' => $titulo,
      'message' => $mensaje,
      'confirmText' => $textoBoton,
    ]);
  }

  public function beforedelete(){
    $this->confirmarAccion(
        null,
        'delete',
        '¿Está seguro que desea eliminar este registro?',
        'Después de confirmar, el registro será eliminado',
        __('Sí, proceed')
    );
  }

  #[On('delete')]
  public function delete($recordId)
  {
    try {
      $document = Media::find($recordId);
      if ($document) {
        if ($document->delete()) {
          $this->dispatch('show-notification', ['type' => 'success', 'message' => __('The record has been deleted')]);
        }
        $this->loadDocuments();
      }
    } catch (QueryException $e) {
      // Capturar errores de integridad referencial (clave foránea)
      if ($e->getCode() == '23000') { // Código de error SQL para restricciones de integridad
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('The record cannot be deleted because it is related to other data.')
        ]);
      } else {
        // Otro tipo de error SQL
        $this->dispatch('show-notification', [
          'type' => 'error',
          'message' => __('An unexpected database error occurred.') . ' ' . $e->getMessage()
        ]);
      }
    } catch (\Exception $e) {
      // Capturar cualquier otro error general
      $this->dispatch('show-notification', [
        'type' => 'error',
        'message' => __('An error occurred while deleting the record') . ' ' . $e->getMessage()
      ]);
    }
  }
}
