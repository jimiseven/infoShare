<?php
declare(strict_types=1);

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: index.php?r=dashboard');
            exit;
        }
        View::render('auth/login', ['title' => 'Iniciar sesion']);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Flash::set('danger', 'Email y contrasena son obligatorios.');
            header('Location: index.php?r=login');
            exit;
        }

        if (!Auth::attempt($email, $password)) {
            Flash::set('danger', 'Credenciales invalidas.');
            header('Location: index.php?r=login');
            exit;
        }

        Flash::set('success', 'Bienvenido al sistema.');
        header('Location: index.php?r=dashboard');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: index.php?r=login');
        exit;
    }
}
