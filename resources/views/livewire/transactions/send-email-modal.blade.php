<div>
  @if ($showModal)
  <!-- Modal -->
  <div class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Sending documents') }} {{ $this->recipientName }}</h5>
          <button type="button" class="btn-close" wire:click="$set('showModal', false)" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-6">
            <div class="col mb-3">
              <label for="fromEmail" class="form-label">{{ __('From') }}</label>
              <input type="email" wire:model="fromEmail" class="form-control @error('fromEmail') is-invalid @enderror"
                placeholder="{{ __('From') }}">
              @error('message')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>
            <div class="col mb-3">
              <label for="recipientEmail" class="form-label">{{ __('To') }}</label>
              <input type="text" wire:model="recipientEmail"
                class="form-control @error('recipientEmail') is-invalid @enderror" placeholder="{{ __('To') }}">
              @error('message')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row g-12">
            <div class="col mb-3">
              <label for="ccEmails" class="form-label">{{ __('ccEmails') }}</label>
              <input type="text" wire:model="ccEmails" class="form-control @error('ccEmails') is-invalid @enderror"
                placeholder="{{ __('ccEmails') }}">
              @error('message')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col mb-3">
              <label for="subject" class="form-label">{{ __('Subject') }}</label>
              <input type="text" wire:model="subject" class="form-control @error('subject') is-invalid @enderror"
                placeholder="{{ __('Subject') }}">
              @error('message')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row g-6">
            <div class="col mb-3">
              <label for="message" class="form-label">{{ __('Message') }}</label>
              <textarea class="form-control @error('message') is-invalid @enderror" wire:model="message" rows="6"
                placeholder="{{ __('Message') }}">
              </textarea>
              @error('message')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row g-3">
            <div class="col">
              <label class="form-label" for="type">{{ __('Document Type') }}</label>
              <select id="type"
                      wire:model="type"
                      class="form-select @error('type') is-invalid @enderror">
                <option value="">{{ __('Seleccione...') }}</option>
                @if ($this->is_proforma)
                  <option value="FE">{{ __('Proforma') }}</option>
                @else
                  <option value="FE">{{ __('Comprobante Electrónico') }}</option>
                @endif
              </select>
              @error('type')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
            </div>
            <div class="col">
            </div>
          </div>


          <div class="row">
            <div class="col">
              <br>
                @if($this->documentType && $this->has_documents && $canview)
                <p><strong>{{ __("Associated Documents") }}</strong></p>
              @endif

              @livewire('transactions.documents-manager', [
                    'transaction_id' => $this->transactionId,
                    'onlyview'  => true,
                    'canview'   => $canview,
                    'cancreate' => $cancreate,
                    'canedit'   => $canedit,
                    'candelete' => auth()->user()->can('delete-documento-proformas'),
                    'canexport' => auth()->user()->can('export-documento-proformas'),

              ], key('transaction-documents-'.$this->transactionId))

            </div>
          </div>
        </div>
        <div class="modal-footer">

          <!-- Botón para Cancelar -->
          <button type="button" wire:click="$set('showModal', false)" wire:loading.attr="disabled"
            class="btn btn-outline-secondary me-sm-4 me-1 mt-5" data-bs-toggle="tooltip" data-bs-offset="0,8"
            data-bs-placement="top" data-bs-custom-class="tooltip-dark" data-bs-original-title="{{ __('Cancelar') }}">

            <!-- Icono de cerrar cuando no está cargando -->
            <span wire:loading.remove wire:target="$set('showModal', false)">
              <i class="bx bx-x-circle"></i> {{ __('Cancelar') }}
            </span>

            <!-- Icono de carga cuando el modal se está cerrando -->
            <span wire:loading wire:target="$set('showModal', false)">
              <i class="spinner-border spinner-border-sm me-1" role="status"></i>
              {{ __('Cerrando...') }}
            </span>
          </button>


          <!-- Botón para Enviar Email -->
          <button wire:click="sendEmail" wire:loading.attr="disabled"
            class="btn btn-primary data-submit me-sm-4 me-1 mt-5" data-bs-toggle="tooltip" data-bs-offset="0,8"
            data-bs-placement="top" data-bs-custom-class="tooltip-dark"
            data-bs-original-title="{{ __('Enviar Email') }}">

            <!-- Icono de sobre cuando no está cargando -->
            <span wire:loading.remove wire:target="sendEmail">
              <i class="bx bx-envelope"></i> {{ __('Enviar') }}
            </span>

            <!-- Icono de carga cuando el email se está enviando -->
            <span wire:loading wire:target="sendEmail">
              <i class="spinner-border spinner-border-sm me-1" role="status"></i>
              {{ __('Enviando...') }}
            </span>
          </button>

        </div>
      </div>
    </div>
  </div>
  @endif
</div>
