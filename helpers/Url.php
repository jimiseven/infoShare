<?php
declare(strict_types=1);

class Url
{
    public static function path(string $route, array $query = []): string
    {
        $route = ltrim($route, '/');

        if (USE_CLEAN_URLS) {
            $url = (APP_BASE_PATH !== '' ? APP_BASE_PATH : '') . '/' . $route;
            if (!empty($query)) {
                $url .= '?' . http_build_query($query);
            }
            return $url;
        }

        $params = array_merge(['r' => $route], $query);
        $base = APP_BASE_PATH !== '' ? APP_BASE_PATH : '';
        return $base . '/index.php?' . http_build_query($params);
    }

    public static function redirect(string $route, array $query = []): void
    {
        header('Location: ' . self::path($route, $query));
        exit;
    }
}
