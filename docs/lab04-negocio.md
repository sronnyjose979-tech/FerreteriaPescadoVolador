# Lab04 - Capa de Negocios

## 1. Entidades principales
Producto, Proveedor, Compra y Detalle de Compra. Se mantienen migraciones y modelos del Lab03 y se separa la lógica en capa de presentación (Controllers), negocio (Services) y datos (Models).

## 2. Validaciones declarativas
- Clases FormRequest para creación y actualización: `StoreProductRequest` / `UpdateProductRequest`, `StoreSupplierRequest` / `UpdateSupplierRequest`, `StorePurchaseRequest` / `UpdatePurchaseRequest`, `StorePurchaseItemRequest` / `UpdatePurchaseItemRequest`.
- Reglas cubren: requeridos, longitud máxima, formato email/url, unicidad (sku, barcode, Supplier_Email, id_Supplier, id_Purchase), rangos numéricos (price min 0, quantity min 1, tax_rate 0-100, stock min 0), existencia de claves foráneas (category_id, brand_id, unit_id, id_product, id_Supplier, id_user).
- Mensajes en español asociados a cada campo, no texto único, retornados como 422 con `errors` por campo.

## 3. Separación de capas
- Controllers delgados con máximo 15 líneas por método: reciben Request validado, delegan al Service y retornan JsonResponse con código correcto (201 con Location en store, 200 en update, 204 en destroy, 200 en index/show).
- Services concentran reglas de negocio y transacciones. Models solo relaciones y casts.

## 4. Reglas de negocio no declarativas

### R1 - No eliminar producto con dependencias activas
Ubicación: `ProductService::deleteProduct`
Comportamiento: si existe al menos un `PurchaseItem` con `id_product` igual, lanza `BusinessException` 409. Esperado: DELETE /api/products/{id} con compras asociadas responde 409 con mensaje.

### R2 - Coherencia de stock
Ubicación: `ProductService::validateStockCoherence` invocada en `createProduct` y `updateProduct`
Comportamiento: si `minimum_stock > maximum_stock` lanza 422; si `stock_quantity < minimum_stock` lanza 422; si `stock_quantity > maximum_stock` lanza 422. Esperado: POST/PUT con stock incoherente responde 422.

### R3 - No confirmar compra sin detalle, total con descuento y cálculo coherente
Ubicación: `PurchaseService::crearConDetalle`
Comportamiento: si `items` vacío lanza 422; si algún `subtotal != quantity * unit_cost` lanza 422; calcula total sumando subtotales, aplica descuento 10% si total >= 50000, 5% si >= 10000, compara con `Purchase_Total` enviado y lanza 422 si difiere; crea Purchase y PurchaseItems y actualiza stock en transacción. Esperado: POST con total incorrecto o sin items responde 422, con total correcto crea y descuenta.

### R4 - Subtotal coherente en detalle y actualización de stock
Ubicación: `PurchaseItemService::crear` y `actualizar`
Comportamiento: valida `subtotal == quantity * unit_cost` con tolerancia 0.01, lanza 422 si no coincide; dentro de transacción crea/actualiza item y ajusta `products.stock_quantity`. Esperado: POST detalle con subtotal 999 en lugar de 200 responde 422.

### R5 - No eliminar proveedor con compras asociadas (regla adicional)
Ubicación: `SupplierServices::eliminar` y `PurchaseService::eliminar`
Comportamiento: si `Supplier->purchases()->exists()` lanza 409. Esperado: DELETE /api/suppliers/{id} con compras responde 409.

## 5. Transacciones y consistencia
- Toda escritura en más de una tabla está envuelta en `DB::transaction`: `ProductService::deleteProduct`, `SupplierServices::eliminar`, `PurchaseService::crearConDetalle` (Purchase + PurchaseItems + incremento stock), `PurchaseItemService::crear/actualizar/eliminar` (item + ajuste stock), `PurchaseService::eliminar`.
- Comportamiento ante fallo intermedio verificado: prueba `reversion de transaccion ante fallo intermedio no deja registros parciales` crea compra con dos items donde el segundo tiene subtotal incoherente; la transacción hace rollback y no queda ni Purchase ni PurchaseItem. Evidencia en `tests/Feature/BusinessRulesTest.php` con `RefreshDatabase`.

## 6. CRUD, paginación, ordenamiento y filtros
- Listados: `ProductService::listPaginated`, `SupplierServices::listPaginated`, `PurchaseService::listPaginated`, `PurchaseItemService::listPaginated`.
- Parámetros: `per_page` máximo 50, `sort` y `direction` con whitelist (ej. Product: name, price, stock_quantity, id, created_at; Supplier: Supplier_First_name, Supplier_Last_name, Supplier_Email, id_Supplier), doble orden `orderBy(sort)->orderBy(id)`, filtros combinables (Product: q, category_id, brand_id, is_active, low_stock, min_price, max_price; Supplier: q, Supplier_Type; Purchase: q, id_Supplier, Purchase_status; PurchaseItem: id_Purchase, id_product).
- Respuesta paginada con `withQueryString()`.

## 7. Excepción de negocio
- Clase `App\Exceptions\BusinessException` con `statusCode` y `errors`, método `render` retorna JSON. Preparada para traducción a HTTP en Lab05. Registrada en `bootstrap/app.php` para `api/*`.

## 8. Pruebas
- Ubicación: `tests/Feature/BusinessRulesTest.php` una prueba por regla más transacción.
- Pruebas: delete producto con dependencias, stock incoherente, compra sin detalle, descuento por umbral, subtotal incoherente, reversión transaccional, delete proveedor con compras.
- Ejecución: `php artisan test --filter=BusinessRulesTest`

## 9. Evidencias
- CRUD: `php artisan route:list` y colección HTTP `docs/http/lab04-crud.http` o pruebas Pest como evidencia; respuestas 201 con Location, 200, 204, 404, 409, 422.
- Datos inválidos: enviar POST /api/products sin name o con price negativo retorna 422 con errores por campo.
- Violación regla: DELETE producto con compras retorna 409, POST compra sin items 422, POST item con subtotal incorrecto 422.
- Transacción: prueba de rollback descrita arriba.
