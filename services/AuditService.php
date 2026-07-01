<?php

namespace App\Services;

use App\Core\Database;

class AuditService
{
    public static function register(
        string $accion,
        string $modulo,
        string $descripcion = null,
        int $userId = null,
        string $tipo = 'info',
        array $detalles = [],
        string $entidad = null,
        int $entidadId = null
    ): int {
        $db = Database::getInstance();

        if ($userId === null) {
            $userId = \App\Core\Session::userId();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }

        return $db->insert('audits', [
            'user_id' => $userId,
            'accion' => $accion,
            'modulo' => $modulo,
            'tipo_audit' => $tipo,
            'descripcion' => $descripcion,
            'detalles' => !empty($detalles) ? json_encode($detalles, JSON_UNESCAPED_UNICODE) : null,
            'ip' => trim($ip),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
        ]);
    }

    public static function getAll(int $page = 1, int $limit = null, array $filters = []): array
    {
        $db = Database::getInstance();
        $limit = $limit ?? PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (!empty($filters['accion'])) {
            $where[] = 'a.accion LIKE :accion';
            $params['accion'] = "%{$filters['accion']}%";
        }
        if (!empty($filters['modulo'])) {
            $where[] = 'a.modulo = :modulo';
            $params['modulo'] = $filters['modulo'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'a.user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }
        if (!empty($filters['fecha_desde'])) {
            $where[] = 'a.created_at >= :fecha_desde';
            $params['fecha_desde'] = $filters['fecha_desde'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = 'a.created_at <= :fecha_hasta';
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['ip'])) {
            $where[] = 'a.ip LIKE :ip';
            $params['ip'] = "%{$filters['ip']}%";
        }
        if (!empty($filters['tipo'])) {
            $where[] = 'a.tipo_audit = :tipo';
            $params['tipo'] = $filters['tipo'];
        }
        if (!empty($filters['entidad'])) {
            $where[] = 'a.entidad = :entidad';
            $params['entidad'] = $filters['entidad'];
            if (!empty($filters['entidad_id'])) {
                $where[] = 'a.entidad_id = :entidad_id';
                $params['entidad_id'] = $filters['entidad_id'];
            }
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT a.*, u.apellido, u.nombre, u.email
                FROM audits a
                LEFT JOIN users u ON a.user_id = u.id
                {$whereClause}
                ORDER BY a.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $countSql = "SELECT COUNT(*) as total
                     FROM audits a
                     {$whereClause}";

        $data = $db->fetchAll($sql, $params);
        $total = (int)$db->fetch($countSql, $params)->total;
        $totalPages = (int)ceil($total / $limit);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
        ];
    }

    public static function getById(int $id): ?object
    {
        $db = Database::getInstance();
        $audit = $db->fetch(
            "SELECT a.*, u.apellido, u.nombre, u.email, u.dni as user_dni, r.nombre as user_rol
             FROM audits a
             LEFT JOIN users u ON a.user_id = u.id
             LEFT JOIN roles r ON u.rol_id = r.id
             WHERE a.id = :id",
            ['id' => $id]
        );

        if ($audit && $audit->detalles) {
            $parsed = json_decode($audit->detalles, true);
            $audit->detalles = json_last_error() === JSON_ERROR_NONE ? $parsed : $audit->detalles;
        }

        return $audit ?: null;
    }

    public static function getStats(): array
    {
        $db = Database::getInstance();

        $hoy = $db->fetch("SELECT COUNT(*) as total FROM audits WHERE DATE(created_at) = CURDATE()")->total;
        $semana = $db->fetch("SELECT COUNT(*) as total FROM audits WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)")->total;
        $mes = $db->fetch("SELECT COUNT(*) as total FROM audits WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->total;
        $total = $db->fetch("SELECT COUNT(*) as total FROM audits")->total;

        $porModulo = $db->fetchAll(
            "SELECT modulo, COUNT(*) as total FROM audits GROUP BY modulo ORDER BY total DESC"
        );

        $porTipo = $db->fetchAll(
            "SELECT tipo_audit, COUNT(*) as total FROM audits GROUP BY tipo_audit ORDER BY total DESC"
        );

        $porAccion = $db->fetchAll(
            "SELECT accion, COUNT(*) as total FROM audits GROUP BY accion ORDER BY total DESC LIMIT 10"
        );

        $usuariosActivos = $db->fetchAll(
            "SELECT u.id, u.apellido, u.nombre, u.email, COUNT(*) as total
             FROM audits a JOIN users u ON a.user_id = u.id
             WHERE a.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY u.id ORDER BY total DESC LIMIT 5"
        );

        return [
            'hoy' => (int)$hoy,
            'semana' => (int)$semana,
            'mes' => (int)$mes,
            'total' => (int)$total,
            'por_modulo' => $porModulo,
            'por_tipo' => $porTipo,
            'por_accion' => $porAccion,
            'usuarios_activos' => $usuariosActivos,
        ];
    }

    public static function getTimeline(int $days = 7): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total,
                    SUM(tipo_audit = 'danger') as danger,
                    SUM(tipo_audit = 'warning') as warning,
                    SUM(tipo_audit = 'success') as success,
                    SUM(tipo_audit = 'info') as info
             FROM audits
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
             GROUP BY DATE(created_at)
             ORDER BY fecha ASC"
        );
    }

    public static function getRecent(int $limit = 10): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT a.*, u.apellido, u.nombre, u.email
             FROM audits a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT {$limit}"
        );
    }

    public static function getByRecord(int $recordId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT a.*, u.apellido, u.nombre
             FROM audits a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE (a.entidad = 'record' AND a.entidad_id = :record_id)
                OR (a.modulo = 'registros' AND a.descripcion LIKE :record_ref)
             ORDER BY a.created_at DESC",
            ['record_id' => $recordId, 'record_ref' => "%#{$recordId}%"]
        );
    }
}
