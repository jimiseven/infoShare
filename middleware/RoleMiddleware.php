<?php
declare(strict_types=1);

class RoleMiddleware
{
    public static function requireRoles(array $roles): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user['rol'], $roles, true)) {
            http_response_code(403);
            exit('No autorizado');
        }
    }
}
