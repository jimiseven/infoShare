<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/View.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/Audit.php';
require_once __DIR__ . '/../helpers/DateFormat.php';
require_once __DIR__ . '/../helpers/Router.php';
require_once __DIR__ . '/../helpers/Url.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RoleMiddleware.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/TicketController.php';
require_once __DIR__ . '/../controllers/ReportController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/MetricController.php';
require_once __DIR__ . '/../controllers/AuditController.php';

$controllers = [
    'auth' => new AuthController(),
    'dashboard' => new DashboardController(),
    'ticket' => new TicketController(),
    'report' => new ReportController(),
    'user' => new UserController(),
    'metric' => new MetricController(),
    'audit' => new AuditController(),
];

$router = new Router();
require __DIR__ . '/../routes/web.php';

return [
    'router' => $router,
];
