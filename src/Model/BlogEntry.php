<?php
declare(strict_types=1);

namespace Salle\PixSalle\Model;


class BlogEntry{

    public int $id;
    public string $title;
    public string $content;
    public int $userId;

    public function __construct(
        int $id,
        string $title,
        string $content,
        int $userId
    ){
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->userId = $userId;
    }

    public function id(): int{
        return $this->id;
    }

    public function title(): string{
        return $this->title;
    }

    public function content(): string{
        return $this->content;
    }

    public function userId(): int{
        return $this->userId;
    }
}
