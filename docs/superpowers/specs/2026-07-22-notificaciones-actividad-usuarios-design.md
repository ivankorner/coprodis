# Notificaciones de Actividad de Usuarios

## Resumen
Super Usuario y Administrador reciben notificaciones automáticas cuando cualquier usuario crea un nuevo registro (form submission). Reutiliza el sistema de notificaciones existente sin cambios de UI ni BD.

## Cambios
- **`controllers/RecordController.php`**: en `store()`, después de `AuditService::register()`, llamar a `NotificationService::createForRole()` para los roles `super_usuario` y `administrador` con mensaje informativo.

## Sin cambios
- Tabla `notifications` (no se modifica)
- Vistas (no se modifican)
- Servicios existentes (solo se invocan)
- Rutas (ya existen)

## Mensaje de ejemplo
> Nuevo registro
> Juan Pérez creó un registro en: Formulario de Ventas

## Roles afectados
- **Super Usuario**: recibe notificaciones
- **Administrador**: recibe notificaciones
- **Usuario**: no recibe (solo ve las suyas propias si existen)
