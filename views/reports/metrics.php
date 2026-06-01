<h1 class="h3 mb-3">Reporte de Metricas</h1>
<?php $loggedUser = Auth::user(); ?>
<form class="card card-body mb-3" method="GET" action="<?= View::e(Url::path('reports/metrics')) ?>">
  <div class="row g-2">
    <div class="col-md-4"><input type="date" class="form-control" name="desde" value="<?= View::e($_GET['desde'] ?? '') ?>" placeholder="Desde"></div>
    <div class="col-md-4"><input type="date" class="form-control" name="hasta" value="<?= View::e($_GET['hasta'] ?? '') ?>" placeholder="Hasta"></div>
    <div class="col-md-4">
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

<?php
  $myRows = array_values(array_filter($rows, static fn(array $row): bool => (int)($row['usuario_id'] ?? 0) === (int)($loggedUser['id'] ?? 0)));
?>

<?php if (($loggedUser['rol'] ?? '') === 'admin'): ?>
  <div class="card mb-3">
    <div class="card-header">Tu avance (<?= View::e($loggedUser['nombre'] ?? 'Usuario') ?>)</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Fecha</th><th>Inbound</th><th>Outbound</th><th>Failed</th><th>Chats</th><th>Emails</th><th>Tickets</th></tr></thead>
          <tbody>
          <?php if (count($myRows) === 0): ?>
            <tr><td colspan="7" class="text-muted">No hay avances tuyos para el filtro actual.</td></tr>
          <?php endif; ?>
          <?php foreach ($myRows as $r): ?>
            <?php
              $rowInbound = isset($r['sms_inbound_calls']) ? (int)$r['sms_inbound_calls'] : (int)$r['inbound_calls'];
              $rowOutbound = isset($r['sms_outbound_calls']) ? (int)$r['sms_outbound_calls'] : (int)$r['outbound_calls'];
              $rowChats = isset($r['sms_chats']) ? (int)$r['sms_chats'] : (int)$r['chats'];
              $rowEmails = isset($r['sms_emails']) ? (int)$r['sms_emails'] : (int)$r['emails'];
            ?>
            <tr>
              <td><?= View::e($r['fecha']) ?></td>
              <td><?= $rowInbound ?></td><td><?= $rowOutbound ?></td>
              <td><?= (int)$r['failed_calls'] ?></td><td><?= $rowChats ?></td>
              <td><?= $rowEmails ?></td>
              <td><?= (int)$r['tickets_dia'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Fecha</th><th>Usuario</th><th>Inbound</th><th>Outbound</th><th>Failed</th><th>Chats</th><th>Emails</th><th>Tickets</th><th>HQ</th><th>Accion</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <?php
      $rowInbound = isset($r['sms_inbound_calls']) ? (int)$r['sms_inbound_calls'] : (int)$r['inbound_calls'];
      $rowOutbound = isset($r['sms_outbound_calls']) ? (int)$r['sms_outbound_calls'] : (int)$r['outbound_calls'];
      $rowChats = isset($r['sms_chats']) ? (int)$r['sms_chats'] : (int)$r['chats'];
      $rowEmails = isset($r['sms_emails']) ? (int)$r['sms_emails'] : (int)$r['emails'];
      $fechaTexto = date('M j', strtotime((string)$r['fecha']));
      $reporterName = $r['nombre'] ?? ($loggedUser['nombre'] ?? 'User');
      $sms = "Here is my report of today\n"
           . "- Report - " . $fechaTexto . "\n"
           . "- Tickets worked (unique): " . (int)$r['tickets_dia'] . "\n"
           . "- Interaction breakdown:\n"
           . "  * Emails: " . $rowEmails . "\n"
           . "  * Online chat: " . $rowChats . "\n"
           . "  * Inbound calls: " . $rowInbound . "\n"
           . "  * Outbound calls: " . $rowOutbound . "\n"
           . "  * Failed calls: " . (int)$r['failed_calls'] . "\n"
           . "- " . $reporterName . "\n"
           . "- Tickets needing HQ help or attention: " . (int)$r['tickets_hq'];
    ?>
    <tr>
      <td><?= View::e($r['fecha']) ?></td><td><?= View::e($r['nombre']) ?></td>
      <td><?= $rowInbound ?></td><td><?= $rowOutbound ?></td>
      <td><?= (int)$r['failed_calls'] ?></td><td><?= $rowChats ?></td>
      <td><?= $rowEmails ?></td><td><?= (int)$r['tickets_dia'] ?></td>
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
