<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Priority.php';
require_once __DIR__ . '/../models/User.php';

class TicketController
{
    public function index(): void
    {
        $user = Auth::user();
        $tickets = Ticket::listByRole($user);
        View::render('tickets/index', [
            'title' => 'Tickets',
            'tickets' => $tickets,
        ]);
    }

    public function create(): void
    {
        $priorities = Priority::all();
        $users = User::assignableUsers();
        View::render('tickets/create', [
            'title' => 'Crear ticket',
            'priorities' => $priorities,
            'users' => $users,
        ]);
    }

    public function store(): void
    {
        $user = Auth::user();
        $identifierExists = trim((string)($_POST['ticket_number'] ?? '')) !== '' || trim((string)($_POST['email'] ?? '')) !== '' || trim((string)($_POST['phone'] ?? '')) !== '';
        if (!$identifierExists) {
            Flash::set('danger', 'Debes ingresar al menos ticket number, email o telefono.');
            header('Location: index.php?r=tickets/create');
            exit;
        }

        $ticketId = Ticket::create($_POST, $user);
        Flash::set('success', 'Ticket creado correctamente.');
        header('Location: index.php?r=tickets/show&id=' . $ticketId);
        exit;
    }

    public function show(): void
    {
        $user = Auth::user();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo 'Ticket invalido';
            return;
        }

        $ticket = Ticket::findById($id, $user);
        if (!$ticket) {
            http_response_code(404);
            echo 'Ticket no encontrado';
            return;
        }

        View::render('tickets/show', [
            'title' => 'Detalle ticket',
            'ticket' => $ticket,
            'comments' => Ticket::comments($id),
            'history' => Ticket::history($id),
            'users' => User::assignableUsers(),
        ]);
    }

    public function updateStatus(): void
    {
        $user = Auth::user();
        $id = (int)($_POST['ticket_id'] ?? 0);
        $estado = trim((string)($_POST['estado'] ?? ''));
        $estadoInfo = $_POST['estado_info'] ?? null;
        if ($id <= 0 || $estado === '') {
            Flash::set('danger', 'Datos incompletos para actualizar estado.');
            header('Location: index.php?r=tickets');
            exit;
        }

        Ticket::updateStatus($id, $estado, $estadoInfo, $user);
        Flash::set('success', 'Estado actualizado.');
        header('Location: index.php?r=tickets/show&id=' . $id);
        exit;
    }

    public function assign(): void
    {
        $id = (int)($_POST['ticket_id'] ?? 0);
        $userId = (int)($_POST['asignado_a'] ?? 0);
        if ($id <= 0 || $userId <= 0) {
            Flash::set('danger', 'Datos invalidos para asignacion.');
            header('Location: index.php?r=tickets');
            exit;
        }
        Ticket::assign($id, $userId);
        Flash::set('success', 'Ticket asignado correctamente.');
        header('Location: index.php?r=tickets/show&id=' . $id);
        exit;
    }

    public function addComment(): void
    {
        $user = Auth::user();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $comentario = trim((string)($_POST['comentario'] ?? ''));
        $esInterno = isset($_POST['es_interno']) ? 1 : 0;
        if ($ticketId <= 0 || $comentario === '') {
            Flash::set('danger', 'Comentario invalido.');
            header('Location: index.php?r=tickets');
            exit;
        }
        Ticket::addComment($ticketId, (int)$user['id'], $comentario, $esInterno);
        Flash::set('success', 'Comentario agregado.');
        header('Location: index.php?r=tickets/show&id=' . $ticketId);
        exit;
    }
}
