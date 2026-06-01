<h1 class="h3 mb-3">Editar usuario</h1>
<div class="card"><div class="card-body">
  <form method="POST" action="index.php?r=users/edit" class="row g-3">
    <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
    <input type="hidden" name="id" value="<?= (int)$target['id'] ?>">
    <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?= View::e($target['nombre']) ?>" required></div>
    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= View::e($target['email']) ?>" required></div>
    <div class="col-md-6"><label class="form-label">Nueva contrasena (opcional)</label><input class="form-control" type="password" name="password"></div>
    <div class="col-md-4"><label class="form-label">Rol</label><select class="form-select" name="rol_id"><?php foreach ($roles as $r): ?><option value="<?= (int)$r['id'] ?>" <?= ((int)$target['rol_id'] === (int)$r['id']) ? 'selected' : '' ?>><?= View::e($r['nombre']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="activo" <?= ((int)$target['activo'] === 1) ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div></div>
    <div class="col-12"><button class="btn btn-dark">Actualizar</button></div>
  </form>
</div></div>
