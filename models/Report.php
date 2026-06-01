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
        $hasMetricEvents = self::hasMetricEventsTable($pdo);
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
        $ticketsDiaSql = $hasMetricEvents
            ? '(SELECT COUNT(DISTINCT me.ticket_id)
                FROM metricas_eventos me
                WHERE me.usuario_id = m.usuario_id
                  AND me.fecha = m.fecha
                  AND me.ticket_id IS NOT NULL)'
            : '(SELECT COUNT(DISTINCT t.id)
                FROM tickets t
                WHERE t.deleted_at IS NULL
                  AND DATE(t.updated_at) = m.fecha
                  AND (t.asignado_a = m.usuario_id OR t.creado_por = m.usuario_id))';

        $smsInboundSql = $hasMetricEvents
            ? '(SELECT COUNT(*) FROM metricas_eventos me WHERE me.usuario_id = m.usuario_id AND me.fecha = m.fecha AND me.modo = "inbound_calls" AND me.ticket_id IS NOT NULL)'
            : 'm.inbound_calls';
        $smsOutboundSql = $hasMetricEvents
            ? '(SELECT COUNT(*) FROM metricas_eventos me WHERE me.usuario_id = m.usuario_id AND me.fecha = m.fecha AND me.modo = "outbound_calls" AND me.ticket_id IS NOT NULL)'
            : 'm.outbound_calls';
        $smsChatsSql = $hasMetricEvents
            ? '(SELECT COUNT(*) FROM metricas_eventos me WHERE me.usuario_id = m.usuario_id AND me.fecha = m.fecha AND me.modo = "chats" AND me.ticket_id IS NOT NULL)'
            : 'm.chats';
        $smsEmailsSql = $hasMetricEvents
            ? '(SELECT COUNT(*) FROM metricas_eventos me WHERE me.usuario_id = m.usuario_id AND me.fecha = m.fecha AND me.modo = "emails" AND me.ticket_id IS NOT NULL)'
            : 'm.emails';

        $sql = 'SELECT m.*, u.nombre, (m.inbound_calls + m.outbound_calls + m.failed_calls + m.chats + m.emails) AS total_interacciones,
                ' . $ticketsDiaSql . ' AS tickets_dia,
                ' . $smsInboundSql . ' AS sms_inbound_calls,
                ' . $smsOutboundSql . ' AS sms_outbound_calls,
                ' . $smsChatsSql . ' AS sms_chats,
                ' . $smsEmailsSql . ' AS sms_emails,
                (
                  SELECT COUNT(*)
                  FROM tickets t
                  WHERE t.deleted_at IS NULL
                    AND t.estado = "preguntar"
                    AND DATE(t.updated_at) = m.fecha
                    AND (t.asignado_a = m.usuario_id OR t.creado_por = m.usuario_id)
                ) AS tickets_hq,
                (
                  SELECT GROUP_CONCAT(DISTINCT COALESCE(NULLIF(t.ticket_number, ""), CONCAT("ID-", t.id)) ORDER BY t.updated_at DESC SEPARATOR ", ")
                  FROM tickets t
                  WHERE t.deleted_at IS NULL
                    AND DATE(t.updated_at) = m.fecha
                    AND (t.asignado_a = m.usuario_id OR t.creado_por = m.usuario_id)
                ) AS tickets_lista
                FROM metricas_diarias m
                INNER JOIN usuarios u ON u.id = m.usuario_id
                WHERE ' . implode(' AND ', $where) . ' ORDER BY m.fecha DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private static function hasMetricEventsTable(PDO $pdo): bool
    {
        return (bool)$pdo->query("SHOW TABLES LIKE 'metricas_eventos'")->fetchColumn();
    }
}
