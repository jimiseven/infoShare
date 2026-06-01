<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';

class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        $tickets = Ticket::listByRole($user);

        $today = date('Y-m-d');
        $stats = [
            'abiertos' => 0,
            'preguntar' => 0,
            'cerrados_hoy' => 0,
            'vencidos' => 0,
        ];

        foreach ($tickets as $t) {
            if ($t['estado'] !== 'cerrado') {
                $stats['abiertos']++;
            }
            if ($t['estado'] === 'preguntar') {
                $stats['preguntar']++;
            }
            if ($t['estado'] === 'cerrado' && str_starts_with((string) $t['updated_at'], $today)) {
                $stats['cerrados_hoy']++;
            }
            if (!empty($t['fecha_vencimiento']) && $t['estado'] !== 'cerrado' && strtotime((string) $t['fecha_vencimiento']) < time()) {
                $stats['vencidos']++;
            }
        }

        View::render('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'tickets' => array_slice($tickets, 0, 8),
        ]);
    }
}
