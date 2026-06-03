# Despliegue de OFFICIUM API

Documentacion operativa para desplegar y actualizar la API Laravel de OFFICIUM en el servidor Debian.

## Datos del entorno

- Dominio API: `https://api.officium.es`
- Ruta en servidor: `/var/www/officium.es/api`
- Repositorio: `https://github.com/ProyectoFinalDAM2025/API.git`
- Framework: Laravel 12
- PHP en servidor verificado: PHP 8.3.13
- Servidor web: Apache 2.4 en Debian
- Usuario web: `www-data`
- Entorno Laravel: `production`

## Estructura importante

```text
/var/www/officium.es
├── api                      # API activa en produccion
├── api_old_YYYYMMDD_HHMM    # backup de una version anterior
├── api_backup_YYYYMMDD_HHMM # backup completo creado antes de sustituir
└── .env.api.backup          # copia de seguridad del .env operativo
```

El servidor web debe apuntar al directorio:

```text
/var/www/officium.es/api/public
```

## Archivos que no se suben a Git

No se deben subir credenciales, dependencias ni archivos generados por usuarios.

El repositorio ignora:

```gitignore
.env
/vendor
/node_modules
/public/storage
/storage/app/public/*
/storage/logs/*
/storage/framework/views/*
/bootstrap/cache/*.php
```

Los documentos, imagenes, videos o PDFs subidos por usuarios se guardan en:

```text
storage/app/public
```

Y se sirven mediante:

```text
public/storage -> storage/app/public
```

## Primer despliegue desde Git

Desde el servidor:

```bash
cd /var/www/officium.es
cp -a api api_backup_$(date +%Y%m%d_%H%M)
cp api/.env .env.api.backup
git clone https://github.com/ProyectoFinalDAM2025/API.git API
cd API
cp ../.env.api.backup .env
composer install --no-dev --optimize-autoloader
php artisan --version
php artisan route:list
```

Si todo funciona, sustituir la API activa:

```bash
cd /var/www/officium.es
mv api api_old_$(date +%Y%m%d_%H%M)
mv API api
cd api
php artisan storage:link
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data /var/www/officium.es/api
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
systemctl reload apache2
```

## Actualizar la API desde Git

Antes de actualizar, se puede crear backup:

```bash
cd /var/www/officium.es
cp -a api api_backup_$(date +%Y%m%d_%H%M)
```

Actualizar codigo:

```bash
cd /var/www/officium.es/api
git status
git pull origin main
```

Despues de traer cambios:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data /var/www/officium.es/api
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
systemctl reload apache2
```

Si hay cambios de Vite o assets frontend:

```bash
npm install
npm run build
```

## Configuracion recomendada de Git en servidor

Evitar avisos por cambios de permisos:

```bash
cd /var/www/officium.es/api
git config core.filemode false
git config pull.ff only
```

Si `git status` muestra cambios solo por permisos:

```bash
git diff --summary
```

Si aparecen cambios como `mode change 100644 => 100755`, restaurar:

```bash
git restore bootstrap/cache/.gitignore storage/app/.gitignore storage/app/private/.gitignore storage/app/public/.gitignore storage/framework/.gitignore storage/framework/cache/.gitignore storage/framework/cache/data/.gitignore storage/framework/sessions/.gitignore storage/framework/testing/.gitignore storage/framework/views/.gitignore storage/logs/.gitignore
```

## Verificacion despues del despliegue

Comprobar Laravel:

```bash
cd /var/www/officium.es/api
php artisan about
```

Valores esperados:

- `Environment`: `production`
- `Debug Mode`: `OFF`
- `Config`: `CACHED`
- `Routes`: `CACHED`
- `Views`: `CACHED`
- `public/storage`: `LINKED`

Comprobar dominio:

```bash
curl -I https://api.officium.es
```

Comprobar login con JSON:

```bash
curl -X POST https://api.officium.es/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@test.com","password":"test"}'
```

Una respuesta de credenciales invalidas confirma que Laravel, rutas y base de datos responden. Un `500` requiere revisar logs.

## Logs utiles

Ultimas peticiones Apache:

```bash
tail -n 50 /var/log/apache2/access.log
```

Peticiones en tiempo real:

```bash
tail -f /var/log/apache2/access.log
```

Solo endpoints usados:

```bash
awk '{print $7}' /var/log/apache2/access.log | tail -n 50
```

Metodo y endpoint:

```bash
awk '{print $6, $7}' /var/log/apache2/access.log | tail -n 50
```

Errores Laravel:

```bash
tail -f /var/www/officium.es/api/storage/logs/laravel.log
```

Errores Apache:

```bash
tail -f /var/log/apache2/error.log
```

## Correo SMTP con Gmail

Si al registrar usuario aparece:

```text
El usuario se creo, pero no pude enviar el correo de verificacion
```

Revisar el log:

```bash
tail -n 120 /var/www/officium.es/api/storage/logs/laravel.log
```

Error tipico:

```text
535-5.7.8 Username and Password not accepted
```

Solucion: usar una contrasena de aplicacion de Gmail, no la contrasena normal de la cuenta.

Configuracion esperada en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=officium.portarentur@gmail.com
MAIL_PASSWORD=contrasena_de_aplicacion_sin_espacios
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=officium.portarentur@gmail.com
MAIL_FROM_NAME="OFFICIUM"
```

Despues de cambiar `.env`:

```bash
php artisan config:clear
php artisan config:cache
```

## Sanctum y pruebas de API

Las rutas protegidas con Sanctum deben recibir el token:

```http
Authorization: Bearer TOKEN
Accept: application/json
Content-Type: application/json
```

Ejemplo:

```bash
curl -X GET https://api.officium.es/api/usuario \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

Para probar correctamente, usar la app, Postman o `curl` con el metodo HTTP real. Por ejemplo, `/api/login` debe probarse con `POST`, no con `curl -I`.

## Notas sobre FFmpeg

En logs puede aparecer:

```text
FFmpeg no esta disponible para generar miniaturas
```

Esto no impide que la API funcione, pero afecta a la generacion de miniaturas o previews de imagen/video. Si se necesita esa funcionalidad en produccion, instalar FFmpeg en Debian y volver a probar subida de archivos.

## Rollback rapido

Si el despliegue falla y existe una carpeta `api_old_YYYYMMDD_HHMM`:

```bash
cd /var/www/officium.es
mv api api_failed_$(date +%Y%m%d_%H%M)
mv api_old_YYYYMMDD_HHMM api
chown -R www-data:www-data /var/www/officium.es/api
systemctl reload apache2
```

Sustituir `api_old_YYYYMMDD_HHMM` por el nombre real del backup.
