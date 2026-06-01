<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Database.php';
require_once __DIR__ . '/helpers/View.php';
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/helpers/Csrf.php';
require_once __DIR__ . '/helpers/Flash.php';
require_once __DIR__ . '/helpers/Audit.php';
require_once __DIR__ . '/helpers/DateFormat.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/controllers/ReportController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/MetricController.php';
require_once __DIR__ . '/controllers/AuditController.php';

$route = $_GET['r'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$dashboardController = new DashboardController();
$ticketController = new TicketController();
$reportController = new ReportController();
$userController = new UserController();
$metricController = new MetricController();
$auditController = new AuditController();

switch ($route) {
    case 'login':
        if ($method === 'POST') {
            Csrf::validateOrFail($_POST['_token'] ?? '');
            $authController->login();
            break;
        }
        $authController->showLogin();
        break;

    case 'logout':
        AuthMiddleware::handle();
        if ($method === 'POST') {
            Csrf::validateOrFail($_POST['_token'] ?? '');
            $authController->logout();
            break;
        }
        http_response_code(405);
        echo 'Method Not Allowed';
        break;

    case 'dashboard':
        AuthMiddleware::handle();
        $dashboardController->index();
        break;

    case 'tickets':
        AuthMiddleware::handle();
        $ticketController->index();
        break;

    case 'tickets/create':
        AuthMiddleware::handle();
        if ($method === 'POST') {
            Csrf::validateOrFail($_POST['_token'] ?? '');
            $ticketController->store();
            break;
        }
        $ticketController->create();
        break;

    case 'tickets/show':
        AuthMiddleware::handle();
        $ticketController->show();
        break;

    case 'tickets/update-status':
        AuthMiddleware::handle();
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            break;
        }
        Csrf::validateOrFail($_POST['_token'] ?? '');
        $ticketController->updateStatus();
        break;

    case 'tickets/assign':
        AuthMiddleware::handle();
        RoleMiddleware::requireRoles(['admin']);
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            break;
        }
        Csrf::validateOrFail($_POST['_token'] ?? '');
        $ticketController->assign();
        break;

    case 'tickets/comment':
        AuthMiddleware::handle();
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            break;
        }
        Csrf::validateOrFail($_POST['_token'] ?? '');
        $ticketController->addComment();
        break;

    case 'tickets/tags':
        AuthMiddleware::handle();
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            break;
        }
        Csrf::validateOrFail($_POST['_token'] ?? '');
        $ticketController->syncTags();
        break;

    case 'tickets/delete':
        AuthMiddleware::handle();
        RoleMiddleware::requireRoles(['admin']);
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            break;
        }
        Csrf::validateOrFail($_POST['_token'] ?? '');
        $ticketController->delete();
        break;

    case 'reports/pending':
        AuthMiddleware::handle();
        $reportController->pending();
        break;

    case 'reports/metrics':
        AuthMiddleware::handle();
        $reportController->metrics();
        break;

    case 'reports/sla':
        AuthMiddleware::handle();
        $reportController->sla();
        break;

    case 'metrics/daily':
        AuthMiddleware::handle();
        if ($method !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            break;
        }
        Csrf::validateOrFail($_POST['_token'] ?? '');
        $metricController->storeDaily();
        break;

    case 'users':
        AuthMiddleware::handle();
        RoleMiddleware::requireRoles(['admin']);
        $userController->index();
        break;

    case 'users/create':
        AuthMiddleware::handle();
        RoleMiddleware::requireRoles(['admin']);
        if ($method === 'POST') {
            Csrf::validateOrFail($_POST['_token'] ?? '');
            $userController->store();
            break;
        }
        $userController->create();
        break;

    case 'users/edit':
        AuthMiddleware::handle();
        RoleMiddleware::requireRoles(['admin']);
        if ($method === 'POST') {
            Csrf::validateOrFail($_POST['_token'] ?? '');
            $userController->update();
            break;
        }
        $userController->edit();
        break;

    case 'audit':
        AuthMiddleware::handle();
        RoleMiddleware::requireRoles(['admin']);
        $auditController->index();
        break;

    default:
        http_response_code(404);
        echo 'Route not found';
        break;
}
