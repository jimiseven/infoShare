<h1 class="h3 mb-3">Reporte de Metricas</h1>
<?php $loggedUser = Auth::user(); ?>
<form class="card card-body mb-3" method="GET" action="<?= View::e(Url::path('reports/metrics')) ?>">
  <div class="row g-2">
    <div class="col-md-3"><input type="date" class="form-control" name="fecha" value="<?= View::e($_GET['fecha'] ?? '') ?>"></div>
    <div class="col-md-3"><input type="date" class="form-control" name="desde" value="<?= View::e($_GET['desde'] ?? '') ?>"></div>
    <div class="col-md-3"><input type="date" class="form-control" name="hasta" value="<?= View::e($_GET['hasta'] ?? '') ?>"></div>
    <div class="col-md-3">
      <select class="form-select" name="usuario_id">
        <option value="">Todos los usuarios</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= (int)$u['id'] ?>" <?= ((int)($_GET['usuario_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>><?= View::e($u['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mt-2"><button class="btn btn-dark btn-sm">Filtrar</button></div>
</form>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Fecha</th><th>Usuario</th><th>Inbound</th><th>Outbound</th><th>Failed</th><th>Chats</th><th>Emails</th><th>Total</th><th>HQ</th><th>Accion</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <?php
      $fechaTexto = date('M j', strtotime((string)$r['fecha']));
      $reporterName = $loggedUser['nombre'] ?? ($r['nombre'] ?? 'User');
      $sms = "Here is my report of today\n"
           . "- Report - " . $fechaTexto . "\n"
           . "- Tickets: " . (int)$r['tickets_dia'] . "\n"
           . "- " . $reporterName . "\n"
           . "- Inbound calls: " . (int)$r['inbound_calls'] . "\n"
           . "- Outbound calls: " . (int)$r['outbound_calls'] . "\n"
           . "- Calls failed to connect: 0\n"
           . "- Online chat: " . (int)$r['chats'] . "\n"
           . "- Issues resolved over the first call: 0\n"
           . "- Emails: " . (int)$r['emails'] . "\n"
           . "- Tickets needing HQ help or attention: 0";
    ?>
    <tr>
      <td><?= View::e($r['fecha']) ?></td><td><?= View::e($r['nombre']) ?></td>
      <td><?= (int)$r['inbound_calls'] ?></td><td><?= (int)$r['outbound_calls'] ?></td>
      <td><?= (int)$r['failed_calls'] ?></td><td><?= (int)$r['chats'] ?></td>
      <td><?= (int)$r['emails'] ?></td><td><?= (int)$r['total_interacciones'] ?></td>
      <td><?= (int)$r['tickets_hq'] ?></td>
      <td>
        <button
          type="button"
          class="btn btn-sm btn-outline-dark"
          data-copy-text="<?= View::e($sms) ?>"
          onclick="copyMetricsSms(this)">
          Copiar SMS
        </button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>

<script>
function copyMetricsSms(button) {
  const text = button.getAttribute('data-copy-text') || '';
  if (!text) return;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(function () {
      button.textContent = 'Copiado';
      setTimeout(function () { button.textContent = 'Copiar SMS'; }, 1200);
    });
    return;
  }
  const input = document.createElement('textarea');
  input.value = text;
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
  button.textContent = 'Copiado';
  setTimeout(function () { button.textContent = 'Copiar SMS'; }, 1200);
}
</script>
