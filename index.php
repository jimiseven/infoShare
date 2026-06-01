<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Database.php';
require_once __DIR__ . '/helpers/View.php';
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/helpers/Csrf.php';
require_once __DIR__ . '/helpers/Flash.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/TicketController.php';

$route = $_GET['r'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();
$dashboardController = new DashboardController();
$ticketController = new TicketController();

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

    default:
        http_response_code(404);
        echo 'Route not found';
        break;
}
