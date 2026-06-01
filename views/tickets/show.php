<?php $user = Auth::user(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Ticket <?= View::e($ticket['ticket_number'] ?: ('ID-' . $ticket['id'])) ?></h1>
  <a href="index.php?r=tickets" class="btn btn-outline-secondary">Volver</a>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card mb-3"><div class="card-body">
      <p><strong>Problema:</strong> <?= View::e($ticket['problem_name'] ?? '-') ?></p>
      <p><strong>Pais:</strong> <?= View::e($ticket['pais'] ?? '-') ?></p>
      <p><strong>Email:</strong> <?= View::e($ticket['email'] ?? '-') ?></p>
      <p><strong>Telefono:</strong> <?= View::e($ticket['phone'] ?? '-') ?></p>
      <p><strong>Estado:</strong> <?= View::e($ticket['estado']) ?></p>
      <p><strong>Estado info:</strong> <?= View::e($ticket['estado_info'] ?? '-') ?></p>
      <p><strong>Prioridad:</strong> <?= View::e($ticket['prioridad_nombre'] ?? '-') ?></p>
      <p><strong>Vencimiento:</strong> <?= View::e(DateFormat::dueEs($ticket['fecha_vencimiento'] ?? null)) ?></p>
      <p><strong>Asignado:</strong> <?= View::e($ticket['asignado_nombre'] ?? '-') ?></p>
      <p><strong>Descripcion:</strong> <?= View::e($ticket['description'] ?? '-') ?></p>
      <p><strong>Tags:</strong>
        <?php if (count($tags) === 0): ?>-
        <?php else: ?>
          <?php foreach ($tags as $tg): ?>
            <span class="badge text-bg-secondary"><?= View::e($tg['nombre']) ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
      </p>
    </div></div>

    <div class="card mb-3">
      <div class="card-header">Actualizar tags</div>
      <div class="card-body">
        <form method="POST" action="index.php?r=tickets/tags">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <select class="form-select mb-2" name="tag_ids[]" multiple>
            <?php foreach ($tagsAll as $tg): ?>
              <option value="<?= (int)$tg['id'] ?>" <?= in_array((int)$tg['id'], $tagsSelected, true) ? 'selected' : '' ?>><?= View::e($tg['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-dark">Guardar tags</button>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Cambiar estado</div>
      <div class="card-body">
        <form method="POST" action="index.php?r=tickets/update-status" class="row g-2">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <div class="col-md-4">
            <select class="form-select" name="estado">
              <?php foreach (['no_tomado','respondido','preguntar','cerrado'] as $estado): ?>
                <option value="<?= $estado ?>" <?= $ticket['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6"><input class="form-control" name="estado_info" value="<?= View::e($ticket['estado_info'] ?? '') ?>" placeholder="Estado info"></div>
          <div class="col-md-2"><button class="btn btn-primary w-100">Actualizar</button></div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Comentarios</div>
      <div class="card-body">
        <form method="POST" action="index.php?r=tickets/comment" class="mb-3">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <textarea class="form-control mb-2" name="comentario" rows="2" placeholder="Agregar comentario" required></textarea>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" checked>
            <label class="form-check-label" for="es_interno">Comentario interno</label>
          </div>
          <button class="btn btn-outline-primary">Agregar</button>
        </form>
        <?php foreach ($comments as $c): ?>
          <div class="border rounded p-2 mb-2">
            <strong><?= View::e($c['usuario_nombre']) ?></strong>
            <small class="text-muted"><?= View::e($c['created_at']) ?></small>
            <div><?= View::e($c['comentario']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if ($user['rol'] === 'admin'): ?>
    <div class="card mb-3">
      <div class="card-header">Asignar ticket</div>
      <div class="card-body">
        <form method="POST" action="index.php?r=tickets/assign" class="row g-2">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <div class="col-8">
            <select class="form-select" name="asignado_a" required>
              <?php foreach ($users as $u): ?>
                <option value="<?= (int)$u['id'] ?>" <?= ((int)$ticket['asignado_a'] === (int)$u['id']) ? 'selected' : '' ?>><?= View::e($u['nombre']) ?> (<?= View::e($u['rol']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-4"><button class="btn btn-dark w-100">Asignar</button></div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">Historial de cambios</div>
      <div class="card-body">
        <?php foreach ($history as $h): ?>
          <div class="border rounded p-2 mb-2">
            <small class="text-muted"><?= View::e($h['created_at']) ?></small>
            <div><strong><?= View::e($h['campo_modificado']) ?></strong></div>
            <div><?= View::e($h['valor_anterior'] ?? '-') ?> -> <?= View::e($h['valor_nuevo'] ?? '-') ?></div>
            <small><?= View::e($h['usuario_nombre'] ?? 'sistema') ?></small>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($user['rol'] === 'admin'): ?>
      <div class="card mt-3">
        <div class="card-body">
          <form method="POST" action="index.php?r=tickets/delete" onsubmit="return confirm('Seguro que deseas eliminar este ticket?');">
            <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Soft delete ticket</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
