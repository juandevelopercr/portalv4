<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiBCCR
{
    private $base;
    private $token;

    public function __construct()
    {
        $this->base = 'https://apim.bccr.fi.cr/SDDE';
        $this->token = config('services.bccr.token');
    }

    public function obtenerTipoCambio($codigo, $fecha)
    {
        // La API requiere YYYY/MM/DD
        $fecha = str_replace('-', '/', $fecha);

        $url = $this->base . "/api/Bccr.GE.SDDE.Publico.Indicadores.API/indicadoresEconomicos/{$codigo}/series";

        Log::info('BCCR: solicitud enviada', [
            'url' => $url,
            'params' => [
                'fechaInicio' => $fecha,
                'fechaFin' => $fecha,
                'idioma' => 'ES'
            ],
            'verify' => app()->environment('production') ? '/etc/ssl/certs/ca-bundle.crt' : false
        ]);

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->withOptions([
                    'verify' => app()->environment('production')
                        ? '/etc/ssl/certs/ca-bundle.crt'
                        : false
                ])
                ->get($url, [
                    'fechaInicio' => $fecha,
                    'fechaFin' => $fecha,
                    'idioma' => 'ES'
                ]);

            if (!$response->successful()) {
                Log::error('BCCR: error en respuesta', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return null;
            }

            $json = $response->json();
            Log::info('BCCR: body recibido', ['json' => $json]);

            // NUEVO FORMATO API SDDE
            $valor = $json['datos'][0]['series'][0]['valorDatoPorPeriodo'] ?? null;

            if ($valor === null) {
                Log::warning('BCCR: no se encontró valorDatoPorPeriodo');
                return null;
            }

            return $valor;

        } catch (\Exception $e) {
            Log::error('BCCR: excepción general', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
