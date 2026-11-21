# Despliegue de Ferretería Pescado Volador

**IF0009 · Laboratorio 14**

Para el despliegue separamos el frontend y el backend en dos repositorios. Primero preparamos MySQL en Aiven, luego empaquetamos la API Laravel con Docker y configuramos el servicio en Render. La aplicación web y la app Android consumen esa misma API.

## 1. Crear la base de datos MySQL en Aiven

Primero creamos el servicio administrado de MySQL en Aiven. Con los datos de conexión del servicio configuramos el archivo `.env` local del backend:

```dotenv
DB_CONNECTION=mysql
DB_HOST=ferreteria-14993cbb-justins-5402.d.aivencloud.com
DB_PORT=27920
DB_DATABASE=laravel
DB_USERNAME=avnadmin
DB_PASSWORD=<contraseña-vigente-del-servicio>
MYSQL_ATTR_SSL_CA=certs/ca.pem
```

Descargamos el certificado CA de Aiven y lo guardamos como `certs/ca.pem`. Laravel utiliza ese archivo para verificar el certificado del servidor MySQL al conectarse por TLS. Si la ruta relativa no se resuelve en el entorno local, se puede indicar la ruta absoluta del archivo. En Render usamos la ruta dentro del contenedor, `/var/www/html/certs/ca.pem`. [Configuración de base de datos][db] · [Certificado CA][cert].

Desde la raíz del backend ejecutamos el siguiente comando para comprobar la conexión con Aiven, aplicar las migraciones y cargar los datos iniciales. Lo hicimos desde nuestra máquina porque el plan gratuito de Render no ofrece acceso por consola al servicio.

```sh
php artisan migrate --seed
```

Durante la siembra, MySQL impide truncar tablas que otras tablas referencian mediante claves foráneas. Por eso el [`DatabaseSeeder`][seed] desactiva temporalmente esas restricciones y las vuelve a activar después de la carga. Como el seeder vacía tablas, se usa únicamente para inicializar o reiniciar una base de demostración. Para aplicar cambios de esquema en una base con datos que se deben conservar, se ejecutan solo las migraciones.

## 2. Preparar Laravel para producción

Creamos el [`Dockerfile`][docker] en la raíz de `FerreteriaPescadoVolador`. La imagen `serversideup/php:8.4-fpm-nginx` ya incluye PHP, PHP-FPM y Nginx. El Dockerfile instala las dependencias de producción con Composer, activa OPcache y ejecuta la aplicación con el usuario `www-data`. La opción `AUTORUN_ENABLED=true` permite que la imagen ejecute las tareas de arranque de Laravel.

Agregamos [`.dockerignore`][dockerignore] para excluir el `.env`, la carpeta `.git`, las dependencias instaladas localmente, los tests y los archivos temporales. Render recibe las variables de entorno desde su panel. El Dockerfile actual usa una sola etapa y queda pendiente medir el tamaño de la imagen.

### Proxy de confianza

Render recibe la conexión HTTPS y reenvía la solicitud al contenedor. Para que Laravel reconozca el esquema original y genere correctamente los enlaces, incluidos los de paginación, agregamos esta línea en el bloque `withMiddleware` de [`bootstrap/app.php`][bootstrap]:

```php
$middleware->trustProxies(at: '*');
```

### CORS

Como la web y la API tienen dominios distintos, publicamos la configuración de CORS con `php artisan config:publish cors`. El archivo [`config/cors.php`][cors] publicado todavía permite cualquier origen. Para que el dominio se pueda cambiar desde Render, queda por aplicar esta configuración:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => array_values(array_filter(array_map(
    'trim',
    explode(',', env('CORS_ALLOWED_ORIGINS', ''))
))),
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

En `CORS_ALLOWED_ORIGINS` se registra el origen público del frontend, con `https://` y sin barra final. Si la app Android envía solicitudes desde el WebView, también se agrega su origen, normalmente `https://localhost`. Los orígenes se separan con comas. Las rutas protegidas reciben el token Bearer de Sanctum en el encabezado `Authorization`.

### Probar la imagen en local

Desde la raíz del backend podemos construir la imagen, iniciarla con el `.env` local y comprobar que Laravel responde. La variable del certificado se ajusta a la ruta que existe dentro del contenedor.

```sh
docker build -t api-equipo .
docker run --rm -p 8080:8080 --env-file .env -e MYSQL_ATTR_SSL_CA=/var/www/html/certs/ca.pem api-equipo
curl -i http://localhost:8080/up
```

El resultado esperado para `/up` es HTTP `200`.

## 3. Desplegar la API en Render

Creamos un **Web Service** y conectamos el repositorio del backend. Render construye la imagen a partir del Dockerfile con la siguiente configuración:

| Opción | Valor |
| --- | --- |
| Language | Docker |
| Root Directory | Vacío, porque el Dockerfile está en la raíz |
| Instance Type | Free |
| Health Check Path | `/up` |
| Auto-Deploy | Off, el despliegue se solicita desde GitHub Actions |

En **Environment** cargamos las variables de Aiven, la ruta del certificado dentro del contenedor y los valores de Laravel para producción. Entre ellos están `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL=https://ferreteriapescadovolador.onrender.com` y `PORT=8080`. La clave de Laravel y la contraseña de MySQL se guardan en el panel de Render.

En **Settings → Deploy Hook** copiamos la URL del hook y la guardamos en el repositorio del backend como secreto `RENDER_DEPLOY_HOOK`. En el registro del primer despliegue se puede comprobar el arranque del contenedor y la ejecución de las migraciones.

### Verificar el servicio

```sh
curl -i https://ferreteriapescadovolador.onrender.com/up
curl -i -H "Accept: application/json" https://ferreteriapescadovolador.onrender.com/api/products
```

La petición a `/up` comprueba que Laravel responde. También sirve para reactivar el servicio Free después de 15 minutos sin tráfico. La primera respuesta puede tardar alrededor de un minuto. [Plan gratuito de Render][render].

Al probar `/api/products` se reportó un error `500`. Como la ruta está protegida por Sanctum, una petición sin token debería devolver `401`. Con `APP_DEBUG=false`, Laravel oculta el detalle del error al cliente. La causa del `500` se debe revisar en los registros de Render. [Rutas de la API][routes].

## 4. Publicar el frontend web

Para configurar la publicación en **Cloudflare Pages**, como se describe en el procedimiento del laboratorio, se entra a **Workers & Pages → Create → Pages → Connect to Git** y se selecciona [`Front_ Ferreteria`][front]. El frontend web usa JavaScript y Vite. Sus páginas de catálogo, inicio de sesión y ventas se generan en `dist/`. [Configuración de compilación de Cloudflare Pages][pages].

| Opción | Valor |
| --- | --- |
| Rama de producción | `main` |
| Directorio raíz | Raíz del repositorio |
| Comando de compilación | `npm run build` |
| Directorio de salida | `dist` |
| `NODE_VERSION` | `24`, para usar la misma versión mayor que en el CI del frontend |
| `VITE_API_URL` | `https://ferreteriapescadovolador.onrender.com/api` |

Vite incorpora `VITE_API_URL` al compilar. Si la URL cambia, hay que generar y publicar un nuevo build. La compilación local de producción generó **48 984 bytes** en seis archivos. En la descripción inicial del proyecto se indicó Vercel y en el procedimiento se indicó Cloudflare Pages. Queda pendiente añadir la URL pública para identificar el proveedor del despliegue activo.

## 5. Configurar la integración y el despliegue continuos

En el backend configuramos [`.github/workflows/ci.yml`][ci] con tres trabajos:

1. **`api`.** Prepara PHP y Node.js, instala las dependencias, genera un `.env` temporal, compila los recursos de Laravel y ejecuta `php artisan test`. Las pruebas usan SQLite en memoria, aisladas de MySQL en Aiven. [Configuración de pruebas][phpunit].
2. **`web`.** Descarga la rama `main` de `Front_Ferreteria` en la carpeta temporal `web/`. Después instala las dependencias con `npm ci` y compila el proyecto con `npm run build`. El acceso al repositorio se realiza mediante `FRONTEND_READ_TOKEN`.
3. **`deploy`.** En un push a `main` del backend, solicita el despliegue a Render mediante el hook si `api` y `web` fueron exitosos. Si uno de esos trabajos falla, GitHub Actions omite el despliegue.

Para crear `FRONTEND_READ_TOKEN`, en la cuenta de GitHub usamos **Settings → Developer settings → Personal access tokens → Fine-grained tokens**. Limitamos el token al repositorio del frontend y damos permiso de lectura a **Contents**. En el repositorio del backend lo guardamos en **Settings → Secrets and variables → Actions → New repository secret**.

El frontend también tiene su propio workflow en `.github/workflows/ci.yaml`. Instala Node.js 24, ejecuta `npm ci` y verifica el build en los pushes y pull requests hacia `main`. Ese workflow verifica la compilación. La publicación web se gestiona desde el proveedor de alojamiento.

En la [ejecución del PR #64][green] pasaron `api` y `web`, con **224 pruebas aprobadas y 951 aserciones**. El despliegue se omitió porque era un pull request. La [ejecución fallida del flujo anterior][red] registró un error de formato. Para completar la evidencia de la rúbrica queda adjuntar una ejecución del flujo actual que muestre una prueba fallida y el despliegue omitido, así como el registro de un despliegue exitoso en Render.

## 6. Preparar la aplicación Android

El proyecto móvil usa Ionic, Vue y Capacitor. Ya incluye el directorio `android/` y Capacitor toma los archivos web desde `dist/`. Para preparar la versión de producción, se configura `VITE_API_URL=https://ferreteriapescadovolador.onrender.com/api` .

Desde la raíz del proyecto móvil se ejecutan:

```sh
npm run build
npx cap sync android
npx cap open android
```

En Android Studio se puede generar un **APK firmado** desde **Build → Generate Signed Bundle / APK → APK**, usando el almacén de claves del equipo. Después se instala el APK en un dispositivo o emulador y se verifica el inicio de sesión contra la API pública. Para la entrega se puede adjuntar el APK a una versión de GitHub Releases y añadir su enlace a este documento. Queda pendiente incorporar el enlace o el archivo firmado. [Android con Capacitor][android].

