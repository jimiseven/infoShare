<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Report.php';

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

        $metricRows = Report::metrics($user, date('Y-m-d'), null, null, null);
        $metricTotal = [
            'inbound_calls' => 0,
            'outbound_calls' => 0,
            'failed_calls' => 0,
            'chats' => 0,
            'emails' => 0,
            'total_interacciones' => 0,
        ];
        foreach ($metricRows as $m) {
            $metricTotal['inbound_calls'] += (int)$m['inbound_calls'];
            $metricTotal['outbound_calls'] += (int)$m['outbound_calls'];
            $metricTotal['failed_calls'] += (int)$m['failed_calls'];
            $metricTotal['chats'] += (int)$m['chats'];
            $metricTotal['emails'] += (int)$m['emails'];
            $metricTotal['total_interacciones'] += (int)$m['total_interacciones'];
        }

        View::render('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'tickets' => array_slice($tickets, 0, 8),
            'metricTotal' => $metricTotal,
        ]);
    }
}
