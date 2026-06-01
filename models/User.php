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

    public static function all(): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT u.id, u.nombre, u.email, u.activo, r.nombre AS rol, u.rol_id
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                ORDER BY u.created_at DESC';
        return $pdo->query($sql)->fetchAll();
    }

    public static function roles(): array
    {
        return Database::connection()->query('SELECT id, nombre FROM roles ORDER BY id ASC')->fetchAll();
    }

    public static function create(array $data): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol_id, activo) VALUES (:nombre, :email, :password_hash, :rol_id, :activo)');
        $stmt->execute([
            'nombre' => trim((string)$data['nombre']),
            'email' => mb_strtolower(trim((string)$data['email'])),
            'password_hash' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
            'rol_id' => (int)$data['rol_id'],
            'activo' => isset($data['activo']) ? 1 : 0,
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        $sql = 'UPDATE usuarios SET nombre = :nombre, email = :email, rol_id = :rol_id, activo = :activo';
        $params = [
            'nombre' => trim((string)$data['nombre']),
            'email' => mb_strtolower(trim((string)$data['email'])),
            'rol_id' => (int)$data['rol_id'],
            'activo' => isset($data['activo']) ? 1 : 0,
            'id' => $id,
        ];
        if (!empty($data['password'])) {
            $sql .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, nombre, email, rol_id, activo FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $u = $stmt->fetch();
        return $u ?: null;
    }
}
