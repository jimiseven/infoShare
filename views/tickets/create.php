<h1 class="h3 mb-3">Crear ticket</h1>
<div class="card">
  <div class="card-body">
    <form method="POST" action="index.php?r=tickets/create" class="row g-3">
      <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">

      <div class="col-md-4">
        <label class="form-label">Ticket number</label>
        <input type="text" class="form-control" name="ticket_number">
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email">
      </div>
      <div class="col-md-4">
        <label class="form-label">Telefono</label>
        <input type="text" class="form-control" name="phone">
      </div>

      <div class="col-md-4">
        <label class="form-label">Pais</label>
        <input type="text" class="form-control" name="pais">
      </div>
      <div class="col-md-4">
        <label class="form-label">Estado</label>
        <select class="form-select" name="estado">
          <option value="no_tomado">no_tomado</option>
          <option value="respondido">respondido</option>
          <option value="preguntar">preguntar</option>
          <option value="cerrado">cerrado</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Prioridad</label>
        <select class="form-select" name="prioridad_id">
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
        <label class="form-label">Asignar a</label>
        <select class="form-select" name="asignado_a">
          <option value="">Sin asignar</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>"><?= View::e($u['nombre']) ?> (<?= View::e($u['rol']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">SLA horas</label>
        <input type="number" class="form-control" name="sla_horas" value="24" min="1">
      </div>
      <div class="col-md-6">
        <label class="form-label">Fecha vencimiento</label>
        <input type="datetime-local" class="form-control" name="fecha_vencimiento">
      </div>

      <div class="col-12">
        <label class="form-label">Estado info</label>
        <input type="text" class="form-control" name="estado_info">
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
