<?php
declare(strict_types=1);

class Tag
{
    public static function all(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT id, nombre FROM tags ORDER BY nombre ASC')->fetchAll();
    }

    public static function idsByTicket(int $ticketId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT tag_id FROM ticket_tags WHERE ticket_id = :ticket_id');
        $stmt->execute(['ticket_id' => $ticketId]);
        return array_map(fn($r) => (int)$r['tag_id'], $stmt->fetchAll());
    }

    public static function syncTicketTags(int $ticketId, array $tagIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        $del = $pdo->prepare('DELETE FROM ticket_tags WHERE ticket_id = :ticket_id');
        $del->execute(['ticket_id' => $ticketId]);
        $ins = $pdo->prepare('INSERT INTO ticket_tags (ticket_id, tag_id) VALUES (:ticket_id, :tag_id)');
        foreach ($tagIds as $tagId) {
            $tid = (int)$tagId;
            if ($tid > 0) {
                $ins->execute(['ticket_id' => $ticketId, 'tag_id' => $tid]);
            }
        }
        $pdo->commit();
    }
}
