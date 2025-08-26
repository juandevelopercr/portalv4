<div class="btn-group btn-sm" id="dropdown-icon-demo">
  <button type="button" class="btn btn-sm buttons-collection dropdown-toggle btn-label-primary mx-2"
    data-bs-toggle="dropdown" aria-expanded="false"><i class="bx bx-export bx-sm me-sm-2"></i>{{
    __('Export') }}</button>
  <ul class="dropdown-menu">
    <li>
      <a href="javascript:void(0);" wire:click="prepareExportExcel" class="dropdown-item d-flex align-items-center">
        <span><i class="bx bxs-file-export me-2"></i>Excel</span>
      </a>
    </li>
    {{--
    <li>
      <a href="javascript:void(0);" wire:click="exportCsv" class="dropdown-item d-flex align-items-center">
        <span><i class="bx bx-file me-2"></i>Csv</span>
      </a>
    </li>
     --}}
    <li>
      <a href="javascript:void(0);" wire:click="prepareExportPdf" class="dropdown-item d-flex align-items-center">
        <span><i class="bx bxs-file-pdf me-2"></i>Pdf</span>
      </a>
    </li>
    <?php
        /*
        <li>
            <a href="#" onclick="printTable()" class="dropdown-item d-flex align-items-center">
                <span><i class="bx bx-printer me-2"></i>Print</span>
            </a>
        </li>
        */
        ?>
  </ul>
</div>
