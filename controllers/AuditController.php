<?php
declare(strict_types=1);

class AuditController
{
    public function index(): void
    {
        $pdo = Database::connection();
        $sql = 'SELECT a.*, u.nombre AS usuario_nombre FROM auditoria_general a INNER JOIN usuarios u ON u.id = a.usuario_id ORDER BY a.created_at DESC LIMIT 500';
        $rows = $pdo->query($sql)->fetchAll();
        View::render('audit/index', [
            'title' => 'Auditoria',
            'rows' => $rows,
        ]);
    }
}
