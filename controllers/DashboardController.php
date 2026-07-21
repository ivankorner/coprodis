<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Services\AuditService;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $this->requirePasswordChange();

        $db = Database::getInstance();
        $userRole = Session::userRole();
        $userId = Session::userId();

        if ($userRole === 'usuario') {
            $stats = [
                'mis_registros' => (int)$db->fetch(
                    "SELECT COUNT(*) as total FROM records WHERE deleted_at IS NULL AND user_id = :user_id",
                    ['user_id' => $userId]
                )->total,
                'registros_hoy' => (int)$db->fetch(
                    "SELECT COUNT(*) as total FROM records WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL AND user_id = :user_id",
                    ['user_id' => $userId]
                )->total,
                'registros_mes' => (int)$db->fetch(
                    "SELECT COUNT(*) as total FROM records WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND deleted_at IS NULL AND user_id = :user_id",
                    ['user_id' => $userId]
                )->total,
                'notificaciones_no_leidas' => (int)$db->fetch(
                    "SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND leido = FALSE",
                    ['user_id' => $userId]
                )->total,
            ];

            $ultimosRegistros = $db->fetchAll(
                "SELECT r.*, f.titulo as form_titulo
                 FROM records r
                 JOIN forms f ON r.form_id = f.id
                 WHERE r.deleted_at IS NULL AND r.user_id = :user_id
                 ORDER BY r.created_at DESC
                 LIMIT 10",
                ['user_id' => $userId]
            );

            $registrosPorFormulario = $db->fetchAll(
                "SELECT f.titulo, COUNT(r.id) as total
                 FROM forms f
                 JOIN records r ON f.id = r.form_id AND r.deleted_at IS NULL AND r.user_id = :user_id
                 WHERE f.deleted_at IS NULL
                 GROUP BY f.id, f.titulo
                 ORDER BY total DESC
                 LIMIT 10",
                ['user_id' => $userId]
            );

            $actividadReciente = [];
        } else {
            $stats = [
                'total_usuarios' => (int)$db->fetch("SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL")->total,
                'usuarios_activos' => (int)$db->fetch("SELECT COUNT(*) as total FROM users WHERE estado = 'activo' AND deleted_at IS NULL")->total,
                'usuarios_inactivos' => (int)$db->fetch("SELECT COUNT(*) as total FROM users WHERE estado = 'inactivo' AND deleted_at IS NULL")->total,
                'total_registros' => (int)$db->fetch("SELECT COUNT(*) as total FROM records WHERE deleted_at IS NULL")->total,
                'registros_hoy' => (int)$db->fetch("SELECT COUNT(*) as total FROM records WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL")->total,
                'registros_mes' => (int)$db->fetch("SELECT COUNT(*) as total FROM records WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND deleted_at IS NULL")->total,
                'formularios_publicados' => (int)$db->fetch("SELECT COUNT(*) as total FROM forms WHERE estado = 'publicado' AND deleted_at IS NULL")->total,
                'notificaciones_no_leidas' => (int)$db->fetch("SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND leido = FALSE", ['user_id' => $userId])->total,
            ];

            $actividadReciente = $db->fetchAll(
                "SELECT a.*, u.apellido, u.nombre
                 FROM audits a
                 LEFT JOIN users u ON a.user_id = u.id
                 ORDER BY a.created_at DESC
                 LIMIT 10"
            );

            $ultimosRegistros = $db->fetchAll(
                "SELECT r.*, f.titulo as form_titulo, u.apellido, u.nombre
                 FROM records r
                 JOIN forms f ON r.form_id = f.id
                 JOIN users u ON r.user_id = u.id
                 WHERE r.deleted_at IS NULL
                 ORDER BY r.created_at DESC
                 LIMIT 10"
            );

            $registrosPorFormulario = $db->fetchAll(
                "SELECT f.titulo, COUNT(r.id) as total
                 FROM forms f
                 LEFT JOIN records r ON f.id = r.form_id AND r.deleted_at IS NULL
                 WHERE f.deleted_at IS NULL
                 GROUP BY f.id, f.titulo
                 ORDER BY total DESC
                 LIMIT 10"
            );
        }

        $this->view('dashboard.index', [
            'stats' => $stats,
            'actividadReciente' => $actividadReciente,
            'ultimosRegistros' => $ultimosRegistros,
            'registrosPorFormulario' => $registrosPorFormulario,
            'userRole' => $userRole,
        ]);
    }
}
