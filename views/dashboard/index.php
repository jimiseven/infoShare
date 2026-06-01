<h1 class="h3 mb-3">Dashboard</h1>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Abiertos</div><div class="h3 mb-0"><?= (int)$stats['abiertos'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Preguntar</div><div class="h3 mb-0"><?= (int)$stats['preguntar'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Cerrados hoy</div><div class="h3 mb-0"><?= (int)$stats['cerrados_hoy'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">SLA vencido</div><div class="h3 mb-0"><?= (int)$stats['vencidos'] ?></div></div></div></div>
</div>

<div class="card mb-4">
  <div class="card-header">Metricas del dia</div>
  <div class="card-body">
    <div class="row g-2 mb-3">
      <div class="col">Inbound: <strong><?= (int)$metricTotal['inbound_calls'] ?></strong></div>
      <div class="col">Outbound: <strong><?= (int)$metricTotal['outbound_calls'] ?></strong></div>
      <div class="col">Failed: <strong><?= (int)$metricTotal['failed_calls'] ?></strong></div>
      <div class="col">Chats: <strong><?= (int)$metricTotal['chats'] ?></strong></div>
      <div class="col">Emails: <strong><?= (int)$metricTotal['emails'] ?></strong></div>
      <div class="col">Total: <strong><?= (int)$metricTotal['total_interacciones'] ?></strong></div>
    </div>
    <form method="POST" action="<?= View::e(Url::path('metrics/daily')) ?>" class="row g-2">
      <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
      <div class="col-md-2"><input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>"></div>
      <div class="col-md-2"><input type="number" min="0" class="form-control" name="inbound_calls" placeholder="Inbound"></div>
      <div class="col-md-2"><input type="number" min="0" class="form-control" name="outbound_calls" placeholder="Outbound"></div>
      <div class="col-md-2"><input type="number" min="0" class="form-control" name="failed_calls" placeholder="Failed"></div>
      <div class="col-md-2"><input type="number" min="0" class="form-control" name="chats" placeholder="Chats"></div>
      <div class="col-md-1"><input type="number" min="0" class="form-control" name="emails" placeholder="Email"></div>
      <div class="col-md-1"><button class="btn btn-dark w-100">Guardar</button></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">Ultimos tickets</div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>#</th><th>Problema</th><th>Estado</th><th>Prioridad</th><th>Asignado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <?php
            $isOldOpen = false;
            if (($t['estado'] ?? '') !== 'cerrado' && !empty($t['created_at'])) {
                $isOldOpen = (time() - strtotime((string)$t['created_at'])) > (5 * 24 * 60 * 60);
            }
          ?>
          <tr class="<?= $isOldOpen ? 'table-danger' : '' ?>">
            <td><?= View::e($t['ticket_number'] ?: ('ID-' . $t['id'])) ?></td>
            <td><?= View::e($t['problem_name'] ?: 'Sin nombre') ?></td>
            <td><?= View::e($t['estado']) ?></td>
            <td><?= View::e($t['prioridad_nombre'] ?? '-') ?></td>
            <td><?= View::e($t['asignado_nombre'] ?? '-') ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="<?= View::e(Url::path('tickets/show', ['id' => (int)$t['id']])) ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
