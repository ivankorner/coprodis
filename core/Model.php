<?php

namespace App\Core;

class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static bool $softDelete = true;
    protected static array $fillable = [];
    protected static array $searchable = [];

    public static function all(): array
    {
        $db = Database::getInstance();
        $table = static::$table;
        $sql = "SELECT * FROM {$table}";
        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        return $db->fetchAll($sql);
    }

    public static function find(int $id): ?object
    {
        $db = Database::getInstance();
        $table = static::$table;
        $pk = static::$primaryKey;
        $sql = "SELECT * FROM {$table} WHERE {$pk} = :id";
        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            $sql .= " AND deleted_at IS NULL";
        }
        return $db->fetch($sql, ['id' => $id]);
    }

    public static function findWithTrashed(int $id): ?object
    {
        $db = Database::getInstance();
        $table = static::$table;
        $pk = static::$primaryKey;
        return $db->fetch("SELECT * FROM {$table} WHERE {$pk} = :id", ['id' => $id]);
    }

    public static function where(string $column, $value): array
    {
        $db = Database::getInstance();
        $table = static::$table;
        $sql = "SELECT * FROM {$table} WHERE {$column} = :value";
        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            $sql .= " AND deleted_at IS NULL";
        }
        return $db->fetchAll($sql, ['value' => $value]);
    }

    public static function whereFirst(string $column, $value): ?object
    {
        $db = Database::getInstance();
        $table = static::$table;
        $sql = "SELECT * FROM {$table} WHERE {$column} = :value";
        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            $sql .= " AND deleted_at IS NULL";
        }
        return $db->fetch($sql . " LIMIT 1", ['value' => $value]);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $fillable = static::$fillable;
        $insertData = array_intersect_key($data, array_flip($fillable));

        if (in_array('created_at', $fillable)) {
            $insertData['created_at'] = date('Y-m-d H:i:s');
        }
        if (in_array('updated_at', $fillable)) {
            $insertData['updated_at'] = date('Y-m-d H:i:s');
        }

        return $db->insert(static::$table, $insertData);
    }

    public static function update(int $id, array $data): int
    {
        $db = Database::getInstance();
        $fillable = static::$fillable;
        $updateData = array_intersect_key($data, array_flip($fillable));

        if (in_array('updated_at', $fillable)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
        }

        $pk = static::$primaryKey;
        return $db->update(static::$table, $updateData, "{$pk} = :id", ['id' => $id]);
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        $table = static::$table;
        $pk = static::$primaryKey;

        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            return $db->update($table, ['deleted_at' => date('Y-m-d H:i:s')], "{$pk} = :id", ['id' => $id]);
        }

        return $db->delete($table, "{$pk} = :id", ['id' => $id]);
    }

    public static function restore(int $id): int
    {
        if (!static::$softDelete) {
            return 0;
        }
        $db = Database::getInstance();
        $table = static::$table;
        $pk = static::$primaryKey;
        return $db->update($table, ['deleted_at' => null], "{$pk} = :id", ['id' => $id]);
    }

    public static function onlyTrashed(): array
    {
        if (!static::$softDelete) {
            return [];
        }
        $db = Database::getInstance();
        $table = static::$table;
        return $db->fetchAll("SELECT * FROM {$table} WHERE deleted_at IS NOT NULL");
    }

    public static function count(): int
    {
        $db = Database::getInstance();
        $table = static::$table;
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        return (int)$db->fetch($sql)->total;
    }

    public static function paginate(int $page = 1, int $limit = null, string $where = '', array $params = [], string $orderBy = 'id DESC'): array
    {
        $limit = $limit ?? PAGINATION_LIMIT;
        $offset = ($page - 1) * $limit;
        $db = Database::getInstance();
        $table = static::$table;

        $whereClause = $where;
        if (static::$softDelete && in_array('deleted_at', static::$fillable)) {
            $whereClause = $where ? "({$where}) AND deleted_at IS NULL" : "deleted_at IS NULL";
        }

        $sql = "SELECT * FROM {$table}";
        if ($whereClause) {
            $sql .= " WHERE {$whereClause}";
        }
        $sql .= " ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";

        $countSql = "SELECT COUNT(*) as total FROM {$table}";
        if ($whereClause) {
            $countSql .= " WHERE {$whereClause}";
        }

        $data = $db->fetchAll($sql, $params);
        $total = (int)$db->fetch($countSql, $params)->total;
        $totalPages = (int)ceil($total / $limit);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
            'prev' => $page > 1 ? $page - 1 : null,
            'next' => $page < $totalPages ? $page + 1 : null,
        ];
    }

    public static function search(string $term, int $page = 1, int $limit = null): array
    {
        if (empty(static::$searchable)) {
            return self::paginate($page, $limit);
        }

        $conditions = array_map(fn($col) => "{$col} LIKE :term", static::$searchable);
        $where = '(' . implode(' OR ', $conditions) . ')';
        return self::paginate($page, $limit, $where, ['term' => "%{$term}%"]);
    }
}
