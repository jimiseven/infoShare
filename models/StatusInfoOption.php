<?php
declare(strict_types=1);

class StatusInfoOption
{
    public static function all(): array
    {
        self::ensureTable();
        self::seedDefaults();

        $stmt = Database::connection()->query('SELECT id, nombre FROM estado_info_opciones ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public static function createIfNotExists(string $name): void
    {
        self::ensureTable();
        $name = trim($name);
        if ($name === '') {
            return;
        }
        $stmt = Database::connection()->prepare('INSERT IGNORE INTO estado_info_opciones (nombre) VALUES (:nombre)');
        $stmt->execute(['nombre' => $name]);
    }

    private static function ensureTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS estado_info_opciones (
                    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    nombre varchar(255) NOT NULL,
                    created_at datetime DEFAULT current_timestamp(),
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_estado_info_nombre (nombre)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
        Database::connection()->exec($sql);
    }

    private static function seedDefaults(): void
    {
        self::createIfNotExists('Awaiting customer response');
        self::createIfNotExists('Awaiting HQ response');
    }
}
