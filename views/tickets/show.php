<?php $user = Auth::user(); ?>

<style>
  .ticket-grid { display: grid; grid-template-columns: 1.7fr 1fr; gap: 1rem; }
  .k-label { font-size: .76rem; color: #5f7385; text-transform: uppercase; letter-spacing: .04em; }
  .k-value { font-weight: 700; color: #102131; }
  .comment-timeline { position: relative; padding-left: 1.1rem; }
  .comment-timeline:before { content: ''; position: absolute; left: .32rem; top: .2rem; bottom: .2rem; width: 2px; background: #d8e2ea; }
  .timeline-item { position: relative; margin-bottom: .75rem; }
  .timeline-item:before { content: ''; position: absolute; left: -.95rem; top: .75rem; width: .55rem; height: .55rem; border-radius: 999px; background: #0a6c8f; }
  .card-soft { background: #f8fbfd; border: 1px solid #dbe4ec; border-radius: .9rem; }
  .sticky-actions { position: sticky; top: 1rem; }
  @media (max-width: 991.98px) { .ticket-grid { grid-template-columns: 1fr; } .sticky-actions { position: static; } }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-0">Ticket <?= View::e($ticket['ticket_number'] ?: ('ID-' . $ticket['id'])) ?></h1>
    <small class="text-muted">Informacion base y seguimiento operativo</small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= View::e(Url::path('tickets')) ?>" class="btn btn-outline-secondary">Volver</a>
    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#editTicketModal">Editar ticket</button>
  </div>
</div>

<div class="ticket-grid">
  <div>
    <div class="card mb-3">
      <div class="card-header">Informacion basica</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4"><div class="k-label">Estado</div><div class="k-value"><?= View::e($ticket['estado']) ?></div></div>
          <div class="col-md-4"><div class="k-label">Prioridad</div><div class="k-value"><?= View::e($ticket['prioridad_nombre'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Vence</div><div class="k-value"><?= View::e(DateFormat::dueEs($ticket['fecha_vencimiento'] ?? null)) ?></div></div>
          <div class="col-md-6"><div class="k-label">Problema</div><div class="k-value"><?= View::e($ticket['problem_name'] ?? '-') ?></div></div>
          <div class="col-md-6"><div class="k-label">Estado info</div><div class="k-value"><?= View::e($ticket['estado_info'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Asignado</div><div><?= View::e($ticket['asignado_nombre'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Correo</div><div><?= View::e($ticket['email'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Telefono</div><div><?= View::e($ticket['phone'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Pais</div><div><?= View::e($ticket['pais'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Creado</div><div><?= View::e($ticket['created_at'] ?? '-') ?></div></div>
          <div class="col-md-4"><div class="k-label">Tags</div><div><?php foreach ($tags as $tg): ?><span class="badge text-bg-secondary me-1"><?= View::e($tg['nombre']) ?></span><?php endforeach; ?><?php if (count($tags) === 0): ?>-<?php endif; ?></div></div>
          <div class="col-12"><div class="k-label">Descripcion</div><div class="card-soft p-2"><?= View::e($ticket['description'] ?? '-') ?></div></div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Comentarios (historial)</div>
      <div class="card-body">
        <form method="POST" action="<?= View::e(Url::path('tickets/comment')) ?>" class="mb-3">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <textarea class="form-control mb-2" name="comentario" rows="3" placeholder="Escribe un nuevo comentario" required></textarea>
          <div class="d-flex justify-content-between align-items-center">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno" checked>
              <label class="form-check-label" for="es_interno">Comentario interno</label>
            </div>
            <button class="btn btn-primary">Agregar comentario</button>
          </div>
        </form>

        <div class="comment-timeline">
          <?php if (count($comments) === 0): ?>
            <div class="text-muted">Sin comentarios aun.</div>
          <?php endif; ?>
          <?php foreach ($comments as $c): ?>
            <div class="timeline-item">
              <div class="card-soft p-2">
                <div class="d-flex justify-content-between">
                  <strong><?= View::e($c['usuario_nombre']) ?></strong>
                  <small class="text-muted"><?= View::e($c['created_at']) ?></small>
                </div>
                <div class="mt-1"><?= View::e($c['comentario']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="sticky-actions">
    <div class="card mb-3">
      <div class="card-header">Actualizar estado</div>
      <div class="card-body">
        <form method="POST" action="<?= View::e(Url::path('tickets/update-status')) ?>" class="row g-2">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <div class="col-12"><select class="form-select" name="estado"><?php foreach (['no_tomado','respondido','preguntar','cerrado'] as $estado): ?><option value="<?= $estado ?>" <?= $ticket['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><select class="form-select" name="estado_info"><?php foreach ($statusInfoOptions as $option): ?><option value="<?= View::e($option['nombre']) ?>" <?= (($ticket['estado_info'] ?? '') === $option['nombre']) ? 'selected' : '' ?>><?= View::e($option['nombre']) ?></option><?php endforeach; ?></select></div>
          <div class="col-12"><button class="btn btn-outline-dark w-100">Guardar estado</button></div>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Tags</div>
      <div class="card-body">
        <form method="POST" action="<?= View::e(Url::path('tickets/tags')) ?>">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <select class="form-select mb-2" name="tag_ids[]" multiple><?php foreach ($tagsAll as $tg): ?><option value="<?= (int)$tg['id'] ?>" <?= in_array((int)$tg['id'], $tagsSelected, true) ? 'selected' : '' ?>><?= View::e($tg['nombre']) ?></option><?php endforeach; ?></select>
          <button class="btn btn-outline-dark w-100">Guardar tags</button>
        </form>
      </div>
    </div>

    <?php if ($user['rol'] === 'admin'): ?>
      <div class="card mb-3">
        <div class="card-header">Asignacion</div>
        <div class="card-body">
          <form method="POST" action="<?= View::e(Url::path('tickets/assign')) ?>" class="row g-2">
            <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
            <div class="col-12"><select class="form-select" name="asignado_a" required><?php foreach ($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= ((int)$ticket['asignado_a'] === (int)$u['id']) ? 'selected' : '' ?>><?= View::e($u['nombre']) ?> (<?= View::e($u['rol']) ?>)</option><?php endforeach; ?></select></div>
            <div class="col-12"><button class="btn btn-dark w-100">Asignar</button></div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header text-danger">Zona administrativa</div>
        <div class="card-body">
          <form method="POST" action="<?= View::e(Url::path('tickets/delete')) ?>" onsubmit="return confirm('Seguro que deseas eliminar este ticket?');">
            <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
            <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
            <button class="btn btn-danger w-100">Eliminar ticket</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="editTicketModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar informacion del ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= View::e(Url::path('tickets/update-fields')) ?>">
        <div class="modal-body">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <div class="row g-2">
            <div class="col-md-6"><label class="form-label">Ticket number</label><input class="form-control" name="ticket_number" value="<?= View::e($ticket['ticket_number'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Pais</label><input class="form-control" name="pais" value="<?= View::e($ticket['pais'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Telefono</label><input class="form-control" name="phone" value="<?= View::e($ticket['phone'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= View::e($ticket['email'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Problema</label><input class="form-control" name="problem_name" value="<?= View::e($ticket['problem_name'] ?? '') ?>"></div>
            <div class="col-md-6"><label class="form-label">Prioridad</label><select class="form-select" name="prioridad_id"><?php foreach ($priorities as $p): ?><option value="<?= (int)$p['id'] ?>" <?= ((int)$ticket['prioridad_id'] === (int)$p['id']) ? 'selected' : '' ?>><?= View::e($p['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">Estado info</label><select class="form-select" name="estado_info"><?php foreach ($statusInfoOptions as $option): ?><option value="<?= View::e($option['nombre']) ?>" <?= (($ticket['estado_info'] ?? '') === $option['nombre']) ? 'selected' : '' ?>><?= View::e($option['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">Descripcion</label><textarea class="form-control" rows="3" name="description"><?= View::e($ticket['description'] ?? '') ?></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-dark">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>
