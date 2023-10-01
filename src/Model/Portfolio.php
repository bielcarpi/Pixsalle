<?php
declare(strict_types=1);

namespace Salle\PixSalle\Model;


class Portfolio{

    private string $title;
    private string $author;
    private string $description;
    private array $albums; //Array of Album

    public function __construct(
        string $title,
        string $author,
        string $description,
        ?array $albums
    ){
        $this->title = $title;
        $this->author = $author;
        $this->description = $description;
        if($albums !== null) $this->albums = $albums;
    }

    public function title(): string{
        return $this->title;
    }

    public function author(): string{
        return $this->author;
    }

    public function description(): string{
        return $this->description;
    }

    public function albums(): array{
        return $this->albums;
    }
}
