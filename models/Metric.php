<?php
declare(strict_types=1);

class Metric
{
    public static function upsertDaily(array $user, array $data): void
    {
        $pdo = Database::connection();
        $fecha = $data['fecha'] ?: date('Y-m-d');
        $sql = 'INSERT INTO metricas_diarias (fecha, usuario_id, inbound_calls, outbound_calls, failed_calls, chats, emails)
                VALUES (:fecha, :usuario_id, :inbound_calls, :outbound_calls, :failed_calls, :chats, :emails)
                ON DUPLICATE KEY UPDATE
                inbound_calls = VALUES(inbound_calls), outbound_calls = VALUES(outbound_calls),
                failed_calls = VALUES(failed_calls), chats = VALUES(chats), emails = VALUES(emails)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'fecha' => $fecha,
            'usuario_id' => $user['id'],
            'inbound_calls' => (int)($data['inbound_calls'] ?? 0),
            'outbound_calls' => (int)($data['outbound_calls'] ?? 0),
            'failed_calls' => (int)($data['failed_calls'] ?? 0),
            'chats' => (int)($data['chats'] ?? 0),
            'emails' => (int)($data['emails'] ?? 0),
        ]);
    }
}
