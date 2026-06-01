<?php
declare(strict_types=1);

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            header('Location: index.php?r=login');
            exit;
        }
    }
}
