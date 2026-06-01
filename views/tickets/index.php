<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Tickets</h1>
  <a class="btn btn-primary" href="index.php?r=tickets/create">Crear ticket</a>
</div>

<form method="GET" action="index.php" class="card card-body mb-3">
  <input type="hidden" name="r" value="tickets">
  <div class="row g-2">
    <div class="col-md-4">
      <select name="tag_id" class="form-select">
        <option value="">Filtrar por tag</option>
        <?php foreach ($tags as $tag): ?>
          <option value="<?= (int)$tag['id'] ?>" <?= ((int)($selectedTagId ?? 0) === (int)$tag['id']) ? 'selected' : '' ?>><?= View::e($tag['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-dark w-100">Aplicar</button></div>
  </div>
</form>

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
        <?php
          $isOldOpen = false;
          if (($t['estado'] ?? '') !== 'cerrado' && !empty($t['created_at'])) {
              $isOldOpen = (time() - strtotime((string)$t['created_at'])) > (5 * 24 * 60 * 60);
          }
        ?>
        <tr class="<?= $isOldOpen ? 'table-danger' : '' ?>">
          <td><?= View::e($t['ticket_number'] ?: ('ID-' . $t['id'])) ?></td>
          <td><?= View::e($t['pais'] ?? '-') ?></td>
          <td><?= View::e($t['estado']) ?></td>
          <td><?= View::e($t['prioridad_nombre'] ?? '-') ?></td>
          <td><?= View::e($t['asignado_nombre'] ?? '-') ?></td>
          <td><?= View::e(DateFormat::dueEs($t['fecha_vencimiento'] ?? null)) ?></td>
          <td><a class="btn btn-sm btn-outline-primary" href="index.php?r=tickets/show&id=<?= (int)$t['id'] ?>">Detalle</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
