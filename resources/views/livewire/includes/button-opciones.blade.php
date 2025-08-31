
    <div class="btn-group">
      <button type="button" class="btn btn-info dropdown-toggle mx-1" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bx bx-menu me-1"></i> {{ __('Opciones') }}
      </button>
      <ul class="dropdown-menu" style="">
        <li>
            <a class="dropdown-item"
                href="javascript:void(0);"
                wire:click.prevent="beforeCreditNote"
                wire:loading.attr="disabled"
                wire:target="beforeCreditNote">
                <i class="bx bx-chevron-right scaleX-n1-rtl"></i>{{ __('Nota de crédito') }}
            </a>
        </li>
        <li>
            <a class="dropdown-item"
                href="javascript:void(0);"
                wire:click.prevent="beforeDebitNote"
                wire:loading.attr="disabled"
                wire:target="beforeDebitNote">
                <i class="bx bx-chevron-right scaleX-n1-rtl"></i>{{ __('Nota de débito') }}
            </a>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li>
            <a class="dropdown-item"
                href="javascript:void(0);"
                wire:click.prevent="beforeclonar"
                wire:loading.attr="disabled"
                wire:target="beforeclonar">
                <i class="bx bx-chevron-right scaleX-n1-rtl"></i>{{ __('Clonar') }}
            </a>
        </li>
      </ul>
    </div>
