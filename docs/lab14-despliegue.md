# Despliegue de Ferretería Pescado Volador

**IF0009 · Laboratorio 14**

Para el despliegue separamos el frontend y el backend en dos repositorios. Primero preparamos MySQL en Aiven, luego empaquetamos la API Laravel con Docker y configuramos el servicio en Render. Publicamos el frontend en Cloudflare Pages y configuramos GitHub Actions para comprobar la aplicación antes de solicitar el despliegue.

La aplicación web y la app Android están configuradas para consumir la misma API pública.

## Arquitectura de despliegue

| Componente | Servicio | Función |
|---|---|---|
| Base de datos | Aiven MySQL | Almacena los datos de la aplicación y recibe conexiones cifradas desde Laravel. |
| Backend | Render | Ejecuta la API Laravel dentro de un contenedor Docker. |
| Frontend | Cloudflare Pages | Publica los archivos estáticos generados con Vite. |
| Aplicación Android | Ionic y Capacitor | Consume la API pública desde el dispositivo. |
| Integración y despliegue continuos | GitHub Actions | Ejecuta las pruebas, compila y solicita el despliegue mediante el hook de Render. |

El navegador carga la web desde Cloudflare Pages y realiza solicitudes HTTPS a la API de Render. Laravel se conecta con MySQL en Aiven mediante TLS. Las credenciales de la base de datos se administran en el backend.

## 1. Crear la base de datos MySQL en Aiven

Primero creamos el servicio administrado de MySQL en Aiven. Con los datos de conexión configuramos el archivo `.env` local del backend:

```dotenv
DB_CONNECTION=mysql
DB_HOST=ferreteria-14993cbb-justins-5402.d.aivencloud.com
DB_PORT=27920
DB_DATABASE=laravel
DB_USERNAME=avnadmin
DB_PASSWORD=<contraseña-vigente-del-servicio>
MYSQL_ATTR_SSL_CA=certs/ca.pem
```

Descargamos el certificado CA de Aiven y lo guardamos como `certs/ca.pem`. Laravel utiliza ese certificado para verificar el servidor MySQL durante la conexión cifrada. Si la ruta relativa no se resuelve en el entorno local, se debe indicar la ruta absoluta del archivo.

Dentro del contenedor, la ruta del certificado es:

```dotenv
MYSQL_ATTR_SSL_CA=/var/www/html/certs/ca.pem
```

Desde la raíz del backend ejecutamos el siguiente comando para comprobar la conexión, aplicar las migraciones y cargar los datos iniciales:

```powershell
php artisan migrate --seed
```

Realizamos esta carga inicial desde nuestra máquina porque el plan gratuito de Render no ofrece acceso por consola al servicio.

Durante la siembra, MySQL impide truncar tablas referenciadas mediante claves foráneas. Por eso el `DatabaseSeeder` desactiva temporalmente esas restricciones y las vuelve a activar después de la carga.

Como el seeder vacía tablas, se utiliza para inicializar o reiniciar una base de demostración. Para aplicar cambios de esquema conservando los datos existentes, se ejecutan las migraciones.

## 2. Preparar Laravel para producción

Creamos el `Dockerfile` en la raíz de `FerreteriaPescadoVolador`. Utilizamos la imagen `serversideup/php:8.5-fpm-nginx-alpine`, que incluye PHP, PHP-FPM y Nginx sobre una base Alpine.

La construcción tiene dos etapas:

1. **Preparación.** Instala las dependencias PHP de producción, copia el código, optimiza la autocarga y ejecuta el descubrimiento de paquetes de Laravel.
2. **Producción.** Recibe la aplicación preparada y la ejecuta con el usuario `www-data`.

```dockerfile
# Etapa 1: preparar Laravel y sus dependencias
FROM serversideup/php:8.5-fpm-nginx-alpine AS preparacion

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist

COPY --chown=www-data:www-data . .

RUN composer dump-autoload --optimize --no-dev --no-scripts \
    && php artisan package:discover --ansi

# Etapa 2: ejecutar la aplicación
FROM serversideup/php:8.5-fpm-nginx-alpine AS produccion

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true

WORKDIR /var/www/html

COPY --from=preparacion --chown=www-data:www-data \
    /var/www/html /var/www/html

USER www-data

EXPOSE 8080
```

PHP 8.5 se utiliza tanto en el contenedor como en el CI del backend. La opción `AUTORUN_ENABLED=true` activa las tareas de arranque de Laravel, incluidas las migraciones y la preparación de cachés.

Agregamos `.dockerignore` para excluir las variables locales, las dependencias instaladas en nuestra máquina y los archivos temporales:

```dockerignore
.env
.env.*
.git
node_modules
vendor
bootstrap/cache/*.php
public/hot
public/storage
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
tests
```

Las variables sensibles de producción se cargan desde el panel de Render.

### Proxy de confianza

Render recibe la conexión HTTPS y reenvía la solicitud al contenedor. Para que Laravel reconozca el esquema original y genere correctamente los enlaces, agregamos esta línea en el bloque `withMiddleware` de `bootstrap/app.php`:

```php
$middleware->trustProxies(at: '*');
```

### CORS

Como la web y la API tienen dominios diferentes, publicamos la configuración de CORS:

```powershell
php artisan config:publish cors
```

En `config/cors.php` configuramos los orígenes permitidos mediante una variable de entorno:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'],

'allowed_origins' => array_values(array_filter(array_map(
    'trim',
    explode(',', env('CORS_ALLOWED_ORIGINS', ''))
))),

'allowed_origins_patterns' => [],

'allowed_headers' => ['*'],

'supports_credentials' => false,
```

En Render configuramos los orígenes de la web y de Android con Capacitor:

```dotenv
CORS_ALLOWED_ORIGINS=https://ferreteria-front.pages.dev,https://localhost
```

Los orígenes se separan con comas y se escriben sin barra final. Las rutas protegidas reciben el token Bearer de Sanctum en el encabezado `Authorization`.

### Construcción y prueba local

Desde la raíz del backend construimos la imagen:

```powershell
docker build -t ferreteria-api:lab14 .
```

Después comprobamos el usuario de ejecución y el tamaño:

```powershell
docker image inspect ferreteria-api:lab14 --format '{{.Config.User}}'
docker image inspect ferreteria-api:lab14 --format '{{.Size}}'
```

Los resultados fueron:

| Comprobación | Resultado |
|---|---|
| Usuario de ejecución | `www-data` |
| Tamaño de la imagen local | 58 226 592 bytes |
| Tamaño aproximado | 55,53 MiB |

Iniciamos el contenedor con el `.env` local y ajustamos la ruta del certificado:

```powershell
docker run --rm --name ferreteria-lab14 -p 8080:8080 --env-file .env -e MYSQL_ATTR_SSL_CA=/var/www/html/certs/ca.pem ferreteria-api:lab14
```

Desde otra terminal consultamos la ruta de salud:

```powershell
curl.exe -i http://localhost:8080/up
```

La respuesta fue `HTTP/1.1 200 OK`, con el mensaje `Application up`. Esto confirmó que Laravel arrancó y respondió dentro del contenedor.

## 3. Desplegar la API en Render

Creamos un **Web Service** y conectamos el repositorio del backend. Render construye la imagen desde el Dockerfile con la siguiente configuración:

| Opción | Valor |
|---|---|
| Language | Docker |
| Root Directory | Vacío, porque el Dockerfile está en la raíz |
| Instance Type | Free |
| Health Check Path | `/up` |
| Auto-Deploy | Off |

En **Environment** cargamos las variables de Aiven, la ruta del certificado y los valores de Laravel para producción:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ferreteriapescadovolador.onrender.com
PORT=8080
MYSQL_ATTR_SSL_CA=/var/www/html/certs/ca.pem
CORS_ALLOWED_ORIGINS=https://ferreteria-front.pages.dev,https://localhost
```

También configuramos `APP_KEY` y las variables `DB_*`. La clave de Laravel y la contraseña de MySQL se guardan como valores sensibles en Render.

En **Settings → Deploy Hook** copiamos la URL del hook y la guardamos en GitHub Actions como secreto `RENDER_DEPLOY_HOOK`.

Después de integrar el pull request #67, Render registró el despliegue del commit `787a82a` con el estado **Deploy live**. El evento indicó **Triggered via Deploy Hook**.

### Verificación pública

Comprobamos la ruta de salud de la API:

```powershell
curl.exe -i https://ferreteriapescadovolador.onrender.com/up
```

La respuesta fue:

```text
HTTP/1.1 200 OK
```

Después consultamos una ruta protegida, indicando el origen del frontend:

```powershell
curl.exe -i -H "Accept: application/json" -H "Origin: https://ferreteria-front.pages.dev" https://ferreteriapescadovolador.onrender.com/api/products
```

Obtuvimos:

```text
HTTP/1.1 401 Unauthorized
access-control-allow-origin: https://ferreteria-front.pages.dev
```

El cuerpo de la respuesta indicó:

```json
{"message":"No autenticado."}
```

Estas respuestas comprobaron que Laravel está accesible por HTTPS, que la ruta exige autenticación y que la API devuelve el origen permitido del frontend.

El servicio Free puede suspenderse después de 15 minutos sin tráfico. La primera solicitud posterior puede tardar mientras vuelve a iniciar.

## 4. Publicar el frontend web

Publicamos el repositorio `ismaeltorrentes-lang/Ferreteria_Front` en **Cloudflare Pages**, conectándolo mediante **Workers & Pages → Create → Pages → Connect to Git**.

El frontend utiliza JavaScript y Vite. La compilación genera las páginas de catálogo, ventas, historial e inicio de sesión.

| Opción | Valor |
|---|---|
| Rama de producción | `main` |
| Directorio raíz | Raíz del repositorio |
| Comando de compilación | `npm run build` |
| Directorio de salida | `dist` |
| `NODE_VERSION` | `24` |
| `VITE_API_URL` | `https://ferreteriapescadovolador.onrender.com/api` |

La dirección pública de la web es:

[https://ferreteria-front.pages.dev/](https://ferreteria-front.pages.dev/)

Vite incorpora `VITE_API_URL` durante la compilación. Si cambia la dirección de la API, se debe generar y publicar un nuevo build.

### Validación de la configuración

En `vite.config.js` agregamos una validación para detener la compilación cuando `VITE_API_URL` esté vacía:

```javascript
const env = loadEnv(mode, process.cwd(), 'VITE_')

if (command === 'build' && !env.VITE_API_URL?.trim()) {
  throw new Error(
    'Falta VITE_API_URL. Configura la dirección de la API antes de compilar.'
  )
}
```

Comprobamos este comportamiento desde PowerShell:

```powershell
$env:VITE_API_URL = " "
npm run build
```

La compilación se detuvo con el mensaje definido. Después eliminamos la variable temporal y compilamos utilizando el `.env`:

```powershell
Remove-Item Env:VITE_API_URL
npm run build
```

### Tamaño del frontend

Medimos los archivos generados con:

```powershell
(Get-ChildItem dist -Recurse -File | Measure-Object Length -Sum).Sum
```

El resultado fue **51 686 bytes**, aproximadamente **50,47 KiB**, para el contenido de `dist` sin comprimir.

## 5. Configurar la integración y el despliegue continuos

En el backend configuramos `.github/workflows/ci.yml` con tres trabajos:

1. **`api`.** Prepara PHP 8.5 y Node.js 22, instala las dependencias, genera un `.env` temporal, compila los recursos de Laravel y ejecuta `php artisan test`.
2. **`web`.** Descarga la rama `main` de `ismaeltorrentes-lang/Ferreteria_Front` en la carpeta temporal `web/`. Instala Node.js 24, ejecuta `npm ci` y compila con `npm run build`.
3. **`deploy`.** Solicita el despliegue a Render mediante el hook cuando hay un push a `main` del backend y los trabajos `api` y `web` terminan correctamente.

El trabajo de despliegue depende de ambos trabajos:

```yaml
deploy:
  needs: [api, web]
  if: github.event_name == 'push' && github.ref == 'refs/heads/main'
```

Las pruebas del backend usan SQLite en memoria mediante `phpunit.xml`. Así se ejecutan aisladas de MySQL en Aiven.

### Secretos de GitHub Actions

El workflow utiliza estos secretos del repositorio del backend:

| Secreto | Uso |
|---|---|
| `FRONTEND_READ_TOKEN` | Permite descargar el repositorio del frontend. |
| `RENDER_DEPLOY_HOOK` | Contiene el enlace que solicita el despliegue en Render. |

El token de lectura se limita al repositorio del frontend y al permiso **Contents → Read**. Los secretos se administran desde **Settings → Secrets and variables → Actions**.

### CI del frontend

El frontend tiene su propio workflow en `.github/workflows/ci.yaml`. Se ejecuta con los pushes y pull requests dirigidos a `main`, instala Node.js 24 y ejecuta:

```powershell
npm ci
npm run build
```

Cloudflare Pages gestiona la publicación web. La comprobación conjunta de la API y del frontend ocurre en el workflow del backend.

### Evidencia de las ejecuciones

| Evidencia | Resultado |
|---|---|
| CI del frontend después de combinar los cambios | Compilación aprobada en `main`. |
| CI del pull request #67 del backend | `api` y `web` aprobados. `deploy` omitido por tratarse de un pull request. |
| CI después de integrar el pull request #67 | Ejecución exitosa en `main` para el commit `787a82a`. |
| Evento de Render | Despliegue iniciado por el hook y finalizado con **Deploy live**. |
| Ejecución de la rama `ci-fallo` | Falló `php artisan test`, el trabajo `web` aprobó y `deploy` quedó omitido. |

La ejecución de `ci-fallo` muestra el fallo de una prueba y la omisión del despliegue. Esa ejecución se realizó fuera de `main`, por lo que también estaba sujeta a la condición de rama del trabajo `deploy`.

Enlaces de evidencia:

- [CI del frontend](https://github.com/ismaeltorrentes-lang/Ferreteria_Front/actions/runs/44964155246).
- [CI del pull request #67](https://github.com/sronnyjose979-tech/FerreteriaPescadoVolador/actions/runs/44964206007).
- [CI del despliegue del commit 787a82a](https://github.com/sronnyjose979-tech/FerreteriaPescadoVolador/actions/runs/44964645864).
- [Ejecución con prueba fallida](https://github.com/sronnyjose979-tech/FerreteriaPescadoVolador/actions/runs/43548008717).

## 6. Preparar la aplicación Android

El proyecto móvil utiliza Ionic, Vue y Capacitor. Incluye el directorio `android/` y toma los archivos web desde `dist/`.

Para generar la aplicación con la API pública, se configura:

```dotenv
VITE_API_URL=https://ferreteriapescadovolador.onrender.com/api
```

Desde la raíz del proyecto móvil se ejecutan:

```powershell
npm run build
npx cap sync android
npx cap open android
```

En Android Studio se genera el APK firmado desde **Build → Generate Signed Bundle / APK → APK**, utilizando el almacén de claves del equipo.

Se generó el archivo `Pescado-volador.apk`, con un tamaño de **27 112 743 bytes**, aproximadamente **25,86 MiB**.

La verificación con `apksigner` de Android SDK confirmó una firma APK v2 válida. El resultado incluyó:

```text
Verifies
Verified using v2 scheme (APK Signature Scheme v2): true
Number of signers: 1
```

El paquete debe instalarse en un dispositivo o emulador para comprobar el inicio de sesión y las operaciones contra la API pública. Para su distribución, se adjunta a la entrega o se publica en GitHub Releases.

[Descargar APK firmado de Pescado Volador v1.0.0](https://github.com/sronnyjose979-tech/FerreteriaPescadoVolador/releases/tag/v1.0.0)

## 7. Resumen de artefactos y verificaciones

| Elemento | Resultado |
|---|---|
| Imagen Docker | Construcción multietapa con PHP 8.5 y Alpine |
| Usuario del contenedor | `www-data` |
| Tamaño de la imagen local | 58 226 592 bytes |
| Tamaño del frontend | 51 686 bytes |
| Tamaño del APK | 27 112 743 bytes |
| Salud local | `/up` respondió `200 OK` |
| Salud pública en Render | `/up` respondió `200 OK` por HTTPS |
| Ruta protegida sin token | `/api/products` respondió `401 Unauthorized` |
| CORS para la web | Encabezado con el origen exacto de Cloudflare Pages |
| Publicación en Render | **Deploy live** para el commit `787a82a` |
| Firma del APK | APK Signature Scheme v2 válida |

