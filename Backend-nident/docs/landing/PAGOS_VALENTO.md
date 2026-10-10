# Pagos Valento — estado

> Este documento **no contiene secretos**. Las llaves viven únicamente en
> `pago_pasarela_config` (base de datos) o en `.env`, nunca en el repositorio.

## Estado actual

| Método | Código | Estado | Notas |
|---|---|---|---|
| Culqi | `culqi` | **SANDBOX — pendiente de credenciales** | Falta cargar las llaves del comercio Valento |
| Pedido por WhatsApp | `whatsapp` | Activo | Crea el pedido pendiente; el stock no baja hasta aprobar |
| Transferencia / Yape / Plin | — | Activo | Pendiente de pago + subida de comprobante |
| Izipay | `izipay` | Inactivo (`is_activo = 0`) | El código existe, no se publica |
| PayPal | `paypal` | Inactivo (`is_activo = 0`) | — |
| Stripe | — | Inactivo | — |

## Dónde vive la configuración

- `pago_pasarela`: una fila por pasarela; `is_activo = 1` marca la vigente.
- `pago_pasarela_config`: pares clave/valor de credenciales por pasarela.
- `App\Services\PaymentGatewayManager`: resuelve la pasarela activa y lee la
  configuración (BD primero, `.env` como respaldo). Fallback seguro: `culqi`.
- `App\Services\CulqiService`: integración con la API de Culqi.

## Qué falta para habilitar Culqi en producción

1. Obtener del cliente las llaves del comercio (pública y privada) de **test** y
   luego las de **producción**.
2. Cargarlas desde el admin en `pago_pasarela_config`
   (`CULQI_PUBLIC_KEY`, `CULQI_PRIVATE_KEY`). No commitearlas.
3. Dejar `pago_pasarela.is_activo = 1` solo en `culqi`.
4. Probar en sandbox: pago aprobado, pago rechazado y doble envío del mismo cargo.

## Reglas que no se negocian

- El total se calcula en el servidor; nunca se confía en el monto del cliente.
- Un pago se confirma **contra la API de Culqi**, jamás con el redirect del
  navegador.
- Idempotencia por id de transacción: un pago no puede generar dos pedidos.
- El stock baja solo con pago confirmado o pedido aprobado, dentro de una
  transacción.
- Pago rechazado: no hay pedido pagado y el stock queda intacto.

## Verificación pendiente (Fase C)

- [ ] Culqi SANDBOX aprobado → pedido pagado → stock baja → email → `/thank-you`
- [ ] Culqi rechazado → sin pedido pagado y sin baja de stock

Ambos quedan **BLOQUEADO** mientras no existan credenciales de Culqi.
