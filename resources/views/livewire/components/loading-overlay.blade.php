<div>
@if($visible)
<div
    style="
        position: fixed;
        inset: 0;
        z-index: 99999;
        background-color: rgba(0, 0, 0, 0.6);
        display: grid;
        place-items: center;
    "
>
    <div class="text-white text-center">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <div class="mt-3 fs-5">{{ $message }}</div>
    </div>
</div>
@endif
</div>
