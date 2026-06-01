<?php
declare(strict_types=1);

class RoleMiddleware
{
    public static function requireRoles(array $roles): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['rol'], $roles, true)) {
            http_response_code(403);
            $file = __DIR__ . '/../views/errors/403.php';
            if (file_exists($file)) {
                include $file;
                exit;
            }
            exit('No autorizado');
        }
    }
}
