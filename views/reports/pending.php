<h1 class="h3 mb-3">Reporte de Pendientes</h1>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Ticket</th><th>Problema</th><th>Estado Info</th><th>Prioridad</th><th>Asignado</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= View::e($r['ticket_number'] ?? '-') ?></td>
      <td><?= View::e($r['problem_name'] ?? '-') ?></td>
      <td><?= View::e($r['estado_info'] ?? '-') ?></td>
      <td><?= View::e($r['prioridad'] ?? '-') ?></td>
      <td><?= View::e($r['asignado'] ?? '-') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>

<div class="card mt-4">
  <div class="card-header">Pendientes por dia</div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th>Dia</th><th>Tickets abiertos</th><th>Accion</th></tr></thead>
      <tbody>
      <?php foreach ($rowsByDay as $day): ?>
        <?php
          $ts = strtotime((string)$day['date']);
          $meses = [1=>'january',2=>'february',3=>'march',4=>'april',5=>'may',6=>'june',7=>'july',8=>'august',9=>'september',10=>'october',11=>'november',12=>'december'];
          $monthName = $meses[(int)date('n', $ts)] ?? date('F', $ts);
          $header = 'Hi, daily open ticket report: ' . date('d', $ts) . ' ' . $monthName . ' ' . date('Y', $ts);
          $lines = [$header];
          foreach ($day['tickets'] as $ticketLine) {
              $lines[] = $ticketLine['ticket_number'] . ' - ' . $ticketLine['problem_name'] . ' - ' . $ticketLine['estado_info'];
          }
          $smsText = implode("\n", $lines);
        ?>
        <tr>
          <td><?= View::e(date('Y-m-d', $ts)) ?></td>
          <td><?= (int)$day['count'] ?></td>
          <td>
            <button
              type="button"
              class="btn btn-sm btn-outline-dark"
              data-copy-text="<?= View::e($smsText) ?>"
              onclick="copyPendingSms(this)">
              Copiar SMS
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function copyPendingSms(button) {
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
