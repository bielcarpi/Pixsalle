<?php

declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use PDO;
use Salle\PixSalle\Model\BlogEntry;

final class MySQLBlogRepository implements BlogRepository
{
    private PDO $databaseConnection;

    public function __construct(PDO $database){
        $this->databaseConnection = $database;
    }


    public function createBlogEntry(string $title, string $content, int $userId): BlogEntry
    {
        $query = <<<'QUERY'
        INSERT INTO blog_entries(user_id, title, content)
        VALUES(:user_id, :title, :content)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('user_id', $userId, PDO::PARAM_INT);
        $statement->bindParam('title', $title, PDO::PARAM_STR);
        $statement->bindParam('content', $content, PDO::PARAM_STR);
        $statement->execute();
        $blogId = $this->databaseConnection->lastInsertId();

        return new BlogEntry(intval($blogId), $title, $content, $userId);
    }

    public function getBlogEntries(): array
    {
        $query = <<<'QUERY'
        SELECT * FROM blog_entries;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->execute();

        $count = $statement->rowCount();
        $entries = [];

        for($i = 0; $i < $count; $i++){
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            array_push($entries, new BlogEntry(intval($row['id']), $row['title'], $row['content'], intval($row['user_id'])));
        }

        return $entries;
    }

    public function getBlogEntry(int $entryId): ?BlogEntry
    {
        $query = <<<'QUERY'
        SELECT * FROM blog_entries WHERE id = :id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('id', $entryId, PDO::PARAM_INT);
        $statement->execute();

        $count = $statement->rowCount();

        if($count > 0){
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return new BlogEntry(intval($row['id']), $row['title'], $row['content'], intval($row['user_id']));
        }
        return null;
    }

    public function modifyBlogEntry(int $entryId, string $title, string $content): ?BlogEntry
    {
        $query = <<<'QUERY'
        UPDATE blog_entries 
        SET title = :title, content = :content
        WHERE id = :id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('id', $entryId, PDO::PARAM_INT);
        $statement->bindParam('title', $title, PDO::PARAM_STR);
        $statement->bindParam('content', $content, PDO::PARAM_STR);
        $statement->execute();
        $count = $statement->rowCount();

        //If the update has been successful, select the user id of that blog entry
        if($count > 0){
            $query = <<<'QUERY'
            SELECT user_id FROM blog_entries 
            WHERE id = :id
            QUERY;

            $statement = $this->databaseConnection->prepare($query);
            $statement->bindParam('id', $entryId, PDO::PARAM_INT);
            $statement->execute();
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return new BlogEntry($entryId, $title, $content, intval($row['user_id']));
        }

        return null;
    }

    public function deleteBlogEntry(int $entryId): bool
    {
        $query = <<<'QUERY'
        DELETE FROM blog_entries WHERE id = :id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('id', $entryId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }
}
