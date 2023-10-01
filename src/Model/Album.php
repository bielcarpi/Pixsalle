<?php
declare(strict_types=1);

namespace Salle\PixSalle\Model;


class Album{

    private int $id;
    private int $portfolioId;
    private string $title;
    private array $photos; //Array of links
    private string $author;

    public function __construct(
        int $id,
        int $portfolioId,
        string $title,
        array $photos,
        ?string $author = null
    ){
        $this->id = $id;
        $this->portfolioId = $portfolioId;
        $this->title = $title;
        $this->photos = $photos;
        if($author !== null) $this->author = $author;
    }

    public function id(): int{
        return $this->id;
    }

    public function portfolioId(): int{
        return $this->portfolioId;
    }

    public function title(): string{
        return $this->title;
    }

    public function photos(): array{
        return $this->photos;
    }

    public function author(): ?string{
        return $this->author;
    }
}
