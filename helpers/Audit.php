<?php
declare(strict_types=1);

class Audit
{
    public static function log(?int $userId, string $action, ?string $table = null, ?int $recordId = null): void
    {
        if ($userId === null) {
            return;
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO auditoria_general (usuario_id, accion, tabla_afectada, registro_id, ip_address, user_agent) VALUES (:usuario_id, :accion, :tabla_afectada, :registro_id, :ip_address, :user_agent)');
        $stmt->execute([
            'usuario_id' => $userId,
            'accion' => $action,
            'tabla_afectada' => $table,
            'registro_id' => $recordId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
