<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Metric.php';

class MetricController
{
    public function storeDaily(): void
    {
        $user = Auth::user();
        $fields = ['inbound_calls', 'outbound_calls', 'failed_calls', 'chats', 'emails'];
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '' && !Validator::positiveInt($_POST[$field], 0)) {
                Flash::set('danger', 'Metricas invalidas, solo numeros enteros.');
                Url::redirect('dashboard');
            }
        }
        Metric::upsertDaily($user, $_POST);
        Audit::log((int)$user['id'], 'REGISTRO_METRICA_DIARIA', 'metricas_diarias', null);
        Flash::set('success', 'Metricas guardadas.');
        Url::redirect('dashboard');
    }
}
