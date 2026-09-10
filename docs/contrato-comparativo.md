# Contrato Comparativo - Ferretería UCR

Este documento define el contrato común para la entidad seleccionada que van a compartir las tres implementaciones del back-end (Laravel, NestJS y ASP.NET Core). Las tres tecnologías deben responder exactamente las mismas rutas, códigos de estado HTTP y estructuras de datos.

# 1\. Entidad Seleccionada: Product

Se seleccionó la entidad `Product` corregida según el diagrama actualizado. A continuación se detallan los atributos corregidos que sustituyen a los del documento anterior. Se deben usar **únicamente** los atributos que aparecen en la imagen.

La entidad `Product` cuenta con 16 atributos y 3 relaciones (Category, Brand, Unit):

|Atributo|Tipo|Descripción / Restricción|
|-|-|-|
|`id\_Product`|Integer, PK, Autoincremental|Identificador único del producto. Clave primaria.|
|`id\_category`|Integer, FK|Llave foránea hacia la entidad `Category`.|
|`id\_brand`|Integer, FK|Llave foránea hacia la entidad `Brand`.|
|`id\_unit`|Integer, FK|Llave foránea hacia la entidad `Unit` (unidad de medida).|
|`Product\_name`|String (Varchar 150)|Nombre comercial del producto. Requerido.|
|`Description`|String (Text)|Descripción detallada del producto.|
|`Price`|Decimal (10,2)|Precio de venta unitario. Requerido. Mayor o igual a 0.|
|`Stock\_Quantity`|Integer|Cantidad actual en inventario.|
|`minimum\_stock`|Integer|Stock mínimo permitido antes de generar alerta de reposición.|
|`maximum\_stock`|Integer|Stock máximo permitido en bodega.|
|`Image\_URL`|String (Varchar 255)|URL de la imagen del producto. Puede ser nulo.|
|`sku`|String (Varchar 50), Único|Código SKU interno. Único.|
|`bar\_code`|String (Varchar 50), Único|Código de barras (EAN/UPC).|
|`tax\_rate`|Decimal (5,2)|Porcentaje de impuesto aplicable (ej: 13.00).|
|`Weigth`|Decimal (8,2)|Peso del producto (kg). Según diagrama aparece como `Weigth`.|
|`is\_active`|Boolean (TinyInt 0/1)|Indica si el producto está activo (1) o inactivo (0).|



# 2\. Endpoints y Respuestas Esperadas

Todas las respuestas deben enviarse en formato JSON.

|Operación|Método HTTP y Ruta|Códigos de Estado|Respuesta Esperada|
|-|-|-|-|
|**Inicio de Sesión**|`POST /api/login`|**200**: Éxito con token **401**: Credenciales incorrectas**422**: Datos inválidos|Devuelve el token de acceso|
|**Listado Paginado**|`GET /api/products?page=1\&per\_page=10\&q=`|**200**: Datos y paginación **401**: Sin token|Lista de productos con metadatos de paginación|
|**Detalle**|`GET /api/products/{id}`|**200**: Encontrado **403**: Sin permiso **404**: No existe|Información detallada del producto.|
|**Creación**|`POST /api/products`|**201**: Creado (con encabezado `Location`)**422**: Datos inválidos|Producto creado exitosamente.|
|**Actualización**|`PUT /api/products/{id}`|**200**: Actualizado **404**: No existe **422**: Datos inválidos|Producto modificado.|
|**Eliminación**|`DELETE /api/products/{id}`|**204**: Eliminado (sin contenido)**404**: No existe**409**: Tiene dependencias|Eliminación del registro.|

# 3\. Datos de Prueba - 5 Registros de Ejemplo

A continuación se presentan 5 registros de prueba por escrito, utilizando la estructura corregida de la entidad `Product`. Estos datos sirven como ejemplo para validar el contrato en las tres tecnologías. Todos los valores son ficticios pero realistas para el contexto de Ferretería UCR.

### Producto 1: Martillo Profesional 16oz

* `id\_Product`: 1
* `id\_category`: 1 (Herramientas Manuales)
* `id\_brand`: 2 (Truper)
* `id\_unit`: 1 (Unidad)
* `Product\_name`: Martillo de Uña Profesional 16oz
* `Description`: Martillo de acero forjado con mango de fibra de vidrio, ideal para trabajos de carpintería y construcción.
* `Price`: 12500.00
* `Stock\_Quantity`: 45
* `minimum\_stock`: 10
* `maximum\_stock`: 100
* `Image\_URL`: https://ferreteria-ucr.com/images/martillo-16oz.jpg
* `sku`: SKU-MART-016-001
* `bar\_code`: 7501234567890
* `tax\_rate`: 0.13
* `Weigth`: 0.45
* `is\_active`: 1

### Producto 2: Taladro Percutor Eléctrico 750W

* `id\_Product`: 2
* `id\_category`: 2 (Herramientas Eléctricas)
* `id\_brand`: 5 (DeWalt)
* `id\_unit`: 1 (Unidad)
* `Product\_name`: Taladro Percutor 750W 1/2"
* `Description`: Taladro percutor de 750W con velocidad variable y reversa, incluye maletín y juego de brocas.
* `Price`: 89500.00
* `Stock\_Quantity`: 18
* `minimum\_stock`: 5
* `maximum\_stock`: 30
* `Image\_URL`: https://ferreteria-ucr.com/images/taladro-750w.jpg
* `sku`: SKU-TAL-750-002
* `bar\_code`: 7509876543210
* `tax\_rate`: 0.13
* `Weigth`: 2.30
* `is\_active`: 1

### Producto 3: Pintura Acrílica Blanca 1 Galón

* `id\_Product`: 3
* `id\_category`: 3 (Pinturas y Acabados)
* `id\_brand`: 3 (Sur)
* `id\_unit`: 4 (Galón)
* `Product\_name`: Pintura Acrílica Blanca Mate 1 Galón
* `Description`: Pintura acrílica de alta cobertura, acabado mate, lavable y resistente a la intemperie para interior y exterior.
* `Price`: 24900.00
* `Stock\_Quantity`: 62
* `minimum\_stock`: 15
* `maximum\_stock`: 120
* `Image\_URL`: https://ferreteria-ucr.com/images/pintura-blanca-1gal.jpg
* `sku`: SKU-PIN-BLA-003
* `bar\_code`: 7501122334455
* `tax\_rate`: 0.13
* `Weigth`: 4.10
* `is\_active`: 1

### Producto 4: Cemento Holcim 50kg

* `id\_Product`: 4
* `id\_category`: 4 (Materiales de Construcción)
* `id\_brand`: 1 (Holcim)
* `id\_unit`: 5 (Saco 50kg)
* `Product\_name`: Cemento Uso General 50kg Holcim
* `Description`: Cemento Portland de uso general para concretos, morteros y blocks, alta resistencia inicial.
* `Price`: 8200.00
* `Stock\_Quantity`: 250
* `minimum\_stock`: 50
* `maximum\_stock`: 500
* `Image\_URL`: https://ferreteria-ucr.com/images/cemento-holcim-50kg.jpg
* `sku`: SKU-CEM-HOL-004
* `bar\_code`: 7505566778899
* `tax\_rate`: 0.13
* `Weigth`: 50.00
* `is\_active`: 1

### Producto 5: Juego de Destornilladores 6 Piezas

* `id\_Product`: 5
* `id\_category`: 1 (Herramientas Manuales)
* `id\_brand`: 4 (Stanley)
* `id\_unit`: 1 (Unidad)
* `Product\_name`: Juego de Destornilladores 6 Piezas
* `Description`: Set de 6 destornilladores planos y Phillips con mango ergonómico antideslizante y punta magnética.
* `Price`: 18750.00
* `Stock\_Quantity`: 8
* `minimum\_stock`: 10
* `maximum\_stock`: 60
* `Image\_URL`: https://ferreteria-ucr.com/images/juego-destornilladores-6pz.jpg
* `sku`: SKU-DES-6PZ-005
* `bar\_code`: 7503344556677
* `tax\_rate`: 0.13
* `Weigth`: 0.95
* `is\_active`: 1

