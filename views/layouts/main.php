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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --bg: #eef1f4;
      --surface: #ffffff;
      --line: #dbe1e7;
      --text: #102131;
      --muted: #5a6f82;
      --primary: #0a6c8f;
      --accent: #f5a524;
      --danger-soft: #fff1f1;
      --shadow: 0 12px 28px rgba(10, 30, 48, 0.08);
    }

    body {
      background: radial-gradient(circle at top right, #dce7ef, var(--bg) 50%);
      color: var(--text);
      font-family: 'Manrope', sans-serif;
    }

    .app-shell { height: 100vh; overflow: hidden; }
    .sidebar {
      width: 280px;
      background: linear-gradient(180deg, #09293b 0%, #0d3a4f 100%);
      color: #d8ebf3;
      box-shadow: var(--shadow);
      height: 100vh;
      position: sticky;
      top: 0;
      overflow-y: auto;
    }
    .brand-wrap {
      border-bottom: 1px solid rgba(255,255,255,.12);
      padding: 1rem 1.2rem;
    }
    .brand-title { font-weight: 800; letter-spacing: .2px; color: #ffffff; }
    .brand-sub { font-size: .8rem; opacity: .75; }

    .nav-link.side-link {
      color: #d8ebf3;
      border-radius: .75rem;
      padding: .55rem .75rem;
      margin-bottom: .3rem;
      font-weight: 600;
    }
    .nav-link.side-link:hover,
    .nav-link.side-link.active {
      background: rgba(255,255,255,.16);
      color: #fff;
    }

    .content-wrap { flex: 1; height: 100vh; overflow: hidden; }
    .topbar {
      background: var(--surface);
      border-bottom: 1px solid var(--line);
      box-shadow: 0 1px 0 rgba(0,0,0,.02);
    }
    .main-panel { padding: 1.1rem; overflow-y: auto; flex: 1; min-height: 0; }
    .card {
      border: 1px solid var(--line);
      box-shadow: var(--shadow);
      border-radius: 1rem;
    }
    .table { --bs-table-bg: transparent; }
    .table-danger { --bs-table-bg: var(--danger-soft); }
    .chip {
      border: 1px solid var(--line);
      border-radius: 999px;
      background: #f8fbfd;
      color: var(--muted);
      font-size: .78rem;
      padding: .15rem .6rem;
    }
    .fab {
      position: fixed;
      right: 1rem;
      bottom: 1rem;
      z-index: 1040;
      border-radius: 999px;
      padding: .7rem 1rem;
      box-shadow: var(--shadow);
    }
    @media (max-width: 991.98px) {
      .main-panel { padding: .8rem; }
    }
  </style>
</head>
<body>
<div class="d-lg-flex app-shell">
  <?php if ($user): ?>
    <aside class="sidebar d-none d-lg-flex flex-column p-3">
      <div class="brand-wrap mb-3">
        <div class="brand-title"><?= View::e(APP_NAME) ?></div>
        <div class="brand-sub">Ticket operations panel</div>
      </div>
      <nav class="nav flex-column mb-3">
        <a href="<?= View::e(Url::path('dashboard')) ?>" class="nav-link side-link">Dashboard</a>
        <a href="<?= View::e(Url::path('tickets')) ?>" class="nav-link side-link">Tickets</a>
        <a href="<?= View::e(Url::path('reports/pending')) ?>" class="nav-link side-link">Pendientes</a>
        <a href="<?= View::e(Url::path('reports/metrics')) ?>" class="nav-link side-link">Metricas</a>
        <a href="<?= View::e(Url::path('reports/sla')) ?>" class="nav-link side-link">SLA</a>
        <?php if ($user['rol'] === 'admin'): ?>
          <a href="<?= View::e(Url::path('users')) ?>" class="nav-link side-link">Usuarios</a>
          <a href="<?= View::e(Url::path('audit')) ?>" class="nav-link side-link">Auditoria</a>
        <?php endif; ?>
      </nav>
      <div class="mt-auto">
        <form method="POST" action="<?= View::e(Url::path('logout')) ?>">
          <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
          <button class="btn btn-outline-light w-100" type="submit">Salir</button>
        </form>
      </div>
    </aside>
  <?php endif; ?>

  <div class="content-wrap d-flex flex-column">
    <header class="topbar px-3 py-2 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <?php if ($user): ?>
          <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav">Menu</button>
        <?php endif; ?>
        <div>
          <strong><?= View::e($title ?? 'Panel') ?></strong>
        </div>
      </div>
      <?php if ($user): ?>
        <span class="chip"><?= View::e($user['nombre']) ?> - <?= View::e($user['rol']) ?></span>
      <?php endif; ?>
    </header>

    <main class="main-panel">
      <?php if ($flash): ?>
        <div class="alert alert-<?= View::e($flash['type']) ?> alert-dismissible fade show" role="alert">
          <?= View::e($flash['message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php include $viewFile; ?>
    </main>
  </div>
</div>

<?php if ($user): ?>
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="mobileNavLabel"><?= View::e(APP_NAME) ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="nav flex-column mb-3">
      <a href="<?= View::e(Url::path('dashboard')) ?>" class="nav-link">Dashboard</a>
      <a href="<?= View::e(Url::path('tickets')) ?>" class="nav-link">Tickets</a>
      <a href="<?= View::e(Url::path('reports/pending')) ?>" class="nav-link">Pendientes</a>
      <a href="<?= View::e(Url::path('reports/metrics')) ?>" class="nav-link">Metricas</a>
      <a href="<?= View::e(Url::path('reports/sla')) ?>" class="nav-link">SLA</a>
      <?php if ($user['rol'] === 'admin'): ?>
        <a href="<?= View::e(Url::path('users')) ?>" class="nav-link">Usuarios</a>
        <a href="<?= View::e(Url::path('audit')) ?>" class="nav-link">Auditoria</a>
      <?php endif; ?>
    </div>
    <form method="POST" action="<?= View::e(Url::path('logout')) ?>">
      <input type="hidden" name="_token" value="<?= View::e(Csrf::token()) ?>">
      <button class="btn btn-outline-dark w-100" type="submit">Salir</button>
    </form>
  </div>
</div>

<button type="button" class="btn btn-warning fab" data-bs-toggle="modal" data-bs-target="#quickActionsModal">Acciones rapidas</button>

<div class="modal fade" id="quickActionsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Acciones rapidas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-grid gap-2">
        <a class="btn btn-outline-primary" href="<?= View::e(Url::path('tickets/create')) ?>">Crear ticket</a>
        <a class="btn btn-outline-primary" href="<?= View::e(Url::path('reports/pending')) ?>">Ver pendientes</a>
        <a class="btn btn-outline-primary" href="<?= View::e(Url::path('reports/metrics')) ?>">Registrar metricas</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
