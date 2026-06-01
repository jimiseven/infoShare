<?php
declare(strict_types=1);

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Url::redirect('dashboard');
        }
        View::render('auth/login', ['title' => 'Iniciar sesion']);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (Auth::isLoginBlocked($email)) {
            $seconds = Auth::blockedSecondsRemaining($email);
            $minutes = (int)ceil($seconds / 60);
            Flash::set('danger', 'Demasiados intentos. Intenta de nuevo en ' . $minutes . ' minuto(s).');
            Url::redirect('login');
        }

        if ($email === '' || $password === '') {
            Flash::set('danger', 'Email y contrasena son obligatorios.');
            Url::redirect('login');
        }

        if (!Auth::attempt($email, $password)) {
            Flash::set('danger', 'Credenciales invalidas.');
            Url::redirect('login');
        }

        Flash::set('success', 'Bienvenido al sistema.');
        Url::redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        Url::redirect('login');
    }
}
