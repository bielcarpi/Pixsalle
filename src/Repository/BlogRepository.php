<?php
declare(strict_types=1);

namespace Salle\PixSalle\Repository;


use Salle\PixSalle\Model\BlogEntry;

interface BlogRepository
{
    public function createBlogEntry(string $title, string $content, int $userId): BlogEntry;
    public function getBlogEntries(): array;
    public function getBlogEntry(int $entryId): ?BlogEntry;
    public function modifyBlogEntry(int $entryId, string $title, string $content): ?BlogEntry;
    public function deleteBlogEntry(int $entryId): bool;
}
