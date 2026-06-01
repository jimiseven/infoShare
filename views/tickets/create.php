<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-0">Crear ticket</h1>
    <small class="text-muted">Carga rapida con los datos minimos disponibles</small>
  </div>
  <a class="btn btn-outline-secondary" href="<?= View::e(Url::path('tickets')) ?>">Volver a tickets</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="<?= View::e(Url::path('tickets/create')) ?>" class="row g-3">
      <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

      <div class="col-md-3">
        <label class="form-label">Ticket number</label>
        <input type="text" class="form-control" name="ticket_number">
      </div>
      <div class="col-md-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email">
      </div>
      <div class="col-md-3">
        <label class="form-label">Telefono</label>
        <input type="text" class="form-control" name="phone">
      </div>
      <div class="col-md-3">
        <label class="form-label">Pais</label>
        <input type="text" class="form-control" name="pais">
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select class="form-select form-select-sm" name="estado">
          <option value="no_tomado">no_tomado</option>
          <option value="respondido">respondido</option>
          <option value="preguntar">preguntar</option>
          <option value="cerrado">cerrado</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Prioridad</label>
        <select class="form-select form-select-sm" name="prioridad_id">
          <?php foreach ($priorities as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= View::e($p['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Problema</label>
        <input type="text" class="form-control" name="problem_name">
      </div>

      <div class="col-md-6">
        <label class="form-label">Estado info</label>
        <select class="form-select form-select-sm" name="estado_info" id="estado_info_select">
          <?php foreach ($statusInfoOptions as $option): ?>
            <option value="<?= View::e($option['nombre']) ?>"><?= View::e($option['nombre']) ?></option>
          <?php endforeach; ?>
          <option value="__nuevo__">+ Crear nuevo</option>
        </select>
      </div>

      <div class="col-md-6 d-none" id="estado_info_nuevo_wrap">
        <label class="form-label">Nuevo estado info</label>
        <input type="text" class="form-control" name="estado_info_nuevo" id="estado_info_nuevo" placeholder="Escribe nuevo estado info">
      </div>

      <div class="col-12">
        <label class="form-label">Tags (fail/question)</label>
        <select class="form-select form-select-sm" name="tag_ids[]" multiple size="2" style="max-width: 360px;">
          <?php foreach ($tags as $tag): ?>
            <option value="<?= (int)$tag['id'] ?>" <?= $tag['nombre'] === 'fail' ? 'selected' : '' ?>><?= View::e($tag['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <small class="text-muted d-block mt-1">Mantener Ctrl para seleccionar multiples.</small>
      </div>
      <div class="col-12">
        <label class="form-label">Descripcion</label>
        <textarea class="form-control" name="description" rows="3"></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-primary" type="submit">Guardar ticket</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function () {
    const select = document.getElementById('estado_info_select');
    const wrap = document.getElementById('estado_info_nuevo_wrap');
    const input = document.getElementById('estado_info_nuevo');
    if (!select || !wrap || !input) return;
    function toggleNewField() {
      const isNew = select.value === '__nuevo__';
      wrap.classList.toggle('d-none', !isNew);
      input.required = isNew;
      if (!isNew) input.value = '';
    }
    select.addEventListener('change', toggleNewField);
    toggleNewField();
  })();
</script>
