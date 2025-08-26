<?php

namespace App\Http\Controllers\hacienda;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiHaciendaController extends Controller
{
  /**
   * Maneja el callback para la factura.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function facturaCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    Log::info('Factura Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::FE);

    // Si todo está correcto, respondemos con un HTTP_OK (200)
    return response()->json(['message' => 'Factura procesada correctamente'], 200);
  }

  /**
   * Maneja el callback para la nota de débito.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function notaDebitoCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    Log::info('Nota de débito Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::NDE);

    return response()->json(['message' => 'Nota de Débito procesada correctamente'], 200);
  }

  /**
   * Maneja el callback para la nota de crédito.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function notaCreditoCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    Log::info('nota de crédito Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::NCE);

    return response()->json(['message' => 'Nota de Crédito procesada correctamente'], 200);
  }

  /**
   * Maneja el callback para el tiquete.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function tiqueteCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    //Log::info('Factura Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::TE);

    return response()->json(['message' => 'Tiquete procesado correctamente'], 200);
  }

  /**
   * Maneja el callback para el mensaje.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  /*
  public function mensajeCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    Log::info('Mensaje Callback Recibido:', $responseData);

    Comprobante::verifyResponseStatusHacienda($responseData);

    return response()->json(['message' => 'Mensaje procesado correctamente']);
  }
  */
  public function mensajeCallBack(Request $request)
  {
    try {
        Log::info('Mensaje Callback Iniciado', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->all()
        ]);

        // Validación básica del payload
        /*
        $validated = $request->validate([
            'clave' => 'required|string|size:50',
            'estado' => 'required|string',
            // ... otras reglas de validación
        ]);
        */

        // Procesar la respuesta
        $result = Comprobante::verifyResponseStatusHacienda($request->all());

        Log::info('Mensaje Callback Procesado', [
            'clave' => $request->input('clave'),
            'resultado' => $result
        ]);

        return response()->json(['message' => 'Mensaje procesado correctamente'], 200);

    } catch (\Throwable $e) {
        Log::error('Error en mensajeCallBack: ' . $e->getMessage(), [
            'exception' => $e,
            'request' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
  }

  /**
   * Maneja el callback para la factura de compra.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function facturaCompraCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    //Log::info('Factura Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::FEC);

    return response()->json(['message' => 'Factura de compra procesada correctamente'], 200);
  }

  /**
   * Maneja el callback para la factura de exportacion.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function facturaExportacionCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    //Log::info('Factura Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::FEE);

    return response()->json(['message' => 'Factura de exportación procesada correctamente'], 200);

  }

  /**
   * Maneja el callback para la factura de recibo de pago.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function facturaReciboPagoCallback(Request $request)
  {
    // Aquí podemos validar la estructura del callback recibido
    $responseData = $request->all();

    // Opcional: Log de la respuesta para auditoría
    //Log::info('Factura Callback Recibido:', $responseData);

    Transaction::verifyResponseStatusHacienda($responseData, Transaction::REP);

    return response()->json(['message' => 'Recibo de pago procesado correctamente'], 200);
  }
}
