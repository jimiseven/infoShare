<?php
declare(strict_types=1);

class Priority
{
    public static function all(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query('SELECT id, nombre, nivel FROM prioridades ORDER BY nivel ASC');
        return $stmt->fetchAll();
    }
}
