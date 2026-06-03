# OFFICIUM API

Memoria definitiva del backend REST de OFFICIUM para usar como soporte de la memoria general del proyecto.

La API esta desarrollada con Laravel y funciona como backend comun para la aplicacion Android y la aplicacion WPF de administracion. Expone autenticacion, registro, perfiles de usuario, publicaciones, documentos multimedia, ofertas de empleo, candidaturas, suscripciones, notificaciones, reportes y moderacion administrativa.

Actualmente la API hace mas de lo que usa WPF y cubre la mayor parte de lo que consume Android. WPF se centra en administradores y reportes; Android consume autenticacion, perfiles, feed social, documentos, ofertas, candidaturas, suscripciones, busqueda, notificaciones y reportes.

## Indice

- [1. Resumen del proyecto](#1-resumen-del-proyecto)
- [2. Estado actual](#2-estado-actual)
- [3. Tecnologias](#3-tecnologias)
- [4. Estructura principal](#4-estructura-principal)
- [5. Arquitectura general](#5-arquitectura-general)
- [6. Autenticacion, roles y permisos](#6-autenticacion-roles-y-permisos)
- [7. Respuestas de la API](#7-respuestas-de-la-api)
- [8. Modulos funcionales](#8-modulos-funcionales)
- [9. Endpoints principales](#9-endpoints-principales)
- [10. Archivos, storage y multimedia](#10-archivos-storage-y-multimedia)
- [11. Notificaciones y eventos](#11-notificaciones-y-eventos)
- [12. Base de datos](#12-base-de-datos)
- [13. Configuracion local](#13-configuracion-local)
- [14. Como ejecutar el proyecto](#14-como-ejecutar-el-proyecto)
- [15. Relacion con Android y WPF](#15-relacion-con-android-y-wpf)
- [16. Observaciones finales](#16-observaciones-finales)

## 1. Resumen del proyecto

El backend se encuentra en:

```text
API-OFFICIUM/API-OFFICIUM/
```

Es una API REST local para OFFICIUM, una plataforma orientada a:

- Usuarios desempleados que crean perfil profesional, publican contenido, suben documentos, buscan ofertas, aplican a empleos y se suscriben a categorias.
- Empresas que crean perfil, publican ofertas de empleo y gestionan candidaturas recibidas.
- Administradores que gestionan otros administradores y moderan reportes sobre publicaciones, ofertas y perfiles.

La URL base usada por los clientes durante desarrollo es:

```text
http://127.0.0.1:8000/api
```

En Android, por ejecutarse en emulador, la misma API se consume como:

```text
http://10.0.2.2:8000/api/
```

## 2. Estado actual

La API incluye actualmente:

- Registro de usuarios con envio de codigo de verificacion por email.
- Login con token Bearer mediante Laravel Sanctum.
- Logout y eliminacion de tokens activos.
- Recuperacion de contrasena por email.
- Cambio de contrasena autenticado.
- Pre-registro de administradores desde un administrador existente.
- Verificacion de codigo de email.
- Consulta del usuario autenticado.
- Consulta de perfil publico por id de usuario.
- Busqueda de perfiles publicos de empresa y desempleado.
- Creacion y actualizacion de perfiles de desempleado.
- Creacion y actualizacion de perfiles de empresa.
- Creacion, listado, actualizacion y eliminacion de administradores.
- Subida, consulta, actualizacion y eliminacion de documentos.
- Separacion de documentos por fotos, videos y PDFs.
- Subida de documentos por administradores en nombre de otros usuarios.
- Publicaciones con texto, archivo opcional, thumbnail y preview.
- Feed paginado de publicaciones.
- Publicaciones propias y publicaciones de otros usuarios.
- Publicaciones de usuarios con perfil de empresa, desempleado o administrador.
- Likes, unlike y comprobacion de like.
- Comentarios sobre publicaciones.
- Ofertas de empleo con categoria, ubicacion y estado.
- Busqueda de ofertas por titulo, ubicacion, categoria y estado.
- Ofertas propias de una empresa.
- Candidaturas de desempleados a ofertas.
- Gestion del estado de candidaturas por parte de la empresa.
- Suscripciones de desempleados a categorias.
- Notificaciones de usuario con contador de no leidas.
- Reportes sobre publicaciones, ofertas y perfiles.
- Panel de reportes para administradores.
- Moderacion administrativa de publicaciones, ofertas y perfiles.
- Eliminacion administrativa de perfiles reportados.
- Notificaciones a usuarios y administradores tras acciones de moderacion.
- Catalogos de sectores, provincias y categorias.
- Grupos y publicaciones de grupos, aunque este modulo no aparece como flujo principal en los README de Android y WPF.

## 3. Tecnologias

| Tecnologia | Uso |
| --- | --- |
| PHP 8.2 | Lenguaje base requerido por `composer.json` |
| Laravel 12 | Framework principal del backend |
| Laravel Sanctum 4 | Autenticacion por tokens personales |
| MySQL | Base de datos configurada en `.env.example` |
| Eloquent ORM | Modelos y relaciones |
| Laravel Mail | Envio de verificacion, recuperacion y pre-registro |
| Laravel Events | Creacion de notificaciones por acciones de usuario |
| Storage public | Guardado de fotos, videos, PDFs, thumbnails y previews |
| FFmpeg | Generacion de miniaturas y previews de imagen/video |
| PHPUnit | Base de pruebas incluida por Laravel |

## 4. Estructura principal

```text
API-OFFICIUM/API-OFFICIUM/
|-- app/
|   |-- Events/
|   |-- Http/
|   |   |-- Controllers/API/
|   |   `-- Middleware/
|   |-- Mail/
|   |-- Models/
|   `-- Services/
|-- database/
|   |-- migrations/
|   `-- seeders/
|-- routes/
|   `-- api.php
|-- storage/
|-- composer.json
|-- artisan
`-- README.md
```

Archivos clave:

- `routes/api.php`: define todos los endpoints REST.
- `app/Http/Controllers/API`: contiene la logica de cada modulo.
- `app/Models`: contiene modelos Eloquent principales.
- `app/Events`: dispara eventos que generan notificaciones.
- `app/Services`: servicios de generacion de thumbnails y previews.
- `database/migrations`: define la estructura de tablas.

## 5. Arquitectura general

La API sigue una arquitectura Laravel clasica:

```text
Cliente Android / WPF
    ->
routes/api.php
    ->
Controller API
    ->
Modelos Eloquent / Storage / Mail / Events
    ->
Respuesta JSON
```

Los controladores principales son:

- `AuthController`: registro, login, verificacion, recuperacion, sesion, consulta y busqueda de usuarios.
- `EmpresaController`: perfil de empresa.
- `DesempleadoController`: perfil de desempleado.
- `AdministradorController`: gestion de administradores.
- `DocumentoController`: subida y gestion de fotos, videos y PDFs, incluyendo subida administrativa para otro usuario.
- `PublicacionController`: feed, publicaciones, archivos, likes y consultas por usuario.
- `ComentarioController`: comentarios.
- `OfertaEmpleoController`: ofertas, busqueda y ofertas propias.
- `AplicacionController`: candidaturas a ofertas.
- `CategoriaController`: categorias y categorias disponibles para suscripcion.
- `SuscripcionsController`: suscripciones del desempleado.
- `NotificacionController`: listado, lectura y eliminacion de notificaciones.
- `ReporteController`: reportes, moderacion administrativa, eliminacion de perfiles reportados y notificaciones asociadas.
- `GrupoController`: grupos, union, abandono y publicaciones de grupo.
- `SectorController` y `ProvinciaController`: catalogos.

## 6. Autenticacion, Roles y Permisos

La API usa Laravel Sanctum. Tras un login correcto se devuelve un token que los clientes deben enviar en:

```text
Authorization: Bearer <token>
Accept: application/json
```

Rutas publicas:

- `POST /api/register`
- `POST /api/login`
- `POST /api/recover`

El resto de rutas principales estan dentro de `auth:sanctum`.

Roles usados:

| Rol en base de datos | Rol mostrado en clientes | Uso |
| --- | --- | --- |
| `usuario` | `Desempleado` o `Usuario` | Perfil de desempleado, publicaciones, documentos, candidaturas y suscripciones |
| `empresa` | `Empresa` | Perfil de empresa, publicaciones, documentos y ofertas |
| `admin` | `Administrador` | Gestion administrativa y moderacion |

Permisos relevantes:

- Solo administradores pueden acceder al listado de reportes y acciones de moderacion.
- Solo administradores pueden listar, editar o eliminar administradores.
- Un administrador no puede eliminar el administrador de su propia sesion desde WPF/API.
- Empresas pueden crear ofertas y gestionar candidaturas de sus propias ofertas.
- Desempleados pueden aplicar a ofertas y gestionar sus suscripciones.
- Usuarios propietarios pueden editar o eliminar sus publicaciones y documentos.
- Administradores pueden subir documentos para otro usuario indicando `IDUsuario` cuando usan la cuenta administrativa especial.
- Existe un administrador especial reconocido por codigo para algunas acciones de gestion sobre contenido:

```text
officium.portarentur@gmail.com
```

## 7. Respuestas de la API

La mayoria de respuestas siguen una estructura parecida:

```json
{
  "StatusCode": 200,
  "ReasonPhrase": "OK",
  "Message": "Operacion realizada correctamente.",
  "Data": {}
}
```

En algunos endpoints antiguos la clave puede aparecer como `data` en minuscula. Los clientes Android y WPF ya contemplan parte de esta variacion.

Codigos usados habitualmente:

| Codigo | Significado |
| --- | --- |
| 200 | Operacion correcta |
| 201 | Recurso creado |
| 400 | Peticion incorrecta |
| 401 | No autenticado o credenciales incorrectas |
| 403 | Acceso no autorizado |
| 404 | Recurso no encontrado |
| 409 | Conflicto o duplicado |
| 422 | Error de validacion |
| 500 | Error interno |

## 8. Modulos Funcionales

### Autenticacion y sesion

Permite registrar usuarios, iniciar sesion, cerrar sesion, verificar email, recuperar contrasena y cambiar contrasena.

Flujo general:

1. El usuario se registra con email y contrasena.
2. La API genera un codigo de verificacion.
3. Se envia un correo de verificacion.
4. El usuario verifica el codigo.
5. El usuario crea un perfil de desempleado, empresa o administrador.
6. En login, la API devuelve token, perfil y rol.

Tambien permite buscar perfiles publicos con `GET /usuarios/buscar`. Esta busqueda acepta texto libre y filtro por rol, y devuelve resultados normalizados con id de usuario, rol, nombre, foto y descripcion breve.

### Perfiles

La API soporta tres tipos de perfil:

- Empresa: nombre de empresa, CIF, sector, ubicacion, sitio web y foto.
- Desempleado: nombre, apellido, DNI, portfolio, disponibilidad, ubicacion y foto.
- Administrador: nombre, apellido, foto de perfil y estado activo.

Los perfiles se actualizan mediante peticiones multipart cuando incluyen foto. En los clientes se usa `POST` con `_method=PUT` para algunos formularios multipart.

### Publicaciones

Las publicaciones pueden incluir:

- Texto obligatorio.
- Archivo opcional.
- Tipo de archivo: `Foto`, `Video` o `PDF`.
- Thumbnail.
- Preview para imagenes.
- Relacion con documentos.
- Likes.
- Comentarios.
- Autor con perfil de empresa, desempleado o administrador.

El feed principal devuelve publicaciones paginadas y excluye publicaciones de grupos.

En las respuestas de publicaciones se cargan tambien los perfiles de los autores y de los usuarios que comentan. Actualmente se incluyen relaciones de `empresa`, `desempleado` y `administrador`, por lo que el cliente puede mostrar correctamente publicaciones o comentarios creados por administradores.

### Documentos

Los usuarios pueden subir documentos independientes del perfil:

- Fotos.
- Videos.
- PDFs.

Tambien se crean documentos asociados a publicaciones cuando una publicacion incluye archivo.

La subida de documentos acepta `IDUsuario` de forma opcional. Si la peticion la realiza el administrador especial, la API guarda el documento para el usuario indicado; en caso contrario, lo guarda para el usuario autenticado. Esto permite operaciones administrativas sin romper la propiedad del documento.

### Ofertas de empleo

Las empresas pueden crear ofertas con:

- Categoria.
- Titulo.
- Descripcion.
- Ubicacion.
- Estado: `Abierta`, `Cerrada` o `En Proceso`.

La API permite listar todas las ofertas, buscar con filtros y obtener ofertas propias de la empresa autenticada.

### Candidaturas

Los desempleados pueden aplicar a una oferta una sola vez. Las empresas pueden ver candidaturas recibidas por oferta y actualizar su estado.

Estados soportados:

- `Abierta`
- `Pendiente`
- `Rechazada`

### Suscripciones

Los desempleados pueden suscribirse a categorias de empleo, consultar sus suscripciones y eliminar suscripciones. La API tambien devuelve categorias a las que el usuario todavia no esta suscrito.

### Notificaciones

La API guarda notificaciones para acciones como likes, comentarios, ofertas, candidaturas, reportes y moderaciones. El cliente puede listar notificaciones, marcar una como leida y eliminarla.

En reportes, la API notifica:

- A los administradores cuando se crea un reporte.
- Al usuario que reporto cuando su reporte se modera o se resuelve eliminando la entidad.
- Al propietario del contenido cuando su publicacion, oferta o perfil se modera.
- A los administradores cuando un reporte se modera o cuando se elimina un perfil reportado.

### Reportes y moderacion

Los usuarios autenticados pueden reportar:

- Publicaciones.
- Ofertas.
- Perfiles de usuario.

Los administradores pueden:

- Ver todos los reportes.
- Filtrar por tipo de entidad.
- Eliminar solo el reporte.
- Moderar la entidad reportada.
- Eliminar perfiles reportados.

Al moderar:

- Una publicacion queda con texto de moderacion, sin archivo, sin thumbnail, sin preview y sin documentos asociados.
- Una oferta queda con titulo y descripcion de moderacion y estado `Cerrada`.
- Un perfil queda con datos publicos sustituidos por textos de moderacion y una imagen por defecto.
- El reporte pasa a estado `Revisado`.
- Se generan notificaciones para el usuario que reporto, para el usuario reportado cuando aplica y para los administradores.

Al eliminar un perfil reportado:

- Solo se permite para reportes de tipo `Usuario`.
- Se eliminan las carpetas de storage del usuario.
- Se eliminan los reportes asociados a ese perfil.
- Se notifican los usuarios que habian reportado el perfil.
- Se notifican los administradores.

## 9. Endpoints Principales

Todas las rutas parten de:

```text
/api
```

### Autenticacion

| Metodo | Ruta | Protegido | Uso |
| --- | --- | --- | --- |
| POST | `/register` | No | Registrar usuario y enviar codigo |
| POST | `/login` | No | Iniciar sesion |
| POST | `/recover` | No | Generar nueva contrasena y enviarla por email |
| GET | `/user` | Si | Obtener usuario autenticado |
| GET | `/testAuth` | Si | Probar token |
| POST | `/logout` | Si | Cerrar sesion y borrar tokens |
| POST | `/verifyCode` | Si | Verificar codigo de email |
| POST | `/change-password` | Si | Cambiar contrasena |
| POST | `/pre-register` | Si, admin | Pre-registrar administrador |
| GET | `/rolUsuario` | Si | Obtener perfil y rol del usuario autenticado |
| GET | `/usuarios/buscar` | Si | Buscar perfiles publicos |
| GET | `/usuarios/{idUsuario}` | Si | Obtener perfil publico por id |
| GET | `/usuarios/grupos` | Si | Grupos del usuario autenticado |

Parametros de `GET /usuarios/buscar`:

```text
q         Texto a buscar en nombre, ubicacion, web, disponibilidad o portfolio
rol       Empresa o Desempleado
per_page  Limite de resultados, maximo 50
```

### Perfiles

| Metodo | Ruta | Uso |
| --- | --- | --- |
| POST | `/empresa` | Crear perfil de empresa |
| GET | `/empresa/{empresa}` | Ver perfil de empresa propio |
| PUT/PATCH | `/empresa/{empresa}` | Actualizar empresa |
| DELETE | `/empresa/{empresa}` | Eliminar empresa |
| POST | `/desempleado` | Crear perfil de desempleado |
| GET | `/desempleado/{desempleado}` | Ver perfil de desempleado propio |
| PUT/PATCH | `/desempleado/{desempleado}` | Actualizar desempleado |
| DELETE | `/desempleado/{desempleado}` | Eliminar desempleado |
| GET | `/administrador` | Listar administradores |
| POST | `/administrador` | Crear perfil administrador |
| GET | `/administrador/{administrador}` | Ver administrador |
| PUT/PATCH | `/administrador/{administrador}` | Actualizar administrador |
| DELETE | `/administrador/{administrador}` | Eliminar administrador |

### Publicaciones y comentarios

| Metodo | Ruta | Uso |
| --- | --- | --- |
| GET | `/publicacion` | Feed paginado |
| POST | `/publicacion` | Crear publicacion |
| GET | `/publicacion/{publicacion}` | Detalle de publicacion |
| PUT/PATCH | `/publicacion/{publicacion}` | Actualizar publicacion |
| DELETE | `/publicacion/{publicacion}` | Eliminar publicacion |
| GET | `/publicacion/{publicacion}/like` | Dar like |
| DELETE | `/publicacion/{publicacion}/unlike` | Quitar like |
| GET | `/publicacion/{publicacion}/liked` | Consultar si el usuario dio like |
| GET | `/publicaciones/postsByUsuario` | Publicaciones propias |
| GET | `/publicaciones/postsByUsuario/{userId}` | Publicaciones de otro usuario |
| POST | `/comentario` | Crear comentario |
| GET | `/comentario/{comentario}` | Ver comentario |
| PUT/PATCH | `/comentario/{comentario}` | Actualizar comentario |
| DELETE | `/comentario/{comentario}` | Eliminar comentario |

### Documentos

| Metodo | Ruta | Uso |
| --- | --- | --- |
| GET | `/documento/{documento}` | Ver documento |
| POST | `/documento` | Subir documento |
| PUT/PATCH | `/documento/{documento}` | Actualizar documento |
| DELETE | `/documento/{documento}` | Eliminar documento |
| GET | `/documentos/byIDUsuario` | Documentos propios |
| GET | `/documentos/byIDUsuario/{userId}` | Documentos de otro usuario |
| GET | `/documentos/fotosByIDUsuario` | Fotos propias |
| GET | `/documentos/fotosByIDUsuario/{userId}` | Fotos de otro usuario |
| GET | `/documentos/videosByIDUsuario` | Videos propios |
| GET | `/documentos/videosByIDUsuario/{userId}` | Videos de otro usuario |
| GET | `/documentos/pdfsByIDUsuario` | PDFs propios |
| GET | `/documentos/pdfsByIDUsuario/{userId}` | PDFs de otro usuario |

Campos principales de `POST /documento`:

```text
Archivo       Archivo obligatorio, maximo 20480 KB
Tipo          Foto, Video, PDF o Publicacion
Descripcion   Texto opcional
Thumbnail     Imagen opcional para PDFs
IDUsuario     Opcional; solo lo respeta el administrador especial
```

### Ofertas y candidaturas

| Metodo | Ruta | Uso |
| --- | --- | --- |
| GET | `/ofertaEmpleo` | Listar ofertas |
| POST | `/ofertaEmpleo` | Crear oferta |
| GET | `/ofertaEmpleo/{ofertaEmpleo}` | Detalle de oferta |
| PUT/PATCH | `/ofertaEmpleo/{ofertaEmpleo}` | Actualizar oferta |
| DELETE | `/ofertaEmpleo/{ofertaEmpleo}` | Eliminar oferta |
| GET | `/ofertaEmpleo/buscar` | Buscar ofertas con filtros |
| GET | `/ofertasEmpleos` | Ofertas propias de empresa |
| POST | `/aplicacion` | Aplicar a oferta |
| GET | `/aplicacion/{aplicacion}` | Ver aplicacion |
| PUT/PATCH | `/aplicacion/{aplicacion}` | Actualizar estado de aplicacion |
| DELETE | `/aplicacion/{aplicacion}` | Eliminar aplicacion |
| GET | `/misAplicaciones` | Aplicaciones del desempleado autenticado |
| GET | `/aplicacion/{oferta}/aplicaciones` | Aplicaciones recibidas en una oferta |

Filtros de busqueda de ofertas:

```text
titulo
ubicacion
categoria
estado
```

### Categorias, suscripciones y catalogos

| Metodo | Ruta | Uso |
| --- | --- | --- |
| GET | `/categoria` | Listar categorias |
| GET | `/categoriasUsuario` | Categorias disponibles para el desempleado |
| GET | `/misSuscripciones` | Suscripciones propias |
| POST | `/suscripcion/add` | Crear suscripcion |
| POST | `/suscripcion/eliminar` | Eliminar suscripcion |
| GET | `/sector` | Listar sectores |
| GET | `/provincia` | Listar provincias |

### Notificaciones

| Metodo | Ruta | Uso |
| --- | --- | --- |
| GET | `/notificacion` | Listar notificaciones del usuario |
| DELETE | `/notificacion/{notificacion}` | Eliminar notificacion |
| GET | `/notificaciones/{id}` | Marcar notificacion como leida |

### Reportes

| Metodo | Ruta | Uso |
| --- | --- | --- |
| POST | `/publicacion/reportar` | Reportar publicacion |
| POST | `/ofertaEmpleo/reportar` | Reportar oferta |
| POST | `/usuarios/reportar` | Reportar perfil |
| GET | `/reportes` | Listar reportes, solo administradores |
| DELETE | `/reportes/{reporte}` | Eliminar reporte sin tocar la entidad |
| POST | `/reportes/{reporte}/moderar` | Moderar entidad reportada |
| DELETE | `/reportes/{reporte}/entidad` | Eliminar perfil reportado |

Campos de reporte:

```text
Motivo
Descripcion
IDPublicacion / IDOferta / IDUsuarioReportado
```

### Grupos

| Metodo | Ruta | Uso |
| --- | --- | --- |
| GET | `/grupo` | Listar grupos |
| POST | `/grupo` | Crear grupo |
| GET | `/grupo/{grupo}` | Ver grupo |
| PUT/PATCH | `/grupo/{grupo}` | Actualizar grupo |
| DELETE | `/grupo/{grupo}` | Eliminar grupo |
| GET | `/grupos/{idGrupo}/unirse` | Solicitar unirse o unirse a grupo |
| GET | `/grupos/{idGrupo}/abandonar` | Abandonar grupo |
| GET | `/grupos/publicaciones/{grupo}` | Publicaciones de grupo |
| GET | `/grupos/byIDUsuario` | Grupos del usuario |
| GET | `/grupos/{idGrupo}/pendientes` | Solicitudes pendientes |
| POST | `/grupos/{idGrupo}/{solicitudUserId}/estado` | Gestionar solicitud |

## 10. Archivos, Storage y Multimedia

La API guarda archivos en el disco `public` de Laravel y devuelve rutas publicas del tipo:

```text
storage/...
/storage/...
```

Los clientes convierten estas rutas a URL completa usando:

```text
http://127.0.0.1:8000
http://10.0.2.2:8000
```

Rutas habituales de almacenamiento:

```text
Empresa/{IDUsuario}/FotoPerfil
Empresa/{IDUsuario}/Publicacion
Empresa/{IDUsuario}/{TipoDocumento}
Administrador/{IDUsuario}/Publicacion
Desempleado/{IDUsuario}/FotoPerfil
Desempleado/{IDUsuario}/Publicacion
Desempleado/{IDUsuario}/{TipoDocumento}
administradores
```

Tipos de archivo soportados por la API:

| Tipo | Uso |
| --- | --- |
| `Foto` | Imagenes de perfil, documentos o publicaciones |
| `Video` | Videos en documentos o publicaciones |
| `PDF` | PDFs en documentos o publicaciones |
| `Publicacion` | Documento asociado internamente a una publicacion |

La API genera thumbnails para fotos y videos mediante servicios internos. Para PDFs, el cliente puede enviar un thumbnail generado previamente.

Servicios de generacion multimedia:

- `ImageThumbnailService::generate`: crea miniatura cuadrada de imagen en `Thumbnails/*.webp`.
- `ImageThumbnailService::generatePreview`: crea preview de imagen en `Previews/*.webp`.
- `VideoThumbnailService::generate`: crea miniatura de video en `Thumbnails/*.jpg`.

Los servicios buscan FFmpeg en este orden:

```text
FFMPEG_PATH
ffmpeg
/usr/bin/ffmpeg
/usr/local/bin/ffmpeg
C:\ffmpeg\bin\ffmpeg.exe
```

Si FFmpeg no esta disponible o el archivo no existe, la API guarda el recurso principal pero deja la miniatura o preview como `null` y registra un aviso en logs.

## 11. Notificaciones y Eventos

La API contiene eventos para generar notificaciones:

- `PublicacionLiked`
- `PublicacionComentada`
- `OfertaCreada`
- `OfertaAplicada`
- `EstadoAplicacion`
- `ReporteCreado`
- `UsuarioSeUnioAGrupo`
- `UsuarioSeUnioAGrupoPrivado`
- `UsuarioAceptoSolicitud`

Las notificaciones incluyen:

- Usuario destino.
- Titulo.
- Mensaje.
- Estado leido/no leido.
- Fecha.
- Ruta de navegacion para el cliente.

Ejemplos de rutas guardadas en notificaciones:

```text
/post/{id}
/ofertaEmpleo/{id}
/usuarios/{id}
/reportes
```

## 12. Base de Datos

Tablas principales definidas por migraciones:

- `users`
- `personal_access_tokens`
- `sectores`
- `empresas`
- `desempleados`
- `administradors`
- `grupos`
- `publicacions`
- `comentarios`
- `categorias`
- `oferta_empleos`
- `aplicacions`
- `documentos`
- `notificacions`
- `likes`
- `suscripcion`
- `provincias`
- `reportes`

Tambien existen tablas/modelos para informacion profesional ampliada:

- experiencias.
- educacion.
- habilidades.
- idiomas.
- niveles de idioma.

Algunos controladores de esta parte existen en el codigo, pero no estan expuestos actualmente en `routes/api.php`.

## 13. Configuracion Local

Configuracion base observada en `.env.example`:

```text
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_officium
DB_USERNAME=root
DB_PASSWORD=
MAIL_MAILER=log
QUEUE_CONNECTION=database
FFMPEG_PATH=
```

Para usar envio real de correos hay que configurar `MAIL_MAILER`, host, puerto, usuario, contrasena y remitente.

`FFMPEG_PATH` es opcional. Si se define, los servicios de thumbnails/previews lo usan antes de buscar `ffmpeg` en las rutas habituales del sistema.

Para servir archivos subidos, Laravel necesita el enlace de storage:

```powershell
php artisan storage:link
```

## 14. Como Ejecutar El Proyecto

Requisitos:

- PHP 8.2 o superior.
- Composer.
- MySQL/MariaDB.
- Extensiones PHP habituales para Laravel.

Pasos recomendados:

```powershell
cd API-OFFICIUM/API-OFFICIUM
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

La API quedara disponible en:

```text
http://127.0.0.1:8000/api
```

Si Android usa emulador:

```text
http://10.0.2.2:8000/api/
```

## 15. Relacion Con Android y WPF

### Android

Android consume la API como cliente movil principal. Usa:

- Login, registro, recuperacion y verificacion.
- Creacion y edicion de perfiles.
- Perfil propio y perfiles publicos.
- Busqueda de perfiles publicos.
- Feed de publicaciones.
- Likes y comentarios.
- Subida de publicaciones y documentos.
- Fotos, videos y PDFs.
- Ofertas, busqueda y candidaturas.
- Suscripciones.
- Notificaciones.
- Reportes de publicaciones, ofertas y perfiles.
- Actualizacion de perfil administrador.

### WPF

WPF funciona como panel administrativo. Usa principalmente:

- Login y validacion de rol administrador.
- Gestion de administradores.
- Pre-registro de administradores.
- Verificacion y creacion de perfil administrador.
- Recuperacion y cambio de contrasena.
- Listado de reportes.
- Detalle de reportes de publicaciones, ofertas y perfiles.
- Eliminacion de reportes.
- Moderacion de contenido reportado.
- Eliminacion administrativa de perfiles reportados.

## 16. Observaciones Finales

Aspectos tecnicos que conviene tener presentes en la memoria general:

- Unificar la clave de respuesta `Data`/`data` para simplificar clientes.
- Corregir textos con caracteres mal codificados en algunas respuestas.
- Extraer la URL base y reglas de administrador especial a configuracion.
- Revisar el uso de `GET` para acciones que modifican estado, como like, marcar notificacion como leida o unirse a grupos.
- Crear pruebas para autenticacion, permisos, reportes, moderacion, publicaciones, documentos, ofertas, candidaturas y busqueda de usuarios.
- Revisar permisos de administrador para que no dependan de un email escrito en codigo.
- Revisar controladores existentes no registrados en rutas, como educacion, experiencia, habilidades e idiomas.
- Normalizar nombres de tablas/modelos y rutas en singular/plural.
- Revisar validaciones de archivos por MIME y tamano segun necesidades reales.
- Completar seeders para catalogos de sectores, provincias y categorias.
- Revisar respuestas de error que exponen SQL o detalles internos en entorno no local.
- Revisar eliminacion de archivos antiguos para cubrir todas las rutas `storage/...` y `/storage/...`.
