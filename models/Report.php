<?php
declare(strict_types=1);

class Report
{
    public static function pendingTickets(array $user): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT t.ticket_number, t.problem_name, t.estado_info, p.nombre AS prioridad, u.nombre AS asignado
                FROM tickets t
                LEFT JOIN prioridades p ON p.id = t.prioridad_id
                LEFT JOIN usuarios u ON u.id = t.asignado_a
                WHERE t.deleted_at IS NULL AND t.estado <> "cerrado"';
        if ($user['rol'] === 'usuario_normal') {
            $sql .= ' AND t.asignado_a = :uid';
            $stmt = $pdo->prepare($sql . ' ORDER BY p.nivel DESC, t.fecha_vencimiento ASC');
            $stmt->execute(['uid' => $user['id']]);
            return $stmt->fetchAll();
        }
        return $pdo->query($sql . ' ORDER BY p.nivel DESC, t.fecha_vencimiento ASC')->fetchAll();
    }

    public static function pendingTicketsByDay(array $user): array
    {
        $rows = self::pendingTicketsRaw($user);
        $grouped = [];

        foreach ($rows as $row) {
            $dayKey = substr((string)$row['created_at'], 0, 10);
            if ($dayKey === '' || $dayKey === false) {
                $dayKey = date('Y-m-d');
            }
            if (!isset($grouped[$dayKey])) {
                $grouped[$dayKey] = [
                    'date' => $dayKey,
                    'count' => 0,
                    'tickets' => [],
                ];
            }

            $grouped[$dayKey]['count']++;
            $grouped[$dayKey]['tickets'][] = [
                'ticket_number' => $row['ticket_number'] ?: ('ID-' . $row['id']),
                'problem_name' => $row['problem_name'] ?: 'Sin problema',
                'estado_info' => $row['estado_info'] ?: '-',
            ];
        }

        krsort($grouped);
        return array_values($grouped);
    }

    private static function pendingTicketsRaw(array $user): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT t.id, t.ticket_number, t.problem_name, t.estado_info, t.created_at, p.nombre AS prioridad, u.nombre AS asignado
                FROM tickets t
                LEFT JOIN prioridades p ON p.id = t.prioridad_id
                LEFT JOIN usuarios u ON u.id = t.asignado_a
                WHERE t.deleted_at IS NULL AND t.estado <> "cerrado"';
        if ($user['rol'] === 'usuario_normal') {
            $sql .= ' AND t.asignado_a = :uid';
            $stmt = $pdo->prepare($sql . ' ORDER BY t.created_at DESC, p.nivel DESC, t.fecha_vencimiento ASC');
            $stmt->execute(['uid' => $user['id']]);
            return $stmt->fetchAll();
        }
        return $pdo->query($sql . ' ORDER BY t.created_at DESC, p.nivel DESC, t.fecha_vencimiento ASC')->fetchAll();
    }

    public static function sla(): array
    {
        $pdo = Database::connection();
        $sql = 'SELECT t.id, t.ticket_number, t.problem_name, t.estado, t.fecha_vencimiento, p.nombre AS prioridad, p.nivel
                FROM tickets t
                LEFT JOIN prioridades p ON p.id = t.prioridad_id
                WHERE t.deleted_at IS NULL AND t.estado <> "cerrado"';
        return $pdo->query($sql)->fetchAll();
    }

    public static function metrics(array $user, ?string $date, ?int $userId, ?string $from, ?string $to): array
    {
        $pdo = Database::connection();
        $where = ['1=1'];
        $params = [];
        if ($date) {
            $where[] = 'm.fecha = :fecha';
            $params['fecha'] = $date;
        }
        if ($from && $to) {
            $where[] = 'm.fecha BETWEEN :desde AND :hasta';
            $params['desde'] = $from;
            $params['hasta'] = $to;
        }
        if ($user['rol'] === 'usuario_normal') {
            $where[] = 'm.usuario_id = :uid';
            $params['uid'] = $user['id'];
        } elseif ($userId) {
            $where[] = 'm.usuario_id = :uid';
            $params['uid'] = $userId;
        }
        $sql = 'SELECT m.*, u.nombre, (m.inbound_calls + m.outbound_calls + m.failed_calls + m.chats + m.emails) AS total_interacciones,
                (
                  SELECT COUNT(*)
                  FROM tickets t
                  WHERE t.deleted_at IS NULL
                    AND t.estado = "preguntar"
                    AND DATE(t.updated_at) = m.fecha
                    AND (t.asignado_a = m.usuario_id OR t.creado_por = m.usuario_id)
                ) AS tickets_hq
                FROM metricas_diarias m
                INNER JOIN usuarios u ON u.id = m.usuario_id
                WHERE ' . implode(' AND ', $where) . ' ORDER BY m.fecha DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
