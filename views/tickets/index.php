<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-0">Tickets</h1>
    <small class="text-muted">Gestion diaria y seguimiento SLA</small>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#filtersModal">Filtros</button>
    <a class="btn btn-primary" href="<?= View::e(Url::path('tickets/create')) ?>">Crear ticket</a>
  </div>
</div>

<form method="GET" action="index.php" class="card card-body mb-3">
  <input type="hidden" name="r" value="tickets">
  <div class="row g-2">
    <div class="col-md-3">
      <input type="text" name="q" value="<?= View::e($filters['q'] ?? '') ?>" class="form-control" placeholder="Buscar ticket, numero o correo">
    </div>
    <div class="col-md-2">
      <select name="estado" class="form-select">
        <option value="">Estado</option>
        <?php foreach (['no_tomado','respondido','preguntar','cerrado'] as $estado): ?>
          <option value="<?= $estado ?>" <?= (($filters['estado'] ?? '') === $estado) ? 'selected' : '' ?>><?= $estado ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="prioridad_id" class="form-select">
        <option value="">Prioridad</option>
        <?php foreach ($priorities as $p): ?>
          <option value="<?= (int)$p['id'] ?>" <?= ((int)($filters['prioridad_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>><?= View::e($p['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="tag_id" class="form-select">
        <option value="">Filtrar por tag</option>
        <?php foreach ($tags as $tag): ?>
          <option value="<?= (int)$tag['id'] ?>" <?= ((int)($filters['tag_id'] ?? 0) === (int)$tag['id']) ? 'selected' : '' ?>><?= View::e($tag['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if ((Auth::user()['rol'] ?? '') !== 'usuario_normal'): ?>
      <div class="col-md-2">
        <select name="asignado_a" class="form-select">
          <option value="">Asignado a</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= ((int)($filters['asignado_a'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>><?= View::e($u['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>
    <div class="col-md-1"><button class="btn btn-outline-dark w-100">Aplicar</button></div>
    <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="<?= View::e(Url::path('tickets')) ?>">Limpiar</a></div>
  </div>
</form>

<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filtros avanzados</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="GET" action="index.php">
        <input type="hidden" name="r" value="tickets">
        <div class="modal-body d-grid gap-2">
          <input type="text" name="q" value="<?= View::e($filters['q'] ?? '') ?>" class="form-control" placeholder="Buscar...">
          <select name="estado" class="form-select">
            <option value="">Estado</option>
            <?php foreach (['no_tomado','respondido','preguntar','cerrado'] as $estado): ?>
              <option value="<?= $estado ?>" <?= (($filters['estado'] ?? '') === $estado) ? 'selected' : '' ?>><?= $estado ?></option>
            <?php endforeach; ?>
          </select>
          <select name="prioridad_id" class="form-select">
            <option value="">Prioridad</option>
            <?php foreach ($priorities as $p): ?>
              <option value="<?= (int)$p['id'] ?>" <?= ((int)($filters['prioridad_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>><?= View::e($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="tag_id" class="form-select">
            <option value="">Tag</option>
            <?php foreach ($tags as $tag): ?>
              <option value="<?= (int)$tag['id'] ?>" <?= ((int)($filters['tag_id'] ?? 0) === (int)$tag['id']) ? 'selected' : '' ?>><?= View::e($tag['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-dark">Aplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
      <tr>
        <th>Ticket</th><th>Pais</th><th>Estado</th><th>Prioridad</th><th>Asignado</th><th>Creado</th><th>Vence</th><th></th>
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
          <td><?= View::e(!empty($t['created_at']) ? date('Y-m-d H:i', strtotime((string)$t['created_at'])) : '-') ?></td>
          <td><?= View::e(DateFormat::dueEs($t['fecha_vencimiento'] ?? null)) ?></td>
          <td><a class="btn btn-sm btn-outline-primary" href="<?= View::e(Url::path('tickets/show', ['id' => (int)$t['id']])) ?>">Detalle</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $totalPages = max(1, (int)ceil(((int)$total) / ((int)$perPage))); ?>
<div class="d-flex justify-content-between align-items-center mt-3">
  <small>Mostrando pagina <?= (int)$page ?> de <?= (int)$totalPages ?> (<?= (int)$total ?> tickets)</small>
  <div class="btn-group">
    <?php
      $queryBase = $filters;
      if ($page > 1):
        $queryPrev = array_merge($queryBase, ['page' => $page - 1]);
    ?>
      <a class="btn btn-sm btn-outline-secondary" href="<?= View::e(Url::path('tickets', $queryPrev)) ?>">Anterior</a>
    <?php endif; ?>
    <?php if ($page < $totalPages):
      $queryNext = array_merge($queryBase, ['page' => $page + 1]);
    ?>
      <a class="btn btn-sm btn-outline-secondary" href="<?= View::e(Url::path('tickets', $queryNext)) ?>">Siguiente</a>
    <?php endif; ?>
  </div>
</div>
