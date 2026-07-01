<?php

namespace App\Services;

use App\Core\Database;

class AuditService
{
    public static function register(
        string $accion,
        string $modulo,
        string $descripcion = null,
        int $userId = null
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
            'descripcion' => $descripcion,
            'ip' => trim($ip),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
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
}
