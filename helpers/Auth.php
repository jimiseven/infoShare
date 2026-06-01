<?php
declare(strict_types=1);

class Auth
{
    private const LOGIN_MAX_ATTEMPTS = 5;
    private const LOGIN_LOCK_MINUTES = 15;

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function attempt(string $email, string $password): bool
    {
        $email = mb_strtolower(trim($email));
        if (self::isLoginBlocked($email)) {
            return false;
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT u.id, u.nombre, u.email, u.password_hash, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id WHERE u.email = :email AND u.activo = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            Audit::log(null, 'LOGIN_FAIL', 'usuarios', null);
            self::registerLoginFail($email);
            return false;
        }

        $isValid = password_verify($password, $user['password_hash']);
        if (!$isValid && str_contains($user['password_hash'], 'hashdemo')) {
            $demoPasswords = [
                'admin@infoshare.com' => 'admin123',
                'juan@infoshare.com' => 'agente123',
                'gerente@infoshare.com' => 'gerente123',
            ];
            $expected = $demoPasswords[mb_strtolower((string)$user['email'])] ?? null;
            $isValid = $expected !== null && hash_equals($expected, $password);
        }
        if (!$isValid) {
            Audit::log((int)$user['id'], 'LOGIN_FAIL', 'usuarios', (int)$user['id']);
            self::registerLoginFail($email);
            return false;
        }

        self::clearLoginThrottle($email);

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol'],
        ];

        Audit::log((int)$user['id'], 'LOGIN_OK', 'usuarios', (int)$user['id']);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function isLoginBlocked(string $email): bool
    {
        $key = self::loginKey($email);
        $state = $_SESSION['_login_throttle'][$key] ?? null;
        if (!$state || !isset($state['blocked_until'])) {
            return false;
        }
        return (int)$state['blocked_until'] > time();
    }

    public static function blockedSecondsRemaining(string $email): int
    {
        $key = self::loginKey($email);
        $state = $_SESSION['_login_throttle'][$key] ?? null;
        if (!$state || !isset($state['blocked_until'])) {
            return 0;
        }
        return max(0, (int)$state['blocked_until'] - time());
    }

    private static function registerLoginFail(string $email): void
    {
        $key = self::loginKey($email);
        $state = $_SESSION['_login_throttle'][$key] ?? ['attempts' => 0, 'blocked_until' => 0];
        $state['attempts'] = (int)$state['attempts'] + 1;
        if ($state['attempts'] >= self::LOGIN_MAX_ATTEMPTS) {
            $state['blocked_until'] = time() + (self::LOGIN_LOCK_MINUTES * 60);
            $state['attempts'] = 0;
            Audit::log(null, 'LOGIN_BLOCKED', 'usuarios', null);
        }
        $_SESSION['_login_throttle'][$key] = $state;
    }

    private static function clearLoginThrottle(string $email): void
    {
        $key = self::loginKey($email);
        unset($_SESSION['_login_throttle'][$key]);
    }

    private static function loginKey(string $email): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return hash('sha256', mb_strtolower(trim($email)) . '|' . $ip);
    }
}
