# Prompt — Validación Culqi: mostrar mensajes reales al usuario

Copia y pega el bloque de abajo en otro proyecto réplica (Laravel + checkout Culqi) para que la IA aplique **solo** esta corrección.

---

## Prompt (copiar desde aquí)

```
Eres un ingeniero Laravel en un e-commerce réplica con checkout Culqi (script v4 + Culqi PHP SDK).

## Problema
Al pagar con tarjeta de prueba de Culqi (ej. fondos insuficientes: 4000 0400 0000 0008 / CVV 295 / MM/YY 03/30), el pago se rechaza correctamente en Culqi, PERO el frontend muestra un mensaje genérico:

  "No se pudo procesar el pago. No se realizó ningún cargo ni pedido."

El usuario NUNCA ve mensajes específicos como "Fondos insuficientes", "CVV incorrecto", etc.

## Causa raíz típica
1. El SDK `culqi/culqi-php` (Charges->create) a menudo NO lanza excepción ante rechazo: devuelve un **string JSON** con el error, o un objeto sin `id`.
2. El código trata esa respuesta como éxito o cae en un `catch (\Exception)` genérico.
3. El controlador responde siempre el mismo texto fijo, sin parsear `decline_code` / `user_message` / `merchant_message`.

## Objetivo
Cuando Culqi rechace el cobro, el cliente debe ver un mensaje claro en español según el código de error (p. ej. insufficient_funds → "Fondos insuficientes...").
No crear pedido. No inventar cobros. Alcance mínimo: solo Culqi mensajes de rechazo.

## Implementación requerida

### 1) Crear App\Exceptions\CulqiPaymentRejectedException
Excepción con:
- mensaje usuario (string)
- errorCode (string, default 'unknown')
- merchantMessage (?string)
Métodos: getUserMessage(), getErrorCode(), getMerchantMessage()

### 2) Actualizar App\Services\CulqiService (o equivalente)
En createCharge():
A) Llamar a Charges->create como ahora.
B) Si el resultado es string → parsear JSON y LAMAR a reject (no continuar).
C) Si no es objeto o no tiene ->id → rechazar con mensaje genérico amigable.
D) Si tiene outcome->type y NO es 'venta_exitosa' → rechazar.
E) Solo si OK devolver array padron:
   ['metodo'=>'culqi','estado'=>'completado','transaccion_id'=>$id,'fecha_pago'=>now(),'codigo_culqi'=>...]

Agregar mapa de mensajes (español), mínimo:
- insufficient_funds
- incorrect_cvv
- lost_card
- stolen_card
- contact_issuer
- issuer_not_available
- issuer_decline_operation
- invalid_card
- processing_error
- expired_card
- fraudulent
- card_velocity_exceeded

Agregar método estático messageFromCulqiPayload(array|string $payload): array
que scrutiniza en este orden fallbacks:
  decline_code | charge_decline_code | outcome.type | type
y mensajes:
  mapa[code] | user_message | merchant_message | outcome.user_message | fallback genérico

Devolver: ['code','user_message','merchant_message']
Loguear CULQI_CHARGE_REJECTED al rechazar.

### 3) CheckoutController (procesarPedido / catch de pasarela)
- Capturar CulqiPaymentRejectedException ANTES del catch genérico.
- Responder JSON 422:
  {
    "success": false,
    "message": "<mensaje usuario>",
    "error_code": "<codigo culqi>",
    "result": null
  }
- En catch (\Exception) genérico: si pasarela es culqi, intentar CulqiService::messageFromCulqiPayload($e->getMessage()) y usar user_message si code != unknown.
- NO usar siempre el texto fijo genérico para errores Culqi.

### 4) Frontend checkout (Blade/JS donde esté procesarTokenCulqi / window.culqi)
- En éxito con success:false O en axios.catch: mostrar Swal con title "Pago rechazado" y text = response.data.message (el backend).
- En window.culqi cuando Culqi.error (token no generado):
  preferir Culqi.error.user_message || Culqi.error.merchant_message

## Qué NO tocar
- Otros gateways (Stripe/PayPal/Izipay) salvo el mismo patrón de mensaje si ya hay estructura idéntica.
- No cambiar orden cobrar→pedido→mail.
- No ampliar el alcance a cPanel/admin.

## Criterio de aceptación
1. Tarjeta test insufficient_funds (4000 0400 0000 0008, CVV 295, 03/30):
   modal muestra algo como "Fondos insuficientes en la tarjeta..."
2. No se crea pedido en BD.
3. En log aparece CULQI_CHARGE_REJECTED con code=insufficient_funds (o equivalente).
4. Network → POST enviar_pago_con_culqi → body tiene message específico + error_code.

## Referencia de tarjetas de error Culqi (docs.culqi.com → Tarjetas de prueba)
- insufficient_funds: VISA 4000 0400 0000 0008 | 03/30 | 295
- incorrect_cvv / contact_issuer / lost_card / etc.: usar las de la documentación oficial de Culqi.

Aplica los cambios de forma mínima y enfocada. Al terminar, lista archivos tocados y cómo probar.
```

---

## Prompt (hasta aquí)

Guarda este archivo en cada réplica si quieres tener la misma guía en el repo.

## Archivos típicos a tocar en el proyecto destino

| Archivo | Acción |
|---------|--------|
| `app/Exceptions/CulqiPaymentRejectedException.php` | Crear |
| `app/Services/CulqiService.php` | Parsear error + mapa mensajes |
| `app/Http/Controllers/Web/CheckoutController.php` | catch específico + JSON |
| `resources/views/web/pages/web_checkout.blade.php` (o JS equivalente) | Mostrar `data.message` al usuario |

## Notas rápidas

- Requiere `culqi/culqi-php` y el flujo checkout ya existente.
- El fix es de **mensajes / validación de respuesta**, no de claves API.
- Si el proyecto aún usa el texto fijo en el `catch`, ese es el primer síntoma a buscar.
