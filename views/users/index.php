<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Usuarios</h1>
  <a href="index.php?r=users/create" class="btn btn-primary">Nuevo usuario</a>
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= View::e($u['nombre']) ?></td>
      <td><?= View::e($u['email']) ?></td>
      <td><?= View::e($u['rol']) ?></td>
      <td><?= (int)$u['activo'] === 1 ? 'si' : 'no' ?></td>
      <td><a class="btn btn-sm btn-outline-dark" href="index.php?r=users/edit&id=<?= (int)$u['id'] ?>">Editar</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
