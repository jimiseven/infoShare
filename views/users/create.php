<h1 class="h3 mb-3">Crear usuario</h1>
<div class="card"><div class="card-body">
  <form method="POST" action="<?= View::e(Url::path('users/create')) ?>" class="row g-3">
    <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
    <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required></div>
    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
    <div class="col-md-6"><label class="form-label">Contrasena</label><input class="form-control" type="password" name="password" required></div>
    <div class="col-md-4"><label class="form-label">Rol</label><select class="form-select" name="rol_id"><?php foreach ($roles as $r): ?><option value="<?= (int)$r['id'] ?>"><?= View::e($r['nombre']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="activo" checked><label class="form-check-label">Activo</label></div></div>
    <div class="col-12"><button class="btn btn-primary">Guardar</button></div>
  </form>
</div></div>
