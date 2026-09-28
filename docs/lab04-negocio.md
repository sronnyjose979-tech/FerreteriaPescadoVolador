# Lab04 - Capa de Negocios

## 1. Entidades principales
Producto, Proveedor, Compra y Detalle de Compra. Se mantienen migraciones y modelos del Lab03 y se separa la lógica en capa de presentación (Controllers), negocio (Services) y datos (Models).

## 2. Validaciones declarativas
- Clases FormRequest para creación y actualización: `StoreProductRequest` / `UpdateProductRequest`, `StoreSupplierRequest` / `UpdateSupplierRequest`, `StorePurchaseRequest` / `UpdatePurchaseRequest`, `StorePurchaseItemRequest` / `UpdatePurchaseItemRequest`.
- Reglas cubren: requeridos, longitud máxima, formato email/url, unicidad (sku, barcode, supplier_email, id_supplier, id_purchase), rangos numéricos (price min 0, quantity min 1, tax_rate 0-100, stock min 0), existencia de claves foráneas (category_id, brand_id, unit_id, product_id, id_supplier, user_id).
- Mensajes en español asociados a cada campo, no texto único, retornados como 422 con `errors` por campo.

## 3. Separación de capas
- Controllers delgados con máximo 15 líneas por método: reciben Request validado, delegan al Service y retornan JsonResponse con código correcto (201 con Location en store, 200 en update, 204 en destroy, 200 en index/show).
- Services concentran reglas de negocio y transacciones. Models solo relaciones y casts.

## 4. Reglas de negocio no declarativas

### R1 - No eliminar producto con dependencias activas
Ubicación: `ProductService::eliminar`
Comportamiento: si existe al menos un `PurchaseItem` con `product_id` igual, lanza `BusinessException` 409. Esperado: DELETE /api/products/{id} con compras asociadas responde 409 con mensaje.

### R2 - Coherencia de stock
Ubicación: `ProductService::validateStockCoherence` invocada en `crear` y `actualizar` (en la actualización se combinan los valores guardados con los nuevos)
Comportamiento: si `minimum_stock > maximum_stock` lanza 409; si `stock_quantity < minimum_stock` lanza 409; si `stock_quantity > maximum_stock` lanza 409. Esperado: POST/PUT con stock incoherente responde 409.

### R3 - No confirmar compra sin detalle, total con descuento y cálculo coherente
Ubicación: `PurchaseService::crearConDetalle`
Comportamiento: si `items` vacío lanza 409; si algún `subtotal != quantity * unit_cost` lanza 409; calcula total sumando subtotales, aplica descuento 10% si total >= 50000, 5% si >= 10000, compara con `purchase_total` enviado (opcional) y lanza 409 si difiere; crea Purchase y PurchaseItems, suma el stock y registra un movimiento de entrada por detalle en una transacción. `PurchaseController::store` llama a este método y la compra queda a nombre del usuario del token. Esperado: POST con total incorrecto o sin items responde 409; sin el campo `items` responde 422; con total correcto crea y descuenta.

### R4 - Subtotal coherente en detalle y actualización de stock
Ubicación: `PurchaseItemService::crear` y `actualizar`
Comportamiento: el subtotal es opcional; si se envía, valida `subtotal == quantity * unit_cost` con tolerancia 0.01 y lanza 409 si no coincide; si no se envía, lo calcula el servicio (también al actualizar solo la cantidad). Dentro de transacción crea/actualiza/elimina el item, ajusta `products.stock_quantity` y registra el movimiento de inventario. Esperado: POST detalle con subtotal 999 en lugar de 200 responde 409.

### R5 - No eliminar proveedor con compras asociadas (regla adicional)
Ubicación: `SupplierServices::eliminar` y `PurchaseService::eliminar`
Comportamiento: si `Supplier->purchases()->exists()` lanza 409. Esperado: DELETE /api/suppliers/{id} con compras responde 409.

### R6 - Stock suficiente al vender
Ubicación: `SaleItemService::crear`, `actualizar` y `eliminar`, con `InventoryMovementService::registrarSalida` y `registrarEntrada`
Comportamiento: al vender bloquea el producto, verifica que haya unidades suficientes y lanza 409 si no; descuenta el stock y registra un movimiento de salida con el saldo resultante. Eliminar o cambiar un detalle devuelve las unidades con un movimiento de entrada. Esperado: POST /api/sale-items con más unidades que el stock responde 409 y no guarda nada.

### R7 - Los pagos no superan el total de la venta
Ubicación: `PaymentService::crear` y `actualizar`
Comportamiento: suma los pagos no cancelados de la venta (sin contar dos veces el pago que se edita) y lanza 409 si con el nuevo monto se supera el total. Permite pagos mixtos (efectivo, tarjeta, SINPE). Esperado: POST /api/payments que excede el total responde 409.

## 5. Transacciones y consistencia
- Toda escritura en más de una tabla está envuelta en `DB::transaction`: `ProductService::eliminar`, `SupplierServices::eliminar`, `PurchaseService::crearConDetalle` (Purchase + PurchaseItems + incremento stock + movimientos), `PurchaseItemService::crear/actualizar/eliminar` (item + ajuste stock + movimiento), `SaleItemService::crear/actualizar/eliminar` (item + ajuste stock + movimiento), `PaymentService::crear/actualizar`, `PurchaseService::eliminar`.
- Comportamiento ante fallo intermedio verificado: prueba `reversion de transaccion ante fallo intermedio no deja registros parciales` crea una compra con dos items donde el segundo referencia un producto inexistente, de modo que la base de datos falla al insertarlo después de haber creado la compra, el primer item y el aumento de stock; la transacción hace rollback, no queda ni Purchase ni PurchaseItem y el stock vuelve a su valor. Evidencia en `tests/Feature/BusinessRulesTest.php` con `RefreshDatabase`. `tests/Unit/Services/SaleItemServiceTest.php` verifica lo mismo en ventas.

## 6. CRUD, paginación, ordenamiento y filtros
- Listados: `ProductService::listPaginated`, `SupplierServices::listPaginated`, `PurchaseService::listPaginated`, `PurchaseItemService::listPaginated`.
- Parámetros: `per_page` máximo 50, `sort` y `direction` con whitelist (ej. Product: name, price, stock_quantity, id, created_at; Supplier: supplier_first_name, supplier_last_name, supplier_email, id_supplier), doble orden `orderBy(sort)->orderBy(id)`, filtros combinables (Product: q, category_id, brand_id, is_active, low_stock, min_price, max_price; Supplier: q, supplier_type; Purchase: q, id_supplier, purchase_status; PurchaseItem: id_purchase, product_id).
- Respuesta paginada con `withQueryString()`.

## 7. Excepción de negocio
- Clase `App\Exceptions\BusinessException` con `statusCode` y `errors`, método `render` retorna JSON. Preparada para traducción a HTTP en Lab05. Registrada en `bootstrap/app.php` para `api/*`.

## 8. Pruebas
- Ubicación: `tests/Feature/BusinessRulesTest.php` una prueba por regla más transacción, y `tests/Unit/Services/*` con pruebas unitarias de cada servicio usando dobles de prueba.
- Pruebas: delete producto con dependencias, stock incoherente, compra sin detalle, descuento por umbral (incluye los límites 9 999.99, 10 000, 49 999 y 50 000), subtotal incoherente, reversión transaccional, delete proveedor con compras, stock insuficiente al vender y pagos que superan el total.
- Ejecución: `php artisan test --filter=BusinessRulesTest` o `php artisan test` para toda la suite.

## 9. Evidencias
- CRUD: `php artisan route:list` y colección HTTP `docs/http/lab04-crud.http` o pruebas Pest como evidencia; respuestas 201 con Location, 200, 204, 404, 409, 422.
- Datos inválidos: enviar POST /api/products sin name o con price negativo retorna 422 con errores por campo.
- Violación regla: DELETE producto con compras retorna 409, POST compra sin items 409, POST item con subtotal incorrecto 409, POST detalle de venta sin stock 409.
- Transacción: prueba de rollback descrita arriba.
