# GreenCycle

Proyecto base para el curso **TM4100**, preparado para desarrollar aplicaciones web con Laravel en un entorno reproducible.
GreenCycle es un proyecto de curso que como objetivo tiene crear un sistema web en el que los usuarios podrán crear un ecosistema virtual donde el principal objetivo es hacer crecer un árbol, el usuario podrá cuidar una variedad de arboles que crecerán dependiente del tiempo que transcurra, como se cuiden y de la atención que se le administre ademas de como usemos los objetos con estos arboles, si los arboles no se cuidan estos pueden morir pero si al contrario un árbol consigue el nivel máximo este empezara a dar frutos, los cuales son monedas para utilizar en la tienda.

Este documento resume el avance alcanzado en el Sprint 1, cubriendo los siguientes componentes: el sistema de autenticación de usuarios, el diseño del modelo de datos, la API inicial para la gestión de árboles y la interfaz base del proyecto, en este sprint se alcanzaron los siguientes objetivos específicos:
#Proposito
Habilitar el registro y el inicio de sesión de usuarios.
Diseñar el modelo de datos base (usuarios y árboles) con una estructura flexible que permita, más adelante, incorporar catálogo, inventario y efectos sin necesidad de rediseñar la base de datos.
Desarrollar una API REST protegida por autenticación, que restrinja las acciones únicamente a los recursos pertenecientes al usuario autenticado.
Crear una interfaz base capaz de consumir dicha API mediante peticiones Fetch.
Seguir el patrón de arquitectura MVC apoyándose en Laravel, haciendo uso de migraciones, seeders y el ORM Eloquent.

## Tecnologías

El proyecto utiliza las siguientes tecnologías:

Frontend: HTML5 semántico, CSS3 (Flexbox/Grid), JavaScript (ES6+), Fetch API.

Backend: PHP 8.x, Laravel 13, Laravel Sanctum, Blade, Eloquent ORM.

Infraestructura y herramientas: Vite, PostgreSQL (Neon), PHPUnit, Laravel Pint, Git, GitHub, GitHub Actions, Render.

## Requisitos locales

Antes de instalar el proyecto, asegúrese de contar con:

- Windows 10 o una versión posterior.
- Laravel Herd.
- PHP 8.5.
- Composer 2.
- Node.js 26.
- npm.
- Git.
- Visual Studio Code.
- Una cuenta de Neon.
- Una cuenta de Render.

> No es necesario instalar Docker ni PostgreSQL localmente.

## Instalación local

### 1. Clonar el repositorio

Abra PowerShell y ejecute:

```powershell
git clone https://github.com/ivanoviedomarinNav/Proyecto-GreenCycle.git
Set-Location Proyecto-GreenCycle
```

### 2. Aplicar la configuración de Laravel Herd

```powershell
herd init
herd isolate 8.5
```

### 3. Seleccionar la versión de Node.js

```powershell
nvm use
```

> Este comando utiliza la versión de Node.js definida para el proyecto.

### 4. Instalar las dependencias

Instale las dependencias de PHP:

```powershell
composer install
```

Instale las dependencias de frontend:

```powershell
npm ci
```

### 5. Crear la configuración local

Copie el archivo de configuración de ejemplo:

```powershell
Copy-Item .env.example .env
```

Genere la clave de la aplicación:

```powershell
php artisan key:generate
```

### 6. Configurar la base de datos

Abra el archivo `.env` y agregue la conexión correspondiente a la rama `development` de Neon:

```dotenv
DB_CONNECTION=pgsql
DB_URL="URL_DE_NEON_DEVELOPMENT"
DB_SSLMODE=require
```

> Sustituya `URL_DE_NEON_DEVELOPMENT` por la cadena de conexión proporcionada por Neon.

### 7. Limpiar la configuración almacenada

```powershell
php artisan optimize:clear
```

### 8. Ejecutar las migraciones y seeders

```powershell
php artisan migrate --seed
```

### 9. Iniciar Vite

```powershell
npm run dev
```

Mantenga esta terminal abierta mientras desarrolla la aplicación.

### 10. Abrir la aplicación

Visite la siguiente dirección en el navegador:

```text
http://proyecto-greencycle.test
```

## Comprobaciones del proyecto

Antes de crear un commit o un pull request, ejecute las siguientes comprobaciones.

### Ejecutar las pruebas automatizadas

```powershell
php artisan test
```

### Comprobar el formato del código PHP

```powershell
.\vendor\bin\pint --test
```

Para corregir automáticamente el formato:

```powershell
.\vendor\bin\pint
```

### Crear los recursos de producción

```powershell
npm run build
```

## Ambientes

| Ambiente | Aplicación | Base de datos |
|---|---|---|
| Desarrollo | Laravel Herd | Neon `development` |
| Pruebas | PHPUnit | SQLite en memoria |
| Producción | Render | Neon `production` |

## Seguridad

Nunca deben publicarse o incluirse en el repositorio:

- El archivo `.env`.
- Las credenciales de Neon.
- El valor de `APP_KEY`.
- Tokens de GitHub.
- Contraseñas.
- Claves de API.
- Archivos que contengan información privada.
- Cadenas de conexión de bases de datos.

Antes de realizar un commit, revise los archivos modificados:

```powershell
git status
```

## Flujo de trabajo

El flujo recomendado para desarrollar una funcionalidad es el siguiente:

1. Actualizar la rama `main`.
2. Crear una nueva rama de trabajo.
3. Desarrollar la funcionalidad.
4. Ejecutar las pruebas localmente.
5. Comprobar el formato del código.
6. Crear el commit.
7. Publicar la rama en GitHub.
8. Crear un pull request.
9. Esperar las comprobaciones automáticas.
10. Revisar el código.
11. Fusionar el pull request mediante **squash**.
12. Actualizar el repositorio local.

### Actualizar la rama principal

```powershell
git switch main
git pull origin main
```

### Crear una rama de trabajo

```powershell
git switch -c feature/nombre-de-la-funcionalidad
```

### Guardar los cambios

```powershell
git add .
git commit -m "feat: agregar nombre de la funcionalidad"
```

### Publicar la rama

```powershell
git push -u origin feature/nombre-de-la-funcionalidad
```

### Actualizar el repositorio después de fusionar

```powershell
git switch main
git pull origin main
```

## Configuración pendiente

Antes de ejecutar el proyecto, sustituya:

```text
URL_DE_NEON_DEVELOPMENT
```

por la cadena de conexión de la base de datos de desarrollo en Neon.
## Funcionalidades del Sprint
Alcance actual: hasta el momento, la aplicación cubre las funciones esenciales de registro e inicio de sesión, siembra de árboles, y consulta tanto del listado como del detalle individual de los árboles pertenecientes a cada usuario. Se dejó lista la estructura inicial del modelo de datos, junto con sus relaciones y los seeders correspondientes. Aspectos como las reglas basadas en tiempo (cuidados, cooldown, deterioro), así como la economía del juego y el sistema de inventario, quedan planificados para sprints posteriores.

Lo desarrollado hasta ahora incluye:

Registro de nuevos usuarios.
Manejo de sesión (inicio y cierre) a través de Laravel Sanctum.
Endpoints y rutas privadas con protección de acceso.
Estructura inicial del modelo de datos (usuarios, árboles y tipos de semilla).
Migraciones y seeders que permiten reproducir el entorno de datos.
Control de autorización basado en propiedad, de modo que cada usuario únicamente puede ver o modificar sus propios árboles.
Endpoint para la creación de árboles (POST /api/trees).
Endpoint para obtener el listado de árboles del usuario.
Endpoint para consultar el detalle de un árbol específico.
Panel inicial (dashboard) que permite registrarse, iniciar sesión, plantar árboles y visualizarlos.
Conexión entre frontend y backend mediante Fetch API, contemplando los distintos estados de la interfaz (cargando, éxito, error y vacío) sin necesidad de recargar la página por completo.

## Usuario de Prueba

Se crea automáticamente con `php artisan migrate --seed` (seeder `DemoUserSeeder`, repetible sin duplicar datos).

| Nombre | Correo | Contraseña |
|---|---|---|
| UsuarioPr | UsuarioPrueba@gmail.com | Prueba123 |

> El correo distingue mayúsculas y minúsculas: escríbalo tal como aparece.
## Equipo y Detalles
- Yoel Gonzales Gonzales - Ivan Oviedo Marin

## Estado del Proyecto
Sprint numero 1

## Versión evaluada del Sprint 1

La entrega del Sprint 1 corresponde al tag **`sprint-1-final`**. Para abrir exactamente esa versión:

```powershell
git fetch --tags
git checkout sprint-1-final
```

## Endpoints del Sprint 1

| Método | Ruta | Auth | Descripción | Códigos |
|---|---|---|---|---|
| POST | `/api/register` | No | Crea la cuenta y devuelve token | 201, 422 |
| POST | `/api/login` | No | Inicia sesión y devuelve token | 200, 422 |
| POST | `/api/logout` | Sí | Revoca el token actual | 200, 401 |
| GET | `/api/me` | Sí | Usuario autenticado | 200, 401 |
| GET | `/api/trees` | Sí | Lista los árboles propios | 200, 401 |
| GET | `/api/trees/{tree}` | Sí | Detalle de un árbol propio | 200, 401, 403, 404 |
| POST | `/api/trees` | Sí | Planta un árbol (solo recibe `seed_type_id`) | 201, 401, 422 |

Las respuestas de árboles pasan por `TreeResource`, que define en un único lugar los atributos expuestos. Nivel, salud, progreso, estado y fechas los fija siempre el servidor.

## Estados de la interfaz y flujo Fetch

El dashboard (ruta `/`, vista `resources/views/home.blade.php`) consume el API con Fetch, sin recargar la página.

| Acción | Antes | Durante | Éxito | Error |
|---|---|---|---|---|
| `GET /api/trees` | — | "Cargando..." | Lista de árboles | Vacío: "Aún no tienes árboles." · Error: mensaje · 401: vuelve al login |
| `GET /api/trees/{tree}` | "Selecciona un árbol para ver su detalle." | "Cargando detalle..." | Tipo, nivel, salud, progreso y estado | 403: "No tienes permiso para ver este árbol." · 404: "El árbol no existe." · otro: "No se pudo cargar el detalle." |
| `POST /api/trees` | Formulario habilitado | Botón deshabilitado ("Plantando...") | "Árbol plantado." y lista actualizada | Mensaje bajo el formulario (422/401) |
| `POST /api/login` | Formulario | Botón deshabilitado ("Entrando...") | Pasa a la vista de árboles | Mensaje de error |
| `POST /api/register` | Formulario | Botón deshabilitado ("Creando...") | Pasa a la vista de árboles | Mensaje de error |
| `POST /api/logout` | Sesión activa | — | Vuelve al login | Limpia el token local igualmente |

## Integridad de datos

Además de las validaciones del API, la base de datos (PostgreSQL) protege las reglas críticas con restricciones (migración `harden_sprint1_schema`):

| Tabla | Restricción |
|---|---|
| `inventories` | `UNIQUE(user_id, item_id)` y `CHECK (quantity >= 0)` |
| `users` | `CHECK (coins >= 0)` |
| `items` | `CHECK (cost >= 0)` |
| `trees` | `CHECK (salud BETWEEN 0 AND 100)`, `CHECK (nivel BETWEEN 0 AND 10)`, `CHECK (progreso >= 0)` y `CHECK (estado IN ('ACTIVE','MATURE','DEAD','HARVESTED'))` |
| `active_effects` | Índice `(tree_id, item_id, expires_at)` para consultar efectos vigentes |

> Los `CHECK` y los cambios de llaves foráneas solo se aplican en PostgreSQL; las pruebas (SQLite en memoria) se saltan esa parte.

### Estrategia para impedir efectos activos duplicados (Sprint 3)

Al activar un efecto (por ejemplo, el acelerador) se ejecutará dentro de `DB::transaction`:

1. Bloquear la fila del árbol con `lockForUpdate()`.
2. Comprobar que no exista un efecto del mismo ítem para ese árbol con `expires_at > now()`.
3. Insertar el efecto solo si no existe.

Así, dos solicitudes repetidas o simultáneas no crean dos efectos equivalentes ni consumen dos unidades del inventario.

## Política de borrado de cuentas e historial

Actualmente no existe eliminación de usuarios. Para conservar el historial de árboles `DEAD` y `HARVESTED` y de compras, las llaves foráneas `trees.user_id` y `purchases.user_id` usan `restrictOnDelete`: un usuario con historial no puede borrarse. Si en el futuro se agrega la eliminación de cuentas, se usará anonimización o *soft delete* en lugar de borrado en cascada.

## Pruebas automatizadas

```powershell
php artisan test
```

Cubren autenticación (registro, login, logout, `me`), autorización por propiedad, creación de árboles con valores definidos por el servidor, validaciones, página de inicio y seeders repetibles.

