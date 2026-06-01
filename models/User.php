<?php
declare(strict_types=1);

class User
{
    public static function assignableUsers(): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query("SELECT u.id, u.nombre, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE u.activo = 1 AND r.nombre IN ('admin', 'usuario_normal', 'gerente') ORDER BY u.nombre ASC");
        return $stmt->fetchAll();
    }
}
