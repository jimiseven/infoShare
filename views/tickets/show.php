<?php $user = Auth::user(); ?>

<style>
  .ticket-grid { display: grid; grid-template-columns: 1fr 1.25fr; gap: .75rem; }
  .k-label { font-size: .76rem; color: #5f7385; text-transform: uppercase; letter-spacing: .04em; }
  .k-value { font-weight: 700; color: #102131; }
  .comment-timeline { position: relative; padding-left: 1.1rem; }
  .comment-timeline:before { content: ''; position: absolute; left: .32rem; top: .2rem; bottom: .2rem; width: 2px; background: #d8e2ea; }
  .timeline-item { position: relative; margin-bottom: .55rem; }
  .timeline-item:before { content: ''; position: absolute; left: -.95rem; top: .75rem; width: .55rem; height: .55rem; border-radius: 999px; background: #0a6c8f; }
  .card-soft { background: #f8fbfd; border: 1px solid #dbe4ec; border-radius: .9rem; }
  .sticky-actions { position: sticky; top: .75rem; }
  .card .card-header { padding: .55rem .75rem; }
  .card .card-body { padding: .7rem; }
  .compact-select { max-height: 150px; }
  .comments-scroll { min-height: 56vh; max-height: 68vh; overflow: auto; }
  .mini-card .card-body { padding: .55rem; }
  .mini-card .form-select { font-size: .88rem; }
  .mini-card .btn { padding-top: .28rem; padding-bottom: .28rem; }
  .ticket-grid .form-control, .ticket-grid .form-select { padding-top: .32rem; padding-bottom: .32rem; }
  .edit-ticket-modal .modal-dialog { max-width: 920px; }
  .edit-ticket-modal .section-title { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #5f7385; margin-bottom: .45rem; }
  .edit-ticket-modal .modal-body { padding: .85rem; }
  .edit-ticket-modal .modal-footer { padding: .7rem .85rem; }
  .edit-ticket-modal .tags-select { min-height: 170px; }
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
    <?php if ($user['rol'] === 'admin'): ?>
      <form method="POST" action="<?= View::e(Url::path('tickets/delete')) ?>" onsubmit="return confirm('Seguro que deseas eliminar este ticket?');" class="d-inline">
        <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
        <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
        <button class="btn btn-danger" type="submit">Eliminar ticket</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="ticket-grid">
  <div>
    <div class="card mb-2">
      <div class="card-header">Informacion basica</div>
      <div class="card-body">
        <div class="row g-2">
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
    <div class="row g-2 mb-2">
      <div class="<?= $user['rol'] === 'admin' ? 'col-md-6' : 'col-md-12' ?>">
        <div class="card mini-card h-100">
          <div class="card-header">Actualizar estado</div>
          <div class="card-body">
            <form method="POST" action="<?= View::e(Url::path('tickets/update-status')) ?>" class="row g-2">
              <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
              <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
              <div class="col-12"><select class="form-select" name="estado"><?php foreach (['no_tomado','respondido','preguntar','cerrado'] as $estado): ?><option value="<?= $estado ?>" <?= $ticket['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option><?php endforeach; ?></select></div>
              <div class="col-12"><select class="form-select" name="estado_info"><?php foreach ($statusInfoOptions as $option): ?><option value="<?= View::e($option['nombre']) ?>" <?= (($ticket['estado_info'] ?? '') === $option['nombre']) ? 'selected' : '' ?>><?= View::e($option['nombre']) ?></option><?php endforeach; ?></select></div>
              <div class="col-12"><button class="btn btn-outline-dark w-100">Guardar</button></div>
            </form>
          </div>
        </div>
      </div>

      <?php if ($user['rol'] === 'admin'): ?>
        <div class="col-md-6">
          <div class="card mini-card h-100">
            <div class="card-header">Asignacion</div>
            <div class="card-body">
              <form method="POST" action="<?= View::e(Url::path('tickets/assign')) ?>" class="row g-2">
                <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
                <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                <div class="col-12"><select class="form-select" name="asignado_a" required><?php foreach ($users as $u): ?><option value="<?= (int)$u['id'] ?>" <?= ((int)$ticket['asignado_a'] === (int)$u['id']) ? 'selected' : '' ?>><?= View::e($u['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><button class="btn btn-dark w-100">Asignar</button></div>
              </form>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <div>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Comentarios (historial)</span>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCommentModal">Agregar comentario</button>
      </div>
      <div class="card-body">
        <div class="comment-timeline comments-scroll">
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
                <?php if (!empty($c['metric_modes'])): ?>
                  <div class="mt-1"><span class="badge text-bg-info">Metrica: <?= View::e(str_replace(',', ', ', (string)$c['metric_modes'])) ?></span></div>
                <?php endif; ?>
                <div class="mt-1"><?= View::e($c['comentario']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="addCommentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo comentario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?= View::e(Url::path('tickets/comment')) ?>">
        <div class="modal-body">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
          <textarea class="form-control mb-2" name="comentario" rows="4" placeholder="Escribe un nuevo comentario" required></textarea>
          <div class="mb-2">
            <label class="form-label">Registrar metrica con este comentario</label>
            <select class="form-select form-select-sm" name="metric_mode">
              <option value="">No registrar</option>
              <option value="inbound_calls">Inbound calls</option>
              <option value="outbound_calls">Outbound calls</option>
              <option value="chats">Online chat</option>
              <option value="emails">Emails</option>
            </select>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="es_interno" id="es_interno_modal" checked>
            <label class="form-check-label" for="es_interno_modal">Comentario interno</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" type="submit">Guardar comentario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade edit-ticket-modal" id="editTicketModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar informacion del ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-8">
            <form method="POST" action="<?= View::e(Url::path('tickets/update-fields')) ?>">
              <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
              <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
              <div class="section-title">Datos del ticket</div>
              <div class="row g-2">
                <div class="col-md-6"><label class="form-label">Ticket number</label><input class="form-control" name="ticket_number" value="<?= View::e($ticket['ticket_number'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Pais</label><input class="form-control" name="pais" value="<?= View::e($ticket['pais'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Telefono</label><input class="form-control" name="phone" inputmode="tel" placeholder="Ej: 00319032272423" value="<?= View::e($ticket['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= View::e($ticket['email'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Problema</label><input class="form-control" name="problem_name" value="<?= View::e($ticket['problem_name'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Prioridad</label><select class="form-select" name="prioridad_id"><?php foreach ($priorities as $p): ?><option value="<?= (int)$p['id'] ?>" <?= ((int)$ticket['prioridad_id'] === (int)$p['id']) ? 'selected' : '' ?>><?= View::e($p['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><label class="form-label">Estado info</label><select class="form-select" name="estado_info"><?php foreach ($statusInfoOptions as $option): ?><option value="<?= View::e($option['nombre']) ?>" <?= (($ticket['estado_info'] ?? '') === $option['nombre']) ? 'selected' : '' ?>><?= View::e($option['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><label class="form-label">Descripcion</label><textarea class="form-control" rows="5" name="description"><?= View::e($ticket['description'] ?? '') ?></textarea></div>
              </div>
              <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-dark">Guardar cambios</button>
              </div>
            </form>
          </div>
          <div class="col-lg-4">
            <form method="POST" action="<?= View::e(Url::path('tickets/tags')) ?>">
              <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
              <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
              <div class="section-title">Tags</div>
              <select class="form-select tags-select" name="tag_ids[]" multiple><?php foreach ($tagsAll as $tg): ?><option value="<?= (int)$tg['id'] ?>" <?= in_array((int)$tg['id'], $tagsSelected, true) ? 'selected' : '' ?>><?= View::e($tg['nombre']) ?></option><?php endforeach; ?></select>
              <div class="d-grid mt-2">
                <button type="submit" class="btn btn-outline-dark">Guardar tags</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
