<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/User.php';

class UserController
{
    public function index(): void
    {
        View::render('users/index', [
            'title' => 'Usuarios',
            'users' => User::all(),
        ]);
    }

    public function create(): void
    {
        View::render('users/create', [
            'title' => 'Crear usuario',
            'roles' => User::roles(),
        ]);
    }

    public function store(): void
    {
        if (!Validator::email($_POST['email'] ?? null)) {
            Flash::set('danger', 'Email invalido.');
            Url::redirect('users/create');
        }
        if (!Validator::positiveInt($_POST['rol_id'] ?? null)) {
            Flash::set('danger', 'Rol invalido.');
            Url::redirect('users/create');
        }
        User::create($_POST);
        $auth = Auth::user();
        Audit::log((int)$auth['id'], 'CREAR_USUARIO', 'usuarios', null);
        Flash::set('success', 'Usuario creado.');
        Url::redirect('users');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $target = User::find($id);
        if (!$target) {
            http_response_code(404);
            exit('Usuario no encontrado');
        }
        View::render('users/edit', [
            'title' => 'Editar usuario',
            'target' => $target,
            'roles' => User::roles(),
        ]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0 || !Validator::email($_POST['email'] ?? null)) {
            Flash::set('danger', 'Datos invalidos para actualizar usuario.');
            Url::redirect('users');
        }
        User::update($id, $_POST);
        $auth = Auth::user();
        Audit::log((int)$auth['id'], 'ACTUALIZAR_USUARIO', 'usuarios', $id);
        Flash::set('success', 'Usuario actualizado.');
        Url::redirect('users');
    }
}
