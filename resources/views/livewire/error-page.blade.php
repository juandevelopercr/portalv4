<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4 py-8">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 md:p-8">
            <!-- Contenedor de imagen -->
            <div class="flex justify-center mb-4 md:mb-6">
                <div class="relative w-32 h-32 md:w-40 md:h-40 bg-indigo-50 rounded-full border-4 border-indigo-100 overflow-hidden"
                     style="box-sizing: content-box;">
                    <div class="absolute inset-0 flex items-center justify-center p-4">
                        <img
                            src="{{ $image }}"
                            alt="Error {{ $code }}"
                            class="max-w-full max-h-full object-contain"
                            style="max-width: 80px; max-height: 80px; width: auto; height: auto; display: block;"
                        >
                    </div>
                </div>
            </div>

            <div class="text-center">
                <div class="text-7xl md:text-8xl font-bold text-indigo-700 mb-2">{{ $code }}</div>
                <p class="text-lg md:text-xl text-gray-700 mb-6 px-4">{{ $message }}</p>

                <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3">
                    @if($code == 419)
                        <!-- SOLUCIÓN PARA EL CICLO DE RECARGA -->
                        <button type="button" onclick="window.location.href = '{{ route('login') }}'"
                                class="btn btn-danger btn-sm flex items-center justify-center"
                                title="Iniciar sesión">
                                <i class="bx bx-log-in mr-2"></i> Iniciar sesión
                        </button>

                    @else
                        <a href="{{ $buttonAction }}"
                           class="btn btn-danger btn-sm flex items-center justify-center">
                            <i class="bx bx-home mr-2"></i> {{ $buttonText }}
                        </a>

                        <button type="button" onclick="history.back()"
                                class="btn btn-outline-danger btn-sm flex items-center justify-center"
                                title="Volver atrás">
                          <i class="bx bx-reply mr-2"></i> Volver atrás
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
