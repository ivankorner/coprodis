<?php

namespace App\Services;

use App\Core\Database;

class NotificationService
{
    public static function create(int $userId, string $titulo, string $mensaje = null, string $tipo = 'info'): int
    {
        $db = Database::getInstance();
        return $db->insert('notifications', [
            'user_id' => $userId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
        ]);
    }

    public static function createForAll(string $titulo, string $mensaje = null, string $tipo = 'info', ?array $excludeUserIds = []): void
    {
        $db = Database::getInstance();
        $exclude = '';
        $params = [];

        if (!empty($excludeUserIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeUserIds), '?'));
            $exclude = "AND id NOT IN ({$placeholders})";
            $params = $excludeUserIds;
        }

        $users = $db->fetchAll(
            "SELECT id FROM users WHERE estado = 'activo' AND deleted_at IS NULL {$exclude}",
            $params
        );

        foreach ($users as $user) {
            self::create($user->id, $titulo, $mensaje, $tipo);
        }
    }

    public static function createForRole(string $rolSlug, string $titulo, string $mensaje = null, string $tipo = 'info'): void
    {
        $db = Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.id FROM users u
             JOIN roles r ON u.rol_id = r.id
             WHERE r.slug = :rol AND u.estado = 'activo' AND u.deleted_at IS NULL",
            ['rol' => $rolSlug]
        );

        foreach ($users as $user) {
            self::create($user->id, $titulo, $mensaje, $tipo);
        }
    }

    public static function getUserNotifications(int $userId, int $page = 1, int $limit = null): array
    {
        $db = Database::getInstance();
        $limit = $limit ?? PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM notifications
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT {$limit} OFFSET {$offset}";

        $countSql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id";

        $data = $db->fetchAll($sql, ['user_id' => $userId]);
        $total = (int)$db->fetch($countSql, ['user_id' => $userId])->total;

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => (int)ceil($total / $limit),
        ];
    }

    public static function getUnreadCount(int $userId): int
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as total FROM notifications WHERE user_id = :user_id AND leido = FALSE",
            ['user_id' => $userId]
        );
        return (int)$result->total;
    }

    public static function getLatest(int $userId, int $limit = 5): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM notifications
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT {$limit}",
            ['user_id' => $userId]
        );
    }

    public static function markAsRead(int $notificationId, int $userId): bool
    {
        $db = Database::getInstance();
        $db->update('notifications', ['leido' => true],
            'id = :id AND user_id = :user_id',
            ['id' => $notificationId, 'user_id' => $userId]
        );
        return true;
    }

    public static function markAllAsRead(int $userId): bool
    {
        $db = Database::getInstance();
        $db->update('notifications', ['leido' => true],
            'user_id = :user_id AND leido = FALSE',
            ['user_id' => $userId]
        );
        return true;
    }
}
