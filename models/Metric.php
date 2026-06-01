<?php
declare(strict_types=1);

class Metric
{
    public static function upsertDaily(array $user, array $data): void
    {
        $pdo = Database::connection();
        $fecha = $data['fecha'] ?: date('Y-m-d');
        $inbound = max(0, (int)($data['inbound_calls'] ?? 0));
        $outbound = max(0, (int)($data['outbound_calls'] ?? 0));
        $failed = max(0, (int)($data['failed_calls'] ?? 0));
        $chats = max(0, (int)($data['chats'] ?? 0));
        $emails = max(0, (int)($data['emails'] ?? 0));

        $sql = 'INSERT INTO metricas_diarias (fecha, usuario_id, inbound_calls, outbound_calls, failed_calls, chats, emails)
                VALUES (:fecha, :usuario_id, :inbound_calls, :outbound_calls, :failed_calls, :chats, :emails)
                ON DUPLICATE KEY UPDATE
                inbound_calls = VALUES(inbound_calls), outbound_calls = VALUES(outbound_calls),
                failed_calls = VALUES(failed_calls), chats = VALUES(chats), emails = VALUES(emails)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'fecha' => $fecha,
            'usuario_id' => $user['id'],
            'inbound_calls' => $inbound,
            'outbound_calls' => $outbound,
            'failed_calls' => $failed,
            'chats' => $chats,
            'emails' => $emails,
        ]);
    }

    public static function incrementFromComment(int $userId, string $mode, ?int $ticketId = null, ?string $comment = null): bool
    {
        $map = [
            'inbound_calls' => 'inbound_calls',
            'outbound_calls' => 'outbound_calls',
            'chats' => 'chats',
            'emails' => 'emails',
        ];

        if (!isset($map[$mode])) {
            return false;
        }

        $column = $map[$mode];
        $pdo = Database::connection();
        $fecha = date('Y-m-d');

        try {
            $pdo->beginTransaction();

            $sql = "INSERT INTO metricas_diarias (fecha, usuario_id, inbound_calls, outbound_calls, failed_calls, chats, emails)
                    VALUES (:fecha, :usuario_id, 0, 0, 0, 0, 0)
                    ON DUPLICATE KEY UPDATE " . $column . " = " . $column . " + 1";

            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([
                'fecha' => $fecha,
                'usuario_id' => $userId,
            ]);

            if (!$ok) {
                $pdo->rollBack();
                return false;
            }

            self::insertMetricEvent($pdo, $fecha, $userId, $ticketId, $mode, $comment);

            $pdo->commit();
            return true;
        } catch (Throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    private static function insertMetricEvent(PDO $pdo, string $fecha, int $userId, ?int $ticketId, string $mode, ?string $comment): void
    {
        $exists = $pdo->query("SHOW TABLES LIKE 'metricas_eventos'")->fetchColumn();
        if (!$exists) {
            return;
        }

        $stmt = $pdo->prepare('INSERT INTO metricas_eventos (fecha, usuario_id, ticket_id, modo, comentario) VALUES (:fecha, :usuario_id, :ticket_id, :modo, :comentario)');
        $stmt->execute([
            'fecha' => $fecha,
            'usuario_id' => $userId,
            'ticket_id' => $ticketId,
            'modo' => $mode,
            'comentario' => $comment,
        ]);
    }
}
