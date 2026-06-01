<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Metric.php';

class MetricController
{
    public function storeDaily(): void
    {
        $user = Auth::user();
        Metric::upsertDaily($user, $_POST);
        Audit::log((int)$user['id'], 'REGISTRO_METRICA_DIARIA', 'metricas_diarias', null);
        Flash::set('success', 'Metricas guardadas.');
        header('Location: index.php?r=dashboard');
        exit;
    }
}
