<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\ThrottleMiddleware;

/** @var Router $router */

$router->addGlobalMiddleware(CsrfMiddleware::class);

// ============================================
// RUTAS DE AUTENTICACIÓN
// ============================================
$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login', [ThrottleMiddleware::class]);
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotForm');
$router->post('/forgot-password', 'AuthController@sendResetLink');
$router->get('/reset-password/{token}', 'AuthController@showResetForm');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/change-password', 'AuthController@showChangePasswordForm', [AuthMiddleware::class]);
$router->post('/change-password', 'AuthController@changePassword', [AuthMiddleware::class]);

// ============================================
// RUTAS PROTEGIDAS
// ============================================
$router->group(['middleware' => AuthMiddleware::class], function (Router $router) {

    // Dashboard
    $router->get('/', 'DashboardController@index');
    $router->get('/dashboard', 'DashboardController@index');

    // ============================================
    // USUARIOS (Super Usuario y Administrador)
    // ============================================
    $router->group(['middleware' => [new RoleMiddleware(['super_usuario', 'administrador'])]], function (Router $router) {
        $router->get('/usuarios', 'UserController@index');
        $router->get('/usuarios/crear', 'UserController@create');
        $router->post('/usuarios/crear', 'UserController@store');
        $router->get('/usuarios/{id}/editar', 'UserController@edit');
        $router->post('/usuarios/{id}/editar', 'UserController@update');
        $router->post('/usuarios/{id}/toggle-estado', 'UserController@toggleStatus');
        $router->post('/usuarios/{id}/reset-password', 'UserController@resetPassword');
        $router->post('/usuarios/{id}/eliminar', 'UserController@delete');
        $router->post('/usuarios/{id}/restaurar', 'UserController@restore');
        $router->get('/usuarios/eliminados', 'UserController@trashed');

        // Formularios (solo Super Usuario puede crear/editar)
        $router->group(['middleware' => [new RoleMiddleware(['super_usuario'])]], function (Router $router) {
            $router->get('/formularios', 'FormBuilderController@index');
            $router->get('/formularios/crear', 'FormBuilderController@create');
            $router->post('/formularios/crear', 'FormBuilderController@store');
            $router->get('/formularios/{id}/editar', 'FormBuilderController@edit');
            $router->post('/formularios/{id}/editar', 'FormBuilderController@update');
            $router->post('/formularios/{id}/duplicar', 'FormBuilderController@duplicate');
            $router->post('/formularios/{id}/toggle', 'FormBuilderController@toggleStatus');
            $router->post('/formularios/{id}/eliminar', 'FormBuilderController@delete');
            $router->post('/formularios/guardar-campos', 'FormBuilderController@saveFields');

            // Auditoría
            $router->get('/auditoria', 'AuditController@index');
            $router->get('/auditoria/exportar', 'AuditController@export');
            $router->get('/auditoria/{id}', 'AuditController@show');

            // Configuración
            $router->get('/configuracion', 'ConfigController@index');
            $router->post('/configuracion', 'ConfigController@update');
        });
    });

    // ============================================
    // REGISTROS (todos los usuarios autenticados)
    // ============================================
    $router->get('/registros', 'RecordController@index');
    $router->get('/registros/crear/{form_id}', 'RecordController@create');
    $router->post('/registros/crear/{form_id}', 'RecordController@store');
    $router->get('/registros/{id}', 'RecordController@show');
    $router->get('/registros/{id}/editar', 'RecordController@edit');
    $router->post('/registros/{id}/editar', 'RecordController@update');
    $router->post('/registros/{id}/archivar', 'RecordController@archive');
    $router->post('/registros/{id}/restaurar', 'RecordController@restore');
    $router->post('/registros/{id}/eliminar', 'RecordController@destroy');
    $router->get('/registros/{id}/historial', 'RecordController@history');

    // ============================================
    // REPORTES (Super Usuario y Administrador)
    // ============================================
    $router->group(['middleware' => [new RoleMiddleware(['super_usuario', 'administrador'])]], function (Router $router) {
        $router->get('/reportes', 'ReportController@index');
        $router->get('/reportes/formulario/{id}', 'ReportController@form');
        $router->get('/reportes/timeline', 'ReportController@timeline');
        $router->post('/reportes/exportar/{tipo}', 'ReportController@export');
        $router->post('/reportes/favoritos/guardar', 'ReportController@saveFavorite');
        $router->post('/reportes/favoritos/{id}/eliminar', 'ReportController@deleteFavorite');
    });

    // ============================================
    // EXPORTACIONES
    // ============================================
    $router->get('/exportar/{modulo}', 'ExportController@index');
    $router->post('/exportar/{modulo}/excel', 'ExportController@excel');
    $router->post('/exportar/{modulo}/csv', 'ExportController@csv');
    $router->post('/exportar/{modulo}/pdf', 'ExportController@pdfList');
    $router->get('/exportar/{modulo}/pdf/{id}', 'ExportController@pdf');

    // ============================================
    // NOTIFICACIONES
    // ============================================
    $router->get('/notificaciones', 'NotificationController@index');
    $router->post('/notificaciones/marcar-leido/{id}', 'NotificationController@markAsRead');
    $router->post('/notificaciones/marcar-todas', 'NotificationController@markAllAsRead');

    // ============================================
    // PERFIL
    // ============================================
    $router->get('/perfil', 'ProfileController@index');
    $router->post('/perfil', 'ProfileController@update');
    $router->post('/perfil/cambiar-password', 'ProfileController@changePassword');

    // ============================================
    // API / AJAX ENDPOINTS
    // ============================================
    $router->get('/api/notificaciones/no-leidas', 'NotificationController@unreadCount');
    $router->get('/api/notificaciones/ultimas', 'NotificationController@latest');
});
