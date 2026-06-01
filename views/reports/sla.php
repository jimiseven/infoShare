<h1 class="h3 mb-3">Reporte SLA</h1>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Ticket</th><th>Problema</th><th>Prioridad</th><th>Vencimiento</th><th>Estado SLA</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= View::e($r['ticket_number'] ?? ('ID-' . $r['id'])) ?></td>
      <td><?= View::e($r['problem_name'] ?? '-') ?></td>
      <td><?= View::e($r['prioridad'] ?? '-') ?></td>
      <td><?= View::e(DateFormat::dueEs($r['fecha_vencimiento'] ?? null)) ?></td>
      <td><?= View::e($r['sla_estado']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
