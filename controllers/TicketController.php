<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Priority.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/StatusInfoOption.php';
require_once __DIR__ . '/../models/Metric.php';

class TicketController
{
    public function index(): void
    {
        $user = Auth::user();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $estadoRaw = trim((string)($_GET['estado'] ?? ''));
        $fechaCierreRaw = trim((string)($_GET['fecha_cierre'] ?? ''));
        $fechaCierre = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCierreRaw) ? $fechaCierreRaw : null;
        $allowedEstados = ['no_tomado', 'respondido', 'preguntar', 'cerrado'];
        $filters = [
            'tag_id' => (int)($_GET['tag_id'] ?? 0) > 0 ? (int)$_GET['tag_id'] : null,
            'estado' => in_array($estadoRaw, $allowedEstados, true) ? $estadoRaw : null,
            'prioridad_id' => (int)($_GET['prioridad_id'] ?? 0) > 0 ? (int)$_GET['prioridad_id'] : null,
            'asignado_a' => (int)($_GET['asignado_a'] ?? 0) > 0 ? (int)$_GET['asignado_a'] : null,
            'q' => trim((string)($_GET['q'] ?? '')),
            'fecha_cierre' => $fechaCierre,
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
        $ticketNumber = trim((string)($_POST['ticket_number'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        if ($ticketNumber === '' && $email === '' && $phone === '') {
            $_POST['ticket_number'] = 'AUTO-' . date('YmdHis');
        }
        if (!Validator::email($_POST['email'] ?? null)) {
            Flash::set('danger', 'Email invalido.');
            Url::redirect('tickets/create');
        }
        if (!Validator::phone($_POST['phone'] ?? null)) {
            Flash::set('danger', 'Telefono invalido. Puedes usar formato que inicia con 00.');
            Url::redirect('tickets/create');
        }

        $estadoInfo = trim((string)($_POST['estado_info'] ?? ''));
        $estadoInfoNuevo = trim((string)($_POST['estado_info_nuevo'] ?? ''));
        if ($estadoInfo === '__nuevo__' && $estadoInfoNuevo === '') {
            Flash::set('danger', 'Debes escribir el nuevo estado info.');
            Url::redirect('tickets/create');
        }
        if ($estadoInfoNuevo !== '') {
            StatusInfoOption::createIfNotExists($estadoInfoNuevo);
            $_POST['estado_info'] = $estadoInfoNuevo;
        } else {
            $_POST['estado_info'] = $estadoInfo;
        }

        try {
            $ticketId = Ticket::create($_POST, $user);
        } catch (Throwable) {
            Flash::set('danger', 'No se pudo crear el ticket. Revisa los datos e intenta de nuevo.');
            Url::redirect('tickets/create');
        }

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

    public function bulkCreate(): void
    {
        $user = Auth::user();
        $rawList = (string)($_POST['ticket_list'] ?? '');
        $lines = preg_split('/\r\n|\r|\n/', $rawList) ?: [];

        $ticketNumbers = [];
        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }
            $firstCell = preg_split('/\t|;|,/', $line)[0] ?? '';
            $ticket = trim((string)$firstCell);
            $ticket = trim($ticket, " \t\n\r\0\x0B\"'");
            if ($ticket !== '') {
                $ticketNumbers[] = mb_substr($ticket, 0, 30);
            }
        }

        $ticketNumbers = array_values(array_unique($ticketNumbers));
        if (count($ticketNumbers) === 0) {
            Flash::set('danger', 'No se detectaron tickets validos en la lista.');
            Url::redirect('tickets');
        }

        $created = 0;
        $existing = 0;
        $failed = 0;
        $firstError = null;
        foreach ($ticketNumbers as $ticketNumber) {
            if (Ticket::existsByTicketNumber($ticketNumber)) {
                $existing++;
                continue;
            }
            try {
                Ticket::create([
                    'ticket_number' => $ticketNumber,
                    'estado' => 'no_tomado',
                ], $user);
                $created++;
            } catch (Throwable $e) {
                $failed++;
                if ($firstError === null) {
                    $firstError = $e->getMessage();
                }
            }
        }

        if ($failed > 0) {
            $detail = $firstError !== null ? ' Error: ' . $firstError : '';
            Flash::set('danger', 'Carga masiva parcial. Nuevos: ' . $created . '. Existentes: ' . $existing . '. Fallidos: ' . $failed . '.' . $detail);
            Url::redirect('tickets');
        }

        Flash::set('success', 'Carga masiva finalizada. Nuevos: ' . $created . '. Ya existentes: ' . $existing . '.');
        Url::redirect('tickets');
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
        if (!Validator::phone($_POST['phone'] ?? null)) {
            Flash::set('danger', 'Telefono invalido. Puedes usar formato que inicia con 00.');
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
        $user = Auth::user();
        if (($user['rol'] ?? '') !== 'admin') {
            Flash::set('danger', 'No tienes permisos para reasignar tickets.');
            Url::redirect('tickets');
        }

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
        $metricMode = trim((string)($_POST['metric_mode'] ?? ''));
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

        if ($metricMode !== '') {
            $metricOk = Metric::incrementFromComment((int)$user['id'], $metricMode, $ticketId, $comentario);
            if (!$metricOk) {
                Flash::set('danger', 'Comentario guardado, pero no se pudo registrar la metrica.');
                Url::redirect('tickets/show', ['id' => $ticketId]);
            }
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

    public function deleteMultiple(): void
    {
        $user = Auth::user();
        $ids = $_POST['ticket_ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            Flash::set('danger', 'Debes seleccionar al menos un ticket para eliminar.');
            Url::redirect('tickets');
        }

        $deleted = Ticket::softDeleteMany($ids, (int)$user['id']);
        if ($deleted <= 0) {
            Flash::set('danger', 'No se eliminaron tickets. Verifica la seleccion.');
            Url::redirect('tickets');
        }

        Flash::set('success', 'Se eliminaron ' . $deleted . ' ticket(s).');
        Url::redirect('tickets');
    }
}
