# Lab07 – Guía de Interfaz del Proyecto – Ferretería Pescado Volador

**Fecha:** Jueves 01 de octubre, 2026 (Semana 08)
**Tema 4:** Diseño inclusivo y experiencia del usuario
**Archivo:** `docs/lab07-guia-ui.md`
**Stack:** Laravel 12 + Inertia v3 + Vue 3 + Tailwind CSS 4 (`resources/css/app.css`, `resources/js/pages/`)
**Paleta elegida:** Combinación 7 Pizarra urbana / Urban slate
**Fuente elegida:** Clarity City (variable, Google Fonts)

Esta guía rige todo el desarrollo front-end. Si un componente no sigue esta guía, no se mergea.

---

## 1. Paleta de color – Pizarra urbana (contraste verificado WCAG 2.1 AA)

Colores base del equipo (no se agregan otros sin aprobación):

| Token | HEX | Rol en la ferretería |
|---|---|---|
| `niebla` | `#E9E6E7` | Fondo app / superficie clara |
| `pizarra` | `#5E5653` | Primario: header, nav, botón principal, texto principal oscuro |
| `acero` | `#7B7F8A` | Secundario: bordes, iconos, header secundario, badges neutros |
| `arena` | `#AB978C` | Acento cálido: resaltados, hover suave, separadores, ilustración |
| `bruma` | `#6B7C98` | Acento frío: enlaces, foco, badges info, total destacado |

Regla WCAG: texto normal ≥ 4.5:1, texto grande (≥18pt / 14pt bold) ≥ 3:1. Verificado con WebAIM Contrast Checker.

### 1.1 Combinaciones aprobadas (las únicas permitidas para texto)

| Combinación | Ratio aprox. | Nivel | Uso permitido |
|---|---|---|---|
| `#5E5653` sobre `#FFFFFF` | 7.3:1 | AAA | Texto principal, labels, botones secundarios |
| `#FFFFFF` sobre `#5E5653` | 7.3:1 | AAA | Header, nav, botón primario Agregar / Confirmar |
| `#5E5653` sobre `#E9E6E7` | 6.5:1 | AA | Body sobre fondo niebla, cards |
| `#1A1A1A` sobre `#E9E6E7` | 14.5:1 | AAA | Precios, totales (casi negro para máxima legibilidad en bodega) |
| `#1A1A1A` sobre `#AB978C` | 7.0:1 | AAA | Badge arena con texto oscuro (ej. "Poco stock") |
| `#FFFFFF` sobre `#6B7C98` oscurecido a `#4A5A73` para botón* | 7.0:1 | AAA | Enlaces y foco. Ver nota abajo |
| `#FFFFFF` sobre `#7B7F8A` oscurecido a `#4E525B` para texto* | 7.5:1 | AAA | Texto secundario. Ver nota abajo |

> \* Nota honesta de diseño inclusivo (Tema 4): `#7B7F8A` y `#AB978C` y `#6B7C98` solos sobre blanco dan ~3.5–4.1:1, **no pasan** para texto pequeño. Por eso en esta guía:
> - `acero #7B7F8A` y `bruma #6B7C98` se usan tal cual solo para: bordes, iconos ≥24px, texto grande (≥18pt), fondos decorativos.
> - Para texto pequeño se usan sus versiones oscurecidas `#4E525B` y `#4A5A73` (mismo tono, más luminancia baja), o texto oscuro `#1A1A1A / #5E5653` encima de `arena / niebla`.
> - Error y éxito no vienen en la paleta: se agregan solo semánticos `#B91C1C` (texto error 5.9:1 sobre blanco) y `#166534` (texto stock 7.2:1), siempre con icono + texto, nunca solo color.

Prohibido:
- Texto `#7B7F8A` o `#AB978C` directo sobre blanco en 12–14px.
- Botón `#6B7C98` con texto blanco en 14px (no llega a 4.5:1). El CTA de compra es `#5E5653` con texto blanco.
- Comunicar stock solo con color. Siempre color + icono + texto ("En stock", "Solo 3", "Agotado").

Modo oscuro: `surface #1F1D1C`, texto `#E9E6E7` (12:1), primario `#AB978C` como acento claro sobre oscuro.

## 2. Tipografía – Clarity City

Fuente única del proyecto: **Clarity City**, sans-serif variable (100–900, italic 0–1). Elegida por alta legibilidad en etiquetas SKU/barcode y números tabulares para precios.

Embed en `<head>` (`resources/views/app.blade.php`):

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Clarity+City:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
```

CSS base (`resources/css/app.css`, Tailwind 4):

```css
@theme inline {
  --font-sans: "Clarity City", ui-sans-serif, system-ui, sans-serif;
}
.clarity { font-family: "Clarity City", sans-serif; font-optical-sizing: auto; }
.price { font-variant-numeric: tabular-nums; }
```

### Escala tipográfica (todos Clarity City)

| Nivel | Size / Weight / Line-height | Uso |
|---|---|---|
| `display` | 32px / 700 / 40px | H1 por pantalla ("Catálogo", "Carrito"). Uno solo por vista |
| `h2` | 24px / 600 / 32px | Secciones ("Filtros", "Resumen de compra") |
| `h3` | 20px / 600 / 28px | Nombre producto, títulos de diálogo |
| `body` | 16px / 400 (normal) / 24px | Descripciones, tablas. Mínimo web 16px |
| `small` | 14px / 400 / 20px | Marca, unidad, SKU, meta |
| `caption` | 12px / 500 / 16px | Labels de formulario (siempre visibles, nunca solo placeholder), badges |
| `price` | 20px / 700 / 28px tabular-nums | Precios y total `₡ 1.250,00` + `aria-label` para lector |

Reglas inclusivas:
- Zoom 200% sin scroll horizontal, interlineado ≥1.5, línea ≤75 caracteres.
- Enlaces subrayados siempre, peso 600, color `#4A5A73` (versión accesible de bruma).
- Pesos variables: 400 lectura, 500 labels, 600–700 títulos/CTA. No usar 100–300 para texto (fino no legible en móvil).

## 3. Espaciado y retícula

Base 4px (Tailwind). Escala permitida: `4, 8, 12, 16, 24, 32, 48, 64`.

- Contenedor: `max-w-7xl mx-auto px-4 md:px-6` (fondo `niebla #E9E6E7`, cards blancas).
- Grid catálogo: `grid gap-4 md:gap-6 grid-cols-2 md:grid-cols-3 lg:grid-cols-4`. Botón Agregar 44×44px.
- Ritmo: H1 `mb-6`, sección `mb-4`, campos `gap-4`, ayuda/error `mt-1`.
- Radios: cards `rounded-lg` (8px), botones/inputs `rounded-md` (6px), badges `rounded-full`.
- Bordes: `1px #7B7F8A` (acero) en cards/inputs claros; divisores suaves `1px #AB978C/40` (arena).
- Foco visible global: `outline: 3px solid #6B7C98; outline-offset: 2px` en `:focus-visible`. Nunca `outline:none` sin reemplazo.

## 4. Estados de los componentes (con Pizarra urbana)

### 4.1 Botón primario – Agregar / Confirmar venta (fondo `pizarra`)

| Estado | Fondo | Texto | Extra |
|---|---|---|---|
| Normal | `#5E5653` | `#FFFFFF` (7.3:1 AAA) | — |
| Hover | `#4A4442` (pizarra 15% más oscura) | `#FFFFFF` | `cursor:pointer` |
| Foco | `#5E5653` | `#FFFFFF` | anillo `3px #6B7C98` + `offset 2px` |
| Deshabilitado | `#E9E6E7` | `#7B7F8A` oscurecido `#4E525B` | `cursor:not-allowed`, `aria-disabled` |
| Error (bloqueo por formulario) | — | banner `#FEE2E2` + texto `#7F1D1D` encima del botón | ej. "Te faltan 2 unidades de stock (409)" |

Secundario (Seguir comprando, Cancelar): fondo `#FFFFFF`, texto `#5E5653`, borde `1px #5E5653`. Foco igual. Peligro (Eliminar, Anular): fondo `#B91C1C`, texto blanco, siempre con confirmación.

### 4.2 Input – buscador, cantidad, cliente, pago (fondo blanco sobre niebla)

| Estado | Borde | Fondo | Ayuda |
|---|---|---|---|
| Normal | `1px #7B7F8A` | `#FFFFFF` | label Clarity City 12px/500 en `#5E5653` siempre visible |
| Foco | `2px #6B7C98` | `#FFFFFF` | + anillo `3px #6B7C98/30` |
| Error (`aria-invalid`, `aria-describedby`) | `2px #B91C1C` | `#FFFFFF` | texto `#7F1D1D` + ⚠ + mensaje 422 en español ("Cantidad debe ser ≥ 1") |
| Deshabilitado | `1px #E9E6E7` | `#E9E6E7` | texto `#4E525B`, `disabled` nativo |

Cantidad POS: `min=1 max=stock` + botones `−/+` 44px. Si `cantidad > stock` → error inline (R6 Lab04, 409), no deja confirmar.

### 4.3 Card producto / Badge stock (arena + bruma)

- Normal: card blanca, borde `#E9E6E7`, precio tabular, badge info `bruma #6B7C98/15` + texto `#4A5A73`.
- Poco stock (`stock <= minimum_stock`, R2): badge `arena #AB978C` + texto `#1A1A1A` "¡Solo quedan 3!".
- En stock: badge verde `#DCFCE7` + `#14532D` + icono ✓.
- Agotado: imagen 60% opacidad, botón disabled "Agotado".
- Skeleton (Inertia `defer`): `bg #E9E6E7 animate-pulse`.

Mapeo API→UI: `422` error por campo, `404` vacío ilustrado, `409` (R1–R7) banner con texto backend, `401` a login, `500` genérico sin traza.

---

## 5. Mapa de navegación (coherente con `routes/api.php` + roles del proyecto)

Fuente de roles: `database/seeders/RolePermissionSeeder.php:20-70` y `docs/openapi.yaml:12`.
Front separado en `FerreteriaFront/src` (Vue + Vite). `routes/web.php:5` solo tiene `/`; las rutas `/sales`, `/bodega` son rutas Vue a crear, no Laravel. Sin carrito por decisión del equipo.

| Recurso API | Endpoint real | Roles con acceso (según API) | Pantalla que lo usa |
|---|---|---|---|
| Productos ver | `GET /api/products`, `GET /api/products/{id}` | admin, cajero, bodeguero | P1 Catálogo (todos) |
| Productos crear/editar | `POST /api/products`, `PUT /api/products/{id}` | admin, bodeguero | P3 Bodega |
| Categorías/marcas/unidades | `GET /api/categories`, `/brands`, `/units` | admin (ver) | P1 filtros, P3 alta |
| Ventas | `GET /api/sales`, `POST /api/sales`, `GET /api/sales/{id}` | ver/crear: admin, cajero | P2 Ventas + Historial (venta directa, sin carrito) |
| Clientes | `GET /api/customers` | admin, cajero | P2 selector cliente |
| Pagos | `POST /api/payments`, `GET /api/payments` | admin (cajero cobra vía venta) | P2 cobro directo |
| Compras ver | `GET /api/purchases`, `GET /api/purchases/{purchase}/items` | admin, **bodeguero** | P3 Bodega (no solo admin) |
| Compras crear | `POST /api/purchases` | admin | P3 Bodega (solicitar/reponer) |
| Proveedores | `GET/POST/PUT /api/suppliers` | admin, **bodeguero** | P3 Bodega (no solo admin) |
| Reporte 1 | `GET /api/products/inventory-summary` | admin, cajero, bodeguero | P3 Reportes |
| Reporte 2 | `GET /api/products?low_stock=1` + `GET /api/purchases/{purchase}/items` | admin, bodeguero | P3 Reportes |

```
[Login / Registro] (Vue) — POST /api/login, POST /api/register (rol por defecto: cajero)
        |
        +-- [P1 Catálogo] (/, todos: admin/cajero/bodeguero)
        |    GET /api/products?q=&category_id=&brand_id=, /categories, /brands
        |
        +-- [P2 Ventas] (/sales, rol cajero + admin, sin carrito)
        |    Venta directa: producto + cantidad + cliente + pago.
        |    GET /api/customers, POST /api/sales + /sale-items + /payments
        |    +-- [Historial + Detalle] (/sales, /sales/{id})
        |
        +-- [P3 Bodega] (/bodega, rol bodeguero + admin) ← corrige observación Lab7
             GET/POST /api/purchases, GET purchases/{id}/items,
             GET/POST/PUT /api/suppliers,
             Reporte 1 inventory-summary + Reporte 2 low_stock/detalle compra
```

Flujo cajero (sin carrito): Catálogo → Ver detalle → Venta directa (cantidad + cliente + pago mixto R7) → Confirmar → descuenta stock + salida (R6) → Historial.
Flujo bodeguero: Bodega → ver compras/proveedores → registrar proveedor/producto → consultar Reporte 1 (por categoría) y Reporte 2 (bajo stock + detalle compra) → reponer.
Accesibilidad: skip-link, `header/nav/main/footer`, breadcrumb, foco atrapado en modal, `aria-live="polite"` en total.

## 6. Bosquejos de las tres pantallas principales

Estilo Pizarra urbana: header `pizarra #5E5653` texto blanco, fondo `niebla #E9E6E7`, cards blancas, CTA `pizarra`, enlaces/foco `bruma #6B7C98`, detalles cálidos `arena #AB978C`. Fuente todo Clarity City.

### P1 – Catálogo principal (`/`, todos ven productos normal)

Todos (admin/cajero/bodeguero) tienen `view products` y ven lo mismo: foto, nombre, marca, precio, stock.
Diferencia por permiso (se muestra/oculta en el front según rol del token):
- Cajero: solo [Ver].
- Bodeguero + admin: [Ver] + [Editar] + [Nuevo producto] (`create/update products`, `RolePermissionSeeder.php:62-66`).

```
+--------------------------------------------------+
| HEADER #5E5653 bl: [Logo PV] (Buscar SKU...)      |
|  [Catálogo] [Ventas] [Bodega]                    |
+--------------------------------------------------+
| H1 Catálogo 32/700             (Orden: Precio v)  |
| [Filtros] {Herramienta x} {Truper x}              |
+--------------------------------------------------+
| +----------+  +----------+  +----------+         |
| | [img]    |  | [img]    |  | [img]    |         |
| | Martillo |  | Taladro  |  | Cemento  | h3 20/600|
| | Truper·u |  | Bosch·u  |  | Cruz·50kg| 14px   |
| | ₡4.500   |  | ₡32.000  |  | ₡6.200   | tabular|
| | {En stock}|  |{Solo 3!}|  | {Agotado}| arena  |
| | [Ver]    |  | [Ver]    |  |[Ver]     | #5E5653|
| | [*Editar solo admin/bodeguero]                  |
| +----------+  +----------+  +----------+         |
| < 1 2 3 ... 5 > (meta/links Lab05)               |
+--------------------------------------------------+
```

API: `GET /products`, `categories`, `brands`, `inventory-summary`. Filtros `fieldset/legend`, `role=status "12 productos"`, `alt` real.

### P2 – Ventas / Punto de venta directo + Historial (`/sales`, solo cajero + admin, sin carrito)

Bodeguero no entra aquí (403, no tiene `view/create sales`). Cajero solo ve sus ventas; admin todas.

```
+--------------------------------------------------+
| H1 Ventas Clarity 32/700       [Nueva venta]      |
+-------------------------------+------------------+
| Venta directa: [Producto v]   | HISTORIAL         |
|  (Cantidad 2) (SKU/pistola)   | #1024 Juan ₡14.012 |
|  Cliente: (Juan v) [+ Nuevo]  | #1023 Most ₡3.200  |
|  Pago: (Efectivo v) (Monto)   | [Ver detalle]     |
|  TOTAL ₡14.012 [Confirmar]    | < 1 2 >           |
+-------------------------------+------------------+
| > Error: "Solo hay 3 taladros" (409 R6)           |
+--------------------------------------------------+
```

API: `POST /sales` → `/sale-items` (R6 stock, R4 subtotal) → `/payments` (R7) en transacción. Barcode de `modelo.md` al input SKU. Total `aria-live`.

Historial dentro de P2 (`/sales`, cajero ve solo las suyas, admin todas; otro usuario → 404):

```
+--------------------------------------------------+
| H1 Ventas  (Buscar #/cliente) [Hoy]               |
+--------------------------------------------------+
| #1024 | 01-oct 10:24 | Juan M. | ₡14.012 |{Pagada}|
|  [Ver]                                            |
| #1023 | 01-oct 09:12 | Most.  | ₡3.200 |{Parcial}|
| < 1 2 >                                           |
+--------------------------------------------------+
| DETALLE #1024: cliente, cajero, fecha             |
|  2x Martillo ₡9.000  50x Tornillo ₡3.400          |
|  Pagos: Efectivo ₡10.000 + Tarjeta ₡4.012         |
|  [Imprimir] [Nueva venta]                         |
+--------------------------------------------------+
```

API: `GET /api/sales` + `GET /api/sales/{id}` + `payments`. Tabla con `th scope`, estados con texto.

### P3 – Bodega: Compras + Proveedores + 2 Reportes (`/bodega`, solo bodeguero + admin)

Cajero no entra aquí (403, no tiene `view purchases/suppliers`). Admin tiene todo; bodeguero ver + crear/editar proveedores y crear/editar productos, pero no puede eliminar (solo admin) ni crear compras (solo admin crea, bodeguero consulta).

```
+--------------------------------------------------+
| H1 Bodega              [Nueva compra] [+ Proveedor]|
+--------------------------------------------------+
| Tabs: [Compras] [Proveedores] [Reportes]          |
| Compras: #88 | 01-oct | Truper | ₡120.000 |{Recib.}|
|  [Ver detalle -> items]                           |
| Proveedores: Truper | 8888-0000 [Ver] [Editar]    |
+--------------------------------------------------+
| REPORTES (los 2 del proyecto):                    |
| R1 Inventario por categoría (inventory-summary):  |
|  Herramientas 45 · Pinturas 62 · Cemento 250      |
| R2 Bajo stock + Detalle compra:                   |
|  Cemento 2 (min 10) [Reponer] · Compra #88 = 10   |
|  items (GET purchases/88/items)                   |
+--------------------------------------------------+
```

API: `GET /api/purchases` + `GET /api/purchases/{purchase}/items` (bodeguero + admin, `api.php:50-51`), `GET/POST/PUT /api/suppliers` (bodeguero + admin, `api.php:53`), `GET /api/products/inventory-summary` (R1, `api.php:36`) + `GET /api/products?low_stock=1` (R2). Corrección Lab7: compras y proveedores NO son solo admin; el rol bodeguero sí tiene acceso según `RolePermissionSeeder.php:62-70` y `openapi.yaml:1414,1748`.

---

## 7. Checklist Lab07 (corrección observación 85→100)

- [x] Guía 50/50 sin observaciones (se mantiene paleta + Clarity City).
- [x] Mapa con roles: cajero (`view/create sales`), bodeguero (`view purchases`, `view/create/update suppliers`), admin total. Compras/proveedores ya no son "solo admin".
- [x] 2 reportes: R1 `inventory-summary` por categoría + R2 `low_stock` + detalle `purchases/{id}/items`.
- [ ] Subir `docs/lab07-guia-ui.md` al repo (la vez pasada solo estaba en Mediación Virtual).
- [ ] Foco `#6B7C98` 3px, táctil 44px, errores español, sin solo-color.

Verificación: `npm run build`, `php artisan route:list --path=api`, re-chequeo WebAIM antes de Mediación Virtual.
