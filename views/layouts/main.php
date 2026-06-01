<?php
$flash = Flash::get();
$user = Auth::user();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e(($title ?? APP_NAME) . ' | ' . APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php?r=dashboard"><?= View::e(APP_NAME) ?></a>
    <?php if ($user): ?>
      <div class="d-flex align-items-center gap-3 text-white">
        <small><?= View::e($user['nombre']) ?> (<?= View::e($user['rol']) ?>)</small>
        <form method="POST" action="index.php?r=logout" class="m-0">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <button class="btn btn-sm btn-outline-light" type="submit">Salir</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</nav>

<main class="container py-4">
  <?php if ($flash): ?>
    <div class="alert alert-<?= View::e($flash['type']) ?> alert-dismissible fade show" role="alert">
      <?= View::e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if ($user): ?>
    <div class="mb-3 d-flex gap-2">
      <a href="index.php?r=dashboard" class="btn btn-sm btn-outline-secondary">Dashboard</a>
      <a href="index.php?r=tickets" class="btn btn-sm btn-outline-secondary">Tickets</a>
      <a href="index.php?r=tickets/create" class="btn btn-sm btn-primary">Nuevo ticket</a>
    </div>
  <?php endif; ?>

  <?php include $viewFile; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
