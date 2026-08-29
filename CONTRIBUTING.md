# Guía de contribución

Este documento describe el flujo de trabajo recomendado para contribuir al proyecto.

## Rama principal

La rama `main` representa la versión estable y desplegable de la aplicación.

No se debe desarrollar ni realizar commits directamente en `main`. Todo cambio debe realizarse en una rama independiente y posteriormente integrarse mediante un pull request.

## Crear una rama de trabajo

### 1. Actualizar la rama principal

Antes de crear una nueva rama, asegúrese de tener la versión más reciente de `main`:

```bash
git switch main
git pull origin main
```

### 2. Crear una nueva rama

Cree una rama utilizando un nombre breve y descriptivo:

```bash
git switch -c feature/nombre-de-la-funcionalidad
```

Por ejemplo:

```bash
git switch -c feature/mantenimiento-productos
```

## Convención para nombres de ramas

Utilice uno de los siguientes prefijos:

| Prefijo | Propósito |
|---|---|
| `feature/` | Incorporar una nueva funcionalidad. |
| `fix/` | Corregir un error. |
| `docs/` | Crear o actualizar documentación. |
| `test/` | Agregar o modificar pruebas. |
| `refactor/` | Reorganizar código sin cambiar su comportamiento. |
| `chore/` | Realizar tareas de mantenimiento o configuración. |

Ejemplos:

```text
feature/registro-usuarios
fix/validacion-correo
docs/actualizar-instalacion
test/crear-productos
refactor/product-controller
chore/configurar-vite
```

Utilice letras minúsculas y separe las palabras con guiones.

## Antes de crear un commit

Antes de guardar los cambios, ejecute las comprobaciones del proyecto.

### Ejecutar las pruebas automatizadas

```bash
php artisan test
```

### Comprobar el formato del código PHP

En PowerShell:

```powershell
.\vendor\bin\pint --test
```

En Bash, Git Bash o una terminal compatible con Unix:

```bash
./vendor/bin/pint --test
```

Para corregir automáticamente el formato:

```powershell
.\vendor\bin\pint
```

### Crear los recursos frontend

```bash
npm run build
```

Todas las comprobaciones deben finalizar correctamente antes de crear el commit.

## Mensajes de commit

Utilice mensajes breves, claros y escritos en presente.

Formato recomendado:

```text
tipo: descripción breve
```

Tipos recomendados:

| Tipo | Propósito |
|---|---|
| `feat` | Agregar una nueva funcionalidad. |
| `fix` | Corregir un error. |
| `docs` | Modificar documentación. |
| `test` | Agregar o actualizar pruebas. |
| `refactor` | Reorganizar código sin cambiar su comportamiento. |
| `chore` | Realizar mantenimiento o configuración. |

Ejemplos:

```text
feat: agrega mantenimiento de productos
fix: corrige validación de correo
docs: actualiza pasos de instalación
test: agrega pruebas para crear usuarios
refactor: separa validación en form request
chore: actualiza configuración de vite
```

Evite mensajes poco descriptivos como:

```text
cambios
arreglos
actualización
commit final
```

## Guardar los cambios

Revise primero el estado del repositorio:

```bash
git status
```

Agregue los archivos modificados:

```bash
git add .
```

Cree el commit:

```bash
git commit -m "feat: agrega mantenimiento de productos"
```

## Publicar una rama

Envíe la rama al repositorio remoto:

```bash
git push -u origin nombre-de-la-rama
```

Por ejemplo:

```bash
git push -u origin feature/mantenimiento-productos
```

Después del primer envío, los cambios posteriores pueden publicarse con:

```bash
git push
```

## Pull requests

Todo cambio debe integrarse a `main` mediante un pull request.

Cada pull request debe:

- Resolver una sola necesidad principal.
- Utilizar un título claro y descriptivo.
- Explicar qué cambió.
- Indicar por qué se realizó el cambio.
- Describir cómo se probó.
- Incluir migraciones cuando correspondan.
- Actualizar la documentación cuando sea necesario.
- Pasar todas las comprobaciones automáticas.
- No contener credenciales ni información privada.

## Descripción recomendada para el pull request

Puede utilizar la siguiente estructura:

```markdown
## Descripción

Explique brevemente qué problema resuelve o qué funcionalidad incorpora.

## Cambios realizados

- Cambio principal.
- Cambio secundario.
- Actualización de documentación o configuración.

## Cómo se probó

Describa las pruebas realizadas para comprobar el funcionamiento.

## Lista de comprobación

- [ ] Ejecuté `php artisan test`.
- [ ] Ejecuté Laravel Pint.
- [ ] Ejecuté `npm run build`.
- [ ] Agregué o actualicé las pruebas necesarias.
- [ ] Actualicé la documentación cuando fue necesario.
- [ ] Verifiqué que no se incluyeran credenciales.
```

## Revisión del código

Antes de fusionar el pull request:

1. Verifique que las comprobaciones automáticas hayan finalizado correctamente.
2. Revise los comentarios realizados por otras personas.
3. Aplique las correcciones solicitadas.
4. Confirme que la rama esté actualizada.
5. Fusione el pull request mediante **squash**.

## Después de fusionar

Actualice el repositorio local:

```bash
git switch main
git pull origin main
```

Elimine la rama local si ya no es necesaria:

```bash
git branch -d nombre-de-la-rama
```

También puede eliminar la rama remota:

```bash
git push origin --delete nombre-de-la-rama
```

## Seguridad

Nunca incluya en un commit o pull request:

- El archivo `.env`.
- Credenciales de bases de datos.
- El valor de `APP_KEY`.
- Tokens de GitHub.
- Contraseñas.
- Claves de API.
- Cadenas de conexión privadas.
- Archivos que contengan información sensible.

Antes de publicar una rama, revise siempre los archivos incluidos:

```bash
git status
git diff --staged
```
````
