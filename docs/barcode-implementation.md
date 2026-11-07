# Códigos de barras y recepción de productos

## Introducción

Los códigos de barras permiten identificar los productos con un lector o con la
cámara del teléfono. Desde Ionic, el bodeguero puede consultar un producto y
seleccionar el detalle de compra que necesita recibir.

Cada código pertenece a un producto. Una compra puede incluir varios productos,
y un mismo producto puede aparecer en distintas compras pendientes.

## Generación del código

Al registrar un producto sin `barcode`, Laravel genera un identificador numérico
de 20 dígitos. Esto ocurre en el evento `creating` del modelo `Product`, dentro
del método `booted()`.

Antes de asignarlo, se comprueba que el código no esté en uso, incluyendo los
productos eliminados lógicamente. La columna `barcode` también tiene una
restricción de unicidad en la base de datos.

Si el producto ya tiene un código al registrarse, se conserva. Editar sus otros
datos no genera un código nuevo. Los productos antiguos sin código tampoco
reciben uno automáticamente al consultarlos.

Para representar el identificador como barras se utiliza la librería Picqer con
el formato **Code 128**. El SVG se genera cuando se solicita, sin guardar una
imagen permanente.

## Archivos principales

| Archivo | Función |
|---|---|
| `app/Models/Product.php` | Genera el código y relaciona el producto con compras, ventas y movimientos. |
| `app/Services/ProductService.php` | Crea productos y realiza la búsqueda por código de barras. |
| `app/Http/Controllers/ProductController.php` | Atiende las consultas del producto y devuelve su representación SVG. |
| `app/Models/Purchase.php` y `PurchaseItem.php` | Representan la compra y sus detalles; cada detalle guarda `received_at`. |
| `app/Services/PurchaseService.php` | Registra las compras pendientes sin ingresar todavía sus unidades al inventario. |
| `app/Services/PurchaseItemService.php` | Lista los detalles candidatos y confirma su recepción. |
| `app/Http/Controllers/PurchaseItemController.php` | Recibe las consultas y confirmaciones enviadas por Ionic. |
| `app/Http/Requests/PurchaseItem/ReceivePurchaseItemRequest.php` | Valida el código y la cantidad enviada. |
| `app/Http/Resources/PurchaseReceiptResource.php` | Define los datos de recepción que recibe Ionic, sin costos de compra. |
| `app/Services/InventoryMovementService.php` | Actualiza las existencias y registra las entradas y salidas del Kardex. |
| `routes/api.php` | Define las rutas de productos y recepción. |

El permiso específico de recepción se controla en `PurchaseItemPolicy` y en el
middleware `EnsureTokenHasAbility`.

## Endpoints

| Método y ruta | Uso |
|---|---|
| `GET /api/products/by-barcode?barcode=...` | Consultar un producto mediante su código exacto. |
| `GET /api/products/{product}/barcode` | Obtener el nombre, código y SVG Code 128 del producto. |
| `GET /api/products/{product}` | Consultar los datos del producto y sus existencias actuales. |
| `GET /api/purchase-receipts?barcode=...` | Listar los detalles pendientes del producto escaneado. |
| `POST /api/purchase-items/{purchase_item}/receive` | Confirmar la recepción del detalle seleccionado. |

Estas rutas requieren autenticación Sanctum. Después de iniciar sesión mediante
`POST /api/login`, el cliente envía el token en la cabecera
`Authorization: Bearer <token>` y solicita respuestas con
`Accept: application/json`. Para enviar la confirmación utiliza además
`Content-Type: application/json`.

Las consultas de productos requieren `view products`. Las rutas de recepción
requieren el permiso y la habilidad del token `receive purchases`; también se
comprueba que el usuario pueda ver el producto. Este acceso permite al bodeguero
recibir detalles de compras del administrador, sin darle permiso para editar las
compras.

### Consulta del producto y su imagen

El código se envía como texto, conservando sus ceros iniciales, espacios y
mayúsculas. La búsqueda es exacta. En Ionic se puede usar `URLSearchParams` para
codificar el valor sin transformarlo.

La consulta del producto devuelve campos como `nombre`, `sku`, `codigo_barras`,
`cantidad_stock` y `esta_activo`, dentro de `data`.

El endpoint de representación devuelve un JSON con `id`, `nombre`,
`codigo_barras` y `svg`. El campo `svg` contiene el XML de la imagen Code 128.
El frontend web puede usarlo para mostrar una etiqueta con el nombre y el código
legible antes de imprimir. Obtener o imprimir esa imagen no cambia el producto
ni sus existencias.

## Recepción desde Ionic

El administrador registra la compra con estado `pendiente` y agrega sus detalles.
En ese momento las cantidades quedan registradas en la compra, pero no aumentan
el stock ni generan entradas en el Kardex.

El bodeguero realiza estos pasos:

1. Escanea el código del producto con la cámara del teléfono.
2. Consulta los detalles disponibles en `/api/purchase-receipts?barcode=...`.
3. Selecciona el detalle correspondiente, revisando la compra y la cantidad esperada.
4. Ingresa la cantidad que recibió físicamente.
5. Confirma la recepción del detalle seleccionado.

Si hay varias compras pendientes del mismo producto, Ionic debe mostrar las
opciones. El código de barras no selecciona una compra automáticamente.

El listado devuelve `id`, `id_purchase`, `product_id`, `nombre`, `barcode`,
`quantity` y `received_at`. Incluye paginación mediante `data`, `links` y `meta`.
Cada detalle se recibe por separado, aunque pertenezca a la misma compra.

## Actualización del inventario y Kardex

Al confirmar, Laravel comprueba el código del producto, la cantidad del detalle y
que este no haya sido recibido. Dentro de una misma transacción:

- Aumenta `Product.stock_quantity` por la cantidad recibida.
- Crea una entrada en `InventoryMovement` con el producto, la cantidad, el usuario
  responsable y el saldo posterior.
- Relaciona el movimiento con el `PurchaseItem` mediante `movementable_type` y
  `movementable_id`.
- Guarda la fecha y hora de recepción en `received_at`.

La compra, el detalle y el producto se bloquean mientras se confirma la operación.
Si ocurre un error, la transacción revierte los cambios. Cuando todos los detalles
están recibidos, la compra cambia automáticamente a `recibida`.

Después de confirmar, Ionic vuelve a consultar el producto para mostrar
`cantidad_stock` actualizado. No debe enviar otra entrada al Kardex ni sumar las
unidades por su cuenta.

Las ventas utilizan su flujo existente: al registrar un `SaleItem`, se comprueba
la disponibilidad, se descuentan las unidades y se registra una salida en
`InventoryMovement`.

El circuito de recepción descrito parte de una compra `pendiente`. Las compras
creadas en otros estados conservan el ingreso inmediato de existencias.

## Validaciones

La cantidad recibida debe ser un número entero positivo y coincidir exactamente
con `PurchaseItem.quantity`. Una cantidad inferior o superior se rechaza con
`422`, sin modificar el stock ni el Kardex. También se rechaza un código que no
corresponda al producto del detalle elegido.

Si `received_at` ya tiene un valor, la confirmación se rechaza con `409`. Esto
impide ingresar dos veces las unidades del mismo detalle. Los detalles con
movimientos previos tampoco están disponibles para una nueva recepción.

Las compras históricas quedan fuera del flujo mediante la fecha de corte definida
en `config/purchases.php`, a partir de `PURCHASE_RECEIVING_STARTS_AT`. Solo se
admiten compras pendientes creadas después de esa fecha. Sin un corte válido,
la recepción está deshabilitada. Los históricos requieren conciliación y no deben
habilitarse cambiando sus fechas o retrocediendo el corte.

Un producto sin código, o con un código que no pueda representarse como Code 128,
recibe `422` al solicitar el SVG. Un código inexistente devuelve `404` al buscarlo.
La falta de autenticación devuelve `401`; la falta de permiso o habilidad, `403`.

## Ejemplo: recepción de 10 martillos

El administrador registra una compra pendiente de **10 martillos**. Supongamos
que el producto tiene el código `000123`, su stock inicial es 0 y el detalle de
compra tiene el identificador 7.

El bodeguero escanea el producto y consulta:

```http
GET /api/purchase-receipts?barcode=000123
```

Ionic muestra la compra y las 10 unidades esperadas. Después de contar los
martillos, el bodeguero selecciona el detalle 7 y envía:

```http
POST /api/purchase-items/7/receive
Content-Type: application/json
```

```json
{
  "barcode": "000123",
  "quantity": 10
}
```

Ejemplo de respuesta exitosa:

```json
{
  "data": {
    "id": 7,
    "id_purchase": "PUR-001",
    "product_id": 1,
    "nombre": "Martillo",
    "barcode": "000123",
    "quantity": 10,
    "received_at": "2026-11-07T15:00:00.000000Z"
  }
}
```

El stock pasa de **0 a 10** y se registra una entrada de 10 unidades vinculada al
detalle 7. Si la compra no tiene otros detalles pendientes, queda como `recibida`.

Si se envían 9 u 11 unidades, la confirmación se rechaza sin ingresar mercadería.
Si se intenta confirmar nuevamente el detalle recibido, se rechaza sin duplicar
el stock ni su entrada en el Kardex.
