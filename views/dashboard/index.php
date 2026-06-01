<h1 class="h3 mb-3">Dashboard</h1>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Abiertos</div><div class="h3"><?= (int)$stats['abiertos'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Preguntar</div><div class="h3"><?= (int)$stats['preguntar'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Cerrados hoy</div><div class="h3"><?= (int)$stats['cerrados_hoy'] ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">SLA vencido</div><div class="h3"><?= (int)$stats['vencidos'] ?></div></div></div></div>
</div>

<div class="card">
  <div class="card-header">Ultimos tickets</div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>#</th><th>Problema</th><th>Estado</th><th>Prioridad</th><th>Asignado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= View::e($t['ticket_number'] ?: ('ID-' . $t['id'])) ?></td>
            <td><?= View::e($t['problem_name'] ?: 'Sin nombre') ?></td>
            <td><?= View::e($t['estado']) ?></td>
            <td><?= View::e($t['prioridad_nombre'] ?? '-') ?></td>
            <td><?= View::e($t['asignado_nombre'] ?? '-') ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="index.php?r=tickets/show&id=<?= (int)$t['id'] ?>">Ver</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
