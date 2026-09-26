# Lab05 - Decisiones de Diseño y Propuesta de Microservicio

## 1. Diseño de recursos y rutas
Rutas orientadas a recursos en plural, sin verbos, con sustantivos. Se usa `Route::apiResource` para generar `index, store, show, update, destroy`.
```
GET    /api/products
POST   /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}
GET    /api/products/inventory-summary
GET    /api/purchases
POST   /api/purchases
GET    /api/purchases/{id}
DELETE /api/purchases/{id}
GET    /api/purchase-items
GET    /api/purchases/{purchaseId}/items
```
Anidado justificado: `purchases/{purchaseId}/items` expone relación 1:N de compra a detalles sin duplicar lógica. Se evita `/getProducts` o verbos.

## 2. Recursos de API y desacoplamiento
Ningún controlador serializa el modelo directo. Todos retornan `ProductResource`, `SupplierResource`, `PurchaseResource`, `PurchaseItemResource`.
Ejemplo `ProductResource` mapea `category_id -> category_id`, expone `price` como float y oculta `pivot`, `hidden`, `deleted_at`. Si cambia la columna `name` a `Product_name` en BD, solo cambia el Resource, no el contrato JSON. Colección paginada usa `Resource::collection($paginator)` que preserva `links` y `meta` consistente.

## 3. Métodos, códigos de estado y errores
| Operación | Método | Código | Encabezado |
|-----------|--------|--------|------------|
| Login | POST | 200 token, 401 credenciales, 422 validación | - |
| Listado | GET | 200 con `current_page, last_page, per_page, total, links`, 401 sin token | - |
| Detalle | GET | 200, 404 inexistente, 403 no corresponde, 401 | - |
| Creación | POST | 201 + `Location: /api/products/{id}`, 422 invalid | Location |
| Actualización | PUT | 200, 404, 422 | - |
| Eliminación | DELETE | 204 sin contenido, 404, 409 dependencias | - |
Manejo centralizado en `bootstrap/app.php` con `render` para `BusinessException -> 409`, `ModelNotFoundException -> 404`, `ValidationException -> 422` con `errors` por campo. No se expone traza.

## 4. Paginación
Estructura consistente en toda la API:
```json
{
  "data": [],
  "current_page": 1,
  "last_page": 5,
  "per_page": 10,
  "total": 45,
  "links": {"first": "...", "last": "...", "prev": null, "next": "..."},
  "meta": {}
}
```
Parámetros `page`, `per_page` con tope 50 validado en Service, `sort` y `direction` con whitelist, filtros combinables `q, category_id, brand_id`.

## 5. Documentación OpenAPI
Especificación `docs/openapi.yaml` OpenAPI 3.0.3 con `servers`, `security bearerAuth`, `tags`, `paths` para cada endpoint, `components/schemas` para `Product, Category, Brand, Unit, Supplier, Purchase` y `responses` `401, 403, 404, 409, 422`. Incluye ejemplos `correcto/invalido` y códigos de error. Accesible desde `http://localhost:8000/api/documentation` si se instala `l5-swagger` (`php artisan l5-swagger:generate`), también versionado como archivo estático.

## 6. Colección HTTP
`docs/http/lab05-collection.http` (18 solicitudes) y `docs/collection-lab05.postman.json` con variables `baseUrl` y `token`. Cubre casos correctos y error (200, 201, 204, 401, 403, 404, 409, 422). Importar en VS Code REST Client o Postman.

## 7. Propuesta de microservicio
### Responsabilidad acotada: Notificaciones y Reportes
Justificación de separación:
- Alta cohesión: envío de correos/SMS post-compra y generación de PDFs agregados (`inventory-summary`) no pertenecen al dominio transaccional de inventario.
- Escalabilidad independiente: picos de reportes no deben afectar latencia de `POST /api/purchases`.
- Ciclo de vida distinto: plantillas de notificación cambian sin desplegar el monolito.
- Fallo aislado: si el servicio de correo cae, la compra sigue confirmada.

Contrato propuesto:
```
POST /api/notifications/purchase-confirmed
Body: { purchaseId, supplierEmail, items, total }
Respuesta: 202 Accepted { notificationId }
GET /api/reports/inventory?format=pdf
Respuesta: 200 application/pdf + Content-Disposition
```
Comunicación asíncrona vía cola `queue:database` y evento `PurchaseConfirmed`. El monolito publica el evento en la transacción, el microservicio lo consume. Despliegue separado en contenedor propio, misma BD de lectura o réplica. Alternativa sin extraer aún: módulo interno con jobs, pero la interfaz ya está desacoplada para extraer sin romper contrato.

## 8. Decisiones tomadas
- Mantener `ProductService::listPaginated` existente sin reescribir para no romper Lab04.
- Usar `supplier_first_name` tal cual en BD pero exponer igual en Resource para estabilidad, documentado en OpenAPI.
- No usar `apiResource` con verbos y mantener `/products/inventory-summary` como reporte agregado separado de CRUD.
