<?php
declare(strict_types=1);

class Ticket
{
    public static function listByRole(array $user, ?int $tagId = null): array
    {
        $pdo = Database::connection();
        $baseSql = 'SELECT t.*, p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel, u.nombre AS asignado_nombre
                    FROM tickets t
                    LEFT JOIN prioridades p ON p.id = t.prioridad_id
                    LEFT JOIN usuarios u ON u.id = t.asignado_a
                    WHERE t.deleted_at IS NULL';

        $params = [];
        if ($tagId !== null && $tagId > 0) {
            $baseSql .= ' AND EXISTS (SELECT 1 FROM ticket_tags tt WHERE tt.ticket_id = t.id AND tt.tag_id = :tag_id)';
            $params['tag_id'] = $tagId;
        }

        if ($user['rol'] === 'usuario_normal') {
            $stmt = $pdo->prepare($baseSql . ' AND t.asignado_a = :uid ORDER BY t.created_at DESC');
            $params['uid'] = $user['id'];
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare($baseSql . ' ORDER BY t.created_at DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data, array $user): int
    {
        $pdo = Database::connection();
        $sql = 'INSERT INTO tickets (ticket_number, pais, phone, email, estado, estado_info, problem_name, description, prioridad_id, fecha_vencimiento, sla_horas, creado_por, asignado_a)
                VALUES (:ticket_number, :pais, :phone, :email, :estado, :estado_info, :problem_name, :description, :prioridad_id, :fecha_vencimiento, :sla_horas, :creado_por, :asignado_a)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'ticket_number' => self::nullIfEmpty($data['ticket_number'] ?? null),
            'pais' => self::nullIfEmpty($data['pais'] ?? null),
            'phone' => self::nullIfEmpty($data['phone'] ?? null),
            'email' => self::nullIfEmpty($data['email'] ?? null),
            'estado' => $data['estado'] ?? 'no_tomado',
            'estado_info' => self::nullIfEmpty($data['estado_info'] ?? null),
            'problem_name' => self::nullIfEmpty($data['problem_name'] ?? null),
            'description' => self::nullIfEmpty($data['description'] ?? null),
            'prioridad_id' => (int)($data['prioridad_id'] ?? 2),
            'fecha_vencimiento' => self::nullIfEmpty($data['fecha_vencimiento'] ?? null),
            'sla_horas' => (int)($data['sla_horas'] ?? 24),
            'creado_por' => $user['id'],
            'asignado_a' => self::nullIfEmpty($data['asignado_a'] ?? null),
        ]);
        $ticketId = (int) $pdo->lastInsertId();
        Audit::log((int)$user['id'], 'CREAR_TICKET', 'tickets', $ticketId);
        return $ticketId;
    }

    public static function findById(int $id, array $user): ?array
    {
        $pdo = Database::connection();
        $sql = 'SELECT t.*, p.nombre AS prioridad_nombre, u.nombre AS asignado_nombre
                FROM tickets t
                LEFT JOIN prioridades p ON p.id = t.prioridad_id
                LEFT JOIN usuarios u ON u.id = t.asignado_a
                WHERE t.id = :id AND t.deleted_at IS NULL';
        if ($user['rol'] === 'usuario_normal') {
            $sql .= ' AND t.asignado_a = :uid';
        }
        $stmt = $pdo->prepare($sql);
        $params = ['id' => $id];
        if ($user['rol'] === 'usuario_normal') {
            $params['uid'] = $user['id'];
        }
        $stmt->execute($params);
        $ticket = $stmt->fetch();
        return $ticket ?: null;
    }

    public static function updateStatus(int $id, string $estado, ?string $estadoInfo, array $user): bool
    {
        $pdo = Database::connection();
        $allowed = ['no_tomado', 'respondido', 'cerrado', 'preguntar'];
        if (!in_array($estado, $allowed, true)) {
            return false;
        }
        $sql = 'UPDATE tickets SET estado = :estado, estado_info = :estado_info, cerrado_por = :cerrado_por WHERE id = :id AND deleted_at IS NULL';
        if ($user['rol'] === 'usuario_normal') {
            $sql .= ' AND asignado_a = :uid';
        }
        $stmt = $pdo->prepare($sql);
        $params = [
            'estado' => $estado,
            'estado_info' => self::nullIfEmpty($estadoInfo),
            'cerrado_por' => $estado === 'cerrado' ? $user['id'] : null,
            'id' => $id,
        ];
        if ($user['rol'] === 'usuario_normal') {
            $params['uid'] = $user['id'];
        }
        $ok = $stmt->execute($params);
        if ($ok && $stmt->rowCount() > 0) {
            self::addHistory($id, (int)$user['id'], 'estado', null, $estado);
            self::addHistory($id, (int)$user['id'], 'estado_info', null, self::nullIfEmpty($estadoInfo));
            Audit::log((int)$user['id'], 'CAMBIO_ESTADO_TICKET', 'tickets', $id);
        }
        return $ok;
    }

    public static function assign(int $id, int $userId): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE tickets SET asignado_a = :asignado_a WHERE id = :id AND deleted_at IS NULL');
        $ok = $stmt->execute(['asignado_a' => $userId, 'id' => $id]);
        if ($ok && $stmt->rowCount() > 0) {
            self::addHistory($id, $userId, 'asignado_a', null, (string)$userId);
            Audit::log($userId, 'ASIGNAR_TICKET', 'tickets', $id);
        }
        return $ok;
    }

    public static function comments(int $ticketId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT c.*, u.nombre AS usuario_nombre FROM comentarios_ticket c INNER JOIN usuarios u ON u.id = c.usuario_id WHERE c.ticket_id = :ticket_id ORDER BY c.created_at DESC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public static function addComment(int $ticketId, int $userId, string $comment, int $esInterno = 1): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO comentarios_ticket (ticket_id, usuario_id, comentario, es_interno) VALUES (:ticket_id, :usuario_id, :comentario, :es_interno)');
        $ok = $stmt->execute([
            'ticket_id' => $ticketId,
            'usuario_id' => $userId,
            'comentario' => trim($comment),
            'es_interno' => $esInterno,
        ]);
        if ($ok) {
            Audit::log($userId, 'COMENTARIO_TICKET', 'tickets', $ticketId);
        }
        return $ok;
    }

    public static function softDelete(int $id, int $userId): bool
    {
        $stmt = Database::connection()->prepare('UPDATE tickets SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $ok = $stmt->execute(['id' => $id]);
        if ($ok && $stmt->rowCount() > 0) {
            Audit::log($userId, 'SOFT_DELETE_TICKET', 'tickets', $id);
        }
        return $ok;
    }

    public static function tags(int $ticketId): array
    {
        $stmt = Database::connection()->prepare('SELECT t.id, t.nombre FROM tags t INNER JOIN ticket_tags tt ON tt.tag_id = t.id WHERE tt.ticket_id = :ticket_id ORDER BY t.nombre ASC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    private static function addHistory(int $ticketId, int $userId, string $field, ?string $old, ?string $new): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO ticket_historial (ticket_id, usuario_id, campo_modificado, valor_anterior, valor_nuevo) VALUES (:ticket_id, :usuario_id, :campo_modificado, :valor_anterior, :valor_nuevo)');
        $stmt->execute([
            'ticket_id' => $ticketId,
            'usuario_id' => $userId,
            'campo_modificado' => $field,
            'valor_anterior' => $old,
            'valor_nuevo' => $new,
        ]);
    }

    public static function history(int $ticketId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT h.*, u.nombre AS usuario_nombre FROM ticket_historial h LEFT JOIN usuarios u ON u.id = h.usuario_id WHERE h.ticket_id = :ticket_id ORDER BY h.created_at DESC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    private static function nullIfEmpty(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
