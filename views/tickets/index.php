<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Tickets</h1>
  <a class="btn btn-primary" href="index.php?r=tickets/create">Crear ticket</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
      <tr>
        <th>Ticket</th><th>Pais</th><th>Estado</th><th>Prioridad</th><th>Asignado</th><th>Vence</th><th></th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($tickets as $t): ?>
        <tr>
          <td><?= View::e($t['ticket_number'] ?: ('ID-' . $t['id'])) ?></td>
          <td><?= View::e($t['pais'] ?? '-') ?></td>
          <td><?= View::e($t['estado']) ?></td>
          <td><?= View::e($t['prioridad_nombre'] ?? '-') ?></td>
          <td><?= View::e($t['asignado_nombre'] ?? '-') ?></td>
          <td><?= View::e($t['fecha_vencimiento'] ?? '-') ?></td>
          <td><a class="btn btn-sm btn-outline-primary" href="index.php?r=tickets/show&id=<?= (int)$t['id'] ?>">Detalle</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
