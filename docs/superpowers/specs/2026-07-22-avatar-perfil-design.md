# Foto de Perfil de Usuario — Design Doc

## Resumen
Permitir que todos los usuarios (sin importar rol) suban una foto de perfil desde la sección Mi Perfil. Si no tienen foto, se muestran las iniciales como actualmente.

## Cambios

### Base de datos
- Nueva migración `007_add_avatar_to_users.sql`:
  ```sql
  ALTER TABLE users ADD COLUMN avatar VARCHAR(500) NULL DEFAULT NULL AFTER localidad;
  ```

### Controladores
- `controllers/ProfileController.php` — método `update()`:
  - Detectar `$request->file('avatar')`
  - Si viene archivo: usar `FileUploadService::uploadImage()` → `uploads/images/avatars/`
  - Si el usuario ya tenía avatar, eliminar el anterior con `FileUploadService::delete()`
  - Guardar ruta relativa en `users.avatar`
  - Si se envía `_remove_avatar` (checkbox oculto), borrar avatar y setear `avatar = NULL`

### Vistas

1. **`views/profile/index.php`** — Nueva sección de Avatar entre el título y "Información Personal":
   - Muestra avatar actual (o círculo con iniciales)
   - Input file para nueva foto
   - Botón "Eliminar foto" si ya tiene avatar
   - Preview con Alpine.js al seleccionar archivo
   - Form cambia a `enctype="multipart/form-data"`

2. **`views/layouts/main.php:177`** — Navbar:
   ```php
   if ($currentUser->avatar): <img src="<?= APP_URL ?>/<?= $currentUser->avatar ?>">
   else: <div>iniciales</div>
   ```

3. **`views/users/index.php:58`** — Listado de usuarios (misma lógica que navbar)

### No se modifica
- `UserController` — solo los usuarios suben su propia foto desde el perfil
- `views/users/edit.php`
- `views/users/create.php`

## Archivos involucrados
| Archivo | Acción |
|---------|--------|
| `migrations/007_add_avatar_to_users.sql` | Crear |
| `controllers/ProfileController.php` | Modificar |
| `views/profile/index.php` | Modificar |
| `views/layouts/main.php` | Modificar |
| `views/users/index.php` | Modificar |
