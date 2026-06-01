<h1 class="h3 mb-3">Auditoria</h1>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Fecha</th><th>Usuario</th><th>Accion</th><th>Tabla</th><th>Registro</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= View::e($r['created_at']) ?></td>
      <td><?= View::e($r['usuario_nombre']) ?></td>
      <td><?= View::e($r['accion']) ?></td>
      <td><?= View::e($r['tabla_afectada'] ?? '-') ?></td>
      <td><?= View::e((string)($r['registro_id'] ?? '-')) ?></td>
      <td><?= View::e($r['ip_address'] ?? '-') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
