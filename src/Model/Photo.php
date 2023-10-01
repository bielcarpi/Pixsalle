<?php
declare(strict_types=1);

namespace Salle\PixSalle\Model;


class Photo{

    private int $id;
    private string $link;
    private int $albumId;
    private int $portfolioId;
    private string $author;

    public function __construct(
        int $id,
        string $link,
        ?int $albumId = null,
        ?int $portfolioId = null,
        ?string $author = null
    ){
        $this->id = $id;
        $this->link = $link;
        if($albumId !== null) $this->albumId = $albumId;
        if($portfolioId !== null) $this->portfolioId = $portfolioId;
        if($author !== null) $this->author = $author;
    }

    public function id(): int{
        return $this->id;
    }

    public function link(): string{
        return $this->link;
    }

    public function albumId(): int{
        return $this->albumId;
    }

    public function portfolioId(): int{
        return $this->portfolioId;
    }

    public function author(): string{
        return $this->author;
    }
}
