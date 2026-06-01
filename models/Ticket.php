<?php
declare(strict_types=1);

class Ticket
{
    public static function searchByRole(array $user, array $filters, int $page, int $perPage): array
    {
        $pdo = Database::connection();
        $where = ['t.deleted_at IS NULL'];
        $params = [];

        if ($user['rol'] === 'usuario_normal') {
            $where[] = 't.asignado_a = :uid';
            $params['uid'] = $user['id'];
        }
        if (isset($filters['tag_id']) && (int)$filters['tag_id'] > 0) {
            $where[] = 'EXISTS (SELECT 1 FROM ticket_tags tt WHERE tt.ticket_id = t.id AND tt.tag_id = :tag_id)';
            $params['tag_id'] = (int)$filters['tag_id'];
        }
        if (!empty($filters['estado'])) {
            $where[] = 't.estado = :estado';
            $params['estado'] = $filters['estado'];
        }
        $fechaCierre = $filters['fecha_cierre'] ?? date('Y-m-d');
        if (!empty($filters['estado']) && $filters['estado'] === 'cerrado') {
            $where[] = 'DATE(t.updated_at) = :fecha_cierre';
            $params['fecha_cierre'] = $fechaCierre;
        } elseif (empty($filters['estado'])) {
            $where[] = '(t.estado <> "cerrado" OR DATE(t.updated_at) = :fecha_cierre)';
            $params['fecha_cierre'] = $fechaCierre;
        }
        if (isset($filters['prioridad_id']) && (int)$filters['prioridad_id'] > 0) {
            $where[] = 't.prioridad_id = :prioridad_id';
            $params['prioridad_id'] = (int)$filters['prioridad_id'];
        }
        if ($user['rol'] !== 'usuario_normal' && isset($filters['asignado_a']) && (int)$filters['asignado_a'] > 0) {
            $where[] = 't.asignado_a = :asignado_a';
            $params['asignado_a'] = (int)$filters['asignado_a'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(CAST(t.id AS CHAR) LIKE :q OR t.ticket_number LIKE :q OR t.email LIKE :q OR t.phone LIKE :q OR t.problem_name LIKE :q OR t.estado_info LIKE :q)';
            $params['q'] = '%' . trim((string)$filters['q']) . '%';
        }

        $whereSql = implode(' AND ', $where);
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM tickets t WHERE ' . $whereSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = 'SELECT t.*, p.nombre AS prioridad_nombre, p.nivel AS prioridad_nivel, u.nombre AS asignado_nombre
                FROM tickets t
                LEFT JOIN prioridades p ON p.id = t.prioridad_id
                LEFT JOIN usuarios u ON u.id = t.asignado_a
                WHERE ' . $whereSql . '
                ORDER BY t.created_at DESC
                LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
        ];
    }

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
        $pais = self::nullIfEmpty(self::limitLen($data['pais'] ?? null, 100));
        if ($pais === null) {
            $pais = 'United States';
        }
        $problemName = self::nullIfEmpty(self::limitLen($data['problem_name'] ?? null, 150));
        if ($problemName === null) {
            $problemName = 'Pending information';
        }
        $stmt->execute([
            'ticket_number' => self::nullIfEmpty(self::limitLen($data['ticket_number'] ?? null, 30)),
            'pais' => $pais,
            'phone' => self::nullIfEmpty(self::limitLen($data['phone'] ?? null, 30)),
            'email' => self::nullIfEmpty(self::limitLen($data['email'] ?? null, 150)),
            'estado' => $data['estado'] ?? 'no_tomado',
            'estado_info' => self::nullIfEmpty(self::limitLen($data['estado_info'] ?? null, 100)),
            'problem_name' => $problemName,
            'description' => self::nullIfEmpty($data['description'] ?? null),
            'prioridad_id' => (int)($data['prioridad_id'] ?? 2),
            'fecha_vencimiento' => null,
            'sla_horas' => null,
            'creado_por' => $user['id'],
            'asignado_a' => $user['id'],
        ]);
        $ticketId = (int) $pdo->lastInsertId();
        Audit::log((int)$user['id'], 'CREAR_TICKET', 'tickets', $ticketId);
        return $ticketId;
    }

    public static function existsByTicketNumber(string $ticketNumber): bool
    {
        $ticketNumber = trim($ticketNumber);
        if ($ticketNumber === '') {
            return false;
        }
        $stmt = Database::connection()->prepare('SELECT id FROM tickets WHERE ticket_number = :ticket_number LIMIT 1');
        $stmt->execute(['ticket_number' => self::limitLen($ticketNumber, 30)]);
        return (bool)$stmt->fetchColumn();
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
        $hasMetricEvents = (bool)$pdo->query("SHOW TABLES LIKE 'metricas_eventos'")->fetchColumn();
        if (!$hasMetricEvents) {
            $stmt = $pdo->prepare('SELECT c.*, u.nombre AS usuario_nombre, NULL AS metric_modes FROM comentarios_ticket c INNER JOIN usuarios u ON u.id = c.usuario_id WHERE c.ticket_id = :ticket_id ORDER BY c.created_at DESC');
            $stmt->execute(['ticket_id' => $ticketId]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT c.*, u.nombre AS usuario_nombre,
                               GROUP_CONCAT(DISTINCT me.modo ORDER BY me.modo SEPARATOR ",") AS metric_modes
                               FROM comentarios_ticket c
                               INNER JOIN usuarios u ON u.id = c.usuario_id
                               LEFT JOIN metricas_eventos me
                                 ON me.ticket_id = c.ticket_id
                                AND me.usuario_id = c.usuario_id
                                AND me.comentario = c.comentario
                                AND me.fecha = DATE(c.created_at)
                               WHERE c.ticket_id = :ticket_id
                               GROUP BY c.id, c.ticket_id, c.usuario_id, c.comentario, c.es_interno, c.created_at, u.nombre
                               ORDER BY c.created_at DESC');
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public static function updateFields(int $id, array $data, array $user): bool
    {
        $pdo = Database::connection();
        $current = self::findById($id, $user);
        if (!$current) {
            return false;
        }

        $allowed = ['ticket_number', 'pais', 'phone', 'email', 'problem_name', 'description', 'prioridad_id', 'estado_info'];
        $payload = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = self::nullIfEmpty($data[$field]);
            }
        }
        if (empty($payload)) {
            return false;
        }

        $stmt = $pdo->prepare('UPDATE tickets SET ticket_number = :ticket_number, pais = :pais, phone = :phone, email = :email, problem_name = :problem_name, description = :description, prioridad_id = :prioridad_id, estado_info = :estado_info WHERE id = :id AND deleted_at IS NULL');
        $ok = $stmt->execute([
            'ticket_number' => $payload['ticket_number'] ?? $current['ticket_number'],
            'pais' => $payload['pais'] ?? $current['pais'],
            'phone' => $payload['phone'] ?? $current['phone'],
            'email' => $payload['email'] ?? $current['email'],
            'problem_name' => $payload['problem_name'] ?? $current['problem_name'],
            'description' => $payload['description'] ?? $current['description'],
            'prioridad_id' => $payload['prioridad_id'] ?? $current['prioridad_id'],
            'estado_info' => $payload['estado_info'] ?? $current['estado_info'],
            'id' => $id,
        ]);

        if ($ok) {
            foreach ($payload as $field => $newValue) {
                $oldValue = $current[$field] ?? null;
                if ((string)$oldValue !== (string)$newValue) {
                    self::addHistory($id, (int)$user['id'], $field, (string)$oldValue, (string)$newValue);
                }
            }
            Audit::log((int)$user['id'], 'ACTUALIZAR_TICKET', 'tickets', $id);
        }

        return $ok;
    }

    public static function addComment(int $ticketId, int $userId, string $comment, int $esInterno = 1): bool
    {
        try {
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
        } catch (Throwable) {
            return false;
        }
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

    public static function softDeleteMany(array $ids, int $userId): int
    {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $v): bool => $v > 0)));
        if (empty($cleanIds)) {
            return 0;
        }

        $pdo = Database::connection();
        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $stmt = $pdo->prepare('UPDATE tickets SET deleted_at = NOW() WHERE deleted_at IS NULL AND id IN (' . $placeholders . ')');
        $stmt->execute($cleanIds);
        $deleted = (int)$stmt->rowCount();

        if ($deleted > 0) {
            foreach ($cleanIds as $ticketId) {
                Audit::log($userId, 'SOFT_DELETE_TICKET', 'tickets', $ticketId);
            }
        }

        return $deleted;
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

    private static function limitLen(mixed $value, int $maxLen): ?string
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string)$value);
        if ($text === '') {
            return null;
        }
        return mb_substr($text, 0, $maxLen);
    }
}
