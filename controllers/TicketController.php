<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Priority.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/StatusInfoOption.php';

class TicketController
{
    public function index(): void
    {
        $user = Auth::user();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $estadoRaw = trim((string)($_GET['estado'] ?? ''));
        $allowedEstados = ['no_tomado', 'respondido', 'preguntar', 'cerrado'];
        $filters = [
            'tag_id' => (int)($_GET['tag_id'] ?? 0) > 0 ? (int)$_GET['tag_id'] : null,
            'estado' => in_array($estadoRaw, $allowedEstados, true) ? $estadoRaw : null,
            'prioridad_id' => (int)($_GET['prioridad_id'] ?? 0) > 0 ? (int)$_GET['prioridad_id'] : null,
            'asignado_a' => (int)($_GET['asignado_a'] ?? 0) > 0 ? (int)$_GET['asignado_a'] : null,
            'q' => trim((string)($_GET['q'] ?? '')),
        ];
        if ($user['rol'] === 'usuario_normal') {
            $filters['asignado_a'] = null;
        }
        $result = Ticket::searchByRole($user, $filters, $page, $perPage);
        View::render('tickets/index', [
            'title' => 'Tickets',
            'tickets' => $result['rows'],
            'tags' => Tag::failQuestionTags(),
            'priorities' => Priority::all(),
            'users' => User::assignableUsers(),
            'filters' => $filters,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $result['total'],
        ]);
    }

    public function create(): void
    {
        $priorities = Priority::all();
        View::render('tickets/create', [
            'title' => 'Crear ticket',
            'priorities' => $priorities,
            'tags' => Tag::failQuestionTags(),
            'statusInfoOptions' => StatusInfoOption::all(),
        ]);
    }

    public function store(): void
    {
        $user = Auth::user();
        $identifierExists = trim((string)($_POST['ticket_number'] ?? '')) !== '' || trim((string)($_POST['email'] ?? '')) !== '' || trim((string)($_POST['phone'] ?? '')) !== '';
        if (!$identifierExists) {
            Flash::set('danger', 'Debes ingresar al menos ticket number, email o telefono.');
            Url::redirect('tickets/create');
        }
        if (!Validator::email($_POST['email'] ?? null)) {
            Flash::set('danger', 'Email invalido.');
            Url::redirect('tickets/create');
        }

        $estadoInfo = trim((string)($_POST['estado_info'] ?? ''));
        $estadoInfoNuevo = trim((string)($_POST['estado_info_nuevo'] ?? ''));
        if ($estadoInfoNuevo !== '') {
            StatusInfoOption::createIfNotExists($estadoInfoNuevo);
            $_POST['estado_info'] = $estadoInfoNuevo;
        } else {
            $_POST['estado_info'] = $estadoInfo;
        }

        $ticketId = Ticket::create($_POST, $user);

        $tagIds = $_POST['tag_ids'] ?? [];
        if (empty($tagIds)) {
            $failTag = Tag::failQuestionTags();
            if (!empty($failTag[0]['id'])) {
                $tagIds = [(int)$failTag[0]['id']];
            }
        }
        Tag::syncTicketTags($ticketId, $tagIds);
        Flash::set('success', 'Ticket creado correctamente.');
        Url::redirect('tickets/show', ['id' => $ticketId]);
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
            'priorities' => Priority::all(),
            'statusInfoOptions' => StatusInfoOption::all(),
            'tagsAll' => Tag::failQuestionTags(),
            'tagsSelected' => Tag::idsByTicket($id),
            'tags' => Ticket::tags($id),
        ]);
    }

    public function updateFields(): void
    {
        $user = Auth::user();
        $id = (int)($_POST['ticket_id'] ?? 0);
        if ($id <= 0) {
            Flash::set('danger', 'Ticket invalido para editar.');
            Url::redirect('tickets');
        }
        if (!Validator::email($_POST['email'] ?? null)) {
            Flash::set('danger', 'Email invalido.');
            Url::redirect('tickets/show', ['id' => $id]);
        }
        Ticket::updateFields($id, $_POST, $user);
        Flash::set('success', 'Ticket actualizado.');
        Url::redirect('tickets/show', ['id' => $id]);
    }

    public function updateStatus(): void
    {
        $user = Auth::user();
        $id = (int)($_POST['ticket_id'] ?? 0);
        $estado = trim((string)($_POST['estado'] ?? ''));
        $estadoInfo = $_POST['estado_info'] ?? null;
        if ($id <= 0 || $estado === '') {
            Flash::set('danger', 'Datos incompletos para actualizar estado.');
            Url::redirect('tickets');
        }

        Ticket::updateStatus($id, $estado, $estadoInfo, $user);
        Flash::set('success', 'Estado actualizado.');
        Url::redirect('tickets/show', ['id' => $id]);
    }

    public function assign(): void
    {
        $id = (int)($_POST['ticket_id'] ?? 0);
        $userId = (int)($_POST['asignado_a'] ?? 0);
        if ($id <= 0 || $userId <= 0) {
            Flash::set('danger', 'Datos invalidos para asignacion.');
            Url::redirect('tickets');
        }
        Ticket::assign($id, $userId);
        Flash::set('success', 'Ticket asignado correctamente.');
        Url::redirect('tickets/show', ['id' => $id]);
    }

    public function addComment(): void
    {
        $user = Auth::user();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $comentario = trim((string)($_POST['comentario'] ?? ''));
        $esInterno = isset($_POST['es_interno']) ? 1 : 0;
        if ($ticketId <= 0 || $comentario === '') {
            Flash::set('danger', 'Comentario invalido.');
            Url::redirect('tickets');
        }
        $ticket = Ticket::findById($ticketId, $user);
        if (!$ticket) {
            Flash::set('danger', 'No tienes acceso al ticket o no existe.');
            Url::redirect('tickets');
        }

        $saved = Ticket::addComment($ticketId, (int)$user['id'], $comentario, $esInterno);
        if (!$saved) {
            Flash::set('danger', 'No se pudo guardar el comentario. Intenta de nuevo.');
            Url::redirect('tickets/show', ['id' => $ticketId]);
        }

        Flash::set('success', 'Comentario agregado.');
        Url::redirect('tickets/show', ['id' => $ticketId]);
    }

    public function syncTags(): void
    {
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            Flash::set('danger', 'Ticket invalido para tags.');
            Url::redirect('tickets');
        }
        Tag::syncTicketTags($ticketId, $_POST['tag_ids'] ?? []);
        $user = Auth::user();
        Audit::log((int)$user['id'], 'ACTUALIZAR_TAGS_TICKET', 'tickets', $ticketId);
        Flash::set('success', 'Tags actualizados.');
        Url::redirect('tickets/show', ['id' => $ticketId]);
    }

    public function delete(): void
    {
        $id = (int)($_POST['ticket_id'] ?? 0);
        $user = Auth::user();
        if ($id <= 0) {
            Flash::set('danger', 'Ticket invalido para eliminar.');
            Url::redirect('tickets');
        }
        Ticket::softDelete($id, (int)$user['id']);
        Flash::set('success', 'Ticket eliminado (soft delete).');
        Url::redirect('tickets');
    }
}
