<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/User.php';

class ReportController
{
    public function pending(): void
    {
        $user = Auth::user();
        View::render('reports/pending', [
            'title' => 'Reporte Pendientes',
            'rows' => Report::pendingTickets($user),
            'rowsByDay' => Report::pendingTicketsByDay($user),
        ]);
    }

    public function metrics(): void
    {
        $user = Auth::user();
        $date = $_GET['fecha'] ?? null;
        $from = $_GET['desde'] ?? null;
        $to = $_GET['hasta'] ?? null;
        $uid = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;
        View::render('reports/metrics', [
            'title' => 'Reporte Metricas',
            'rows' => Report::metrics($user, $date, $uid, $from, $to),
            'users' => User::assignableUsers(),
        ]);
    }

    public function sla(): void
    {
        $rows = Report::sla();
        $now = time();
        foreach ($rows as &$r) {
            $ts = $r['fecha_vencimiento'] ? strtotime((string)$r['fecha_vencimiento']) : null;
            $r['sla_estado'] = 'en_tiempo';
            if ($ts === null) {
                $r['sla_estado'] = 'sin_sla';
            } elseif ($ts < $now) {
                $r['sla_estado'] = 'vencido';
            } elseif ($ts < $now + (6 * 3600)) {
                $r['sla_estado'] = 'proximo';
            }
        }
        View::render('reports/sla', [
            'title' => 'Reporte SLA',
            'rows' => $rows,
        ]);
    }
}
