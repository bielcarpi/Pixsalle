<?php

declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use PDO;
use Salle\PixSalle\Model\Album;
use Salle\PixSalle\Model\Photo;
use Salle\PixSalle\Model\Portfolio;

final class MySQLPortfolioRepository implements PortfolioRepository
{
    private PDO $databaseConnection;

    public function __construct(PDO $database){
        $this->databaseConnection = $database;
    }


    public function createPortfolio(string $userLoggedInEmail, Portfolio $portfolio): bool
    {
        $query = <<<'QUERY'
        INSERT INTO portfolio(user_id, title, description)
        VALUES((SELECT id FROM users WHERE email = :email), :title, :description)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $title = $portfolio->title();
        $description = $portfolio->description();

        $statement->bindParam('email', $userLoggedInEmail, PDO::PARAM_STR);
        $statement->bindParam('title', $title, PDO::PARAM_STR);
        $statement->bindParam('description', $description, PDO::PARAM_STR);

        return $statement->execute();
    }


    public function getPortfolio(int $portfolioId): ?Portfolio
    {
        $query = <<<'QUERY'
        SELECT user_id, email, title, description FROM portfolio AS p
        INNER JOIN users AS u ON p.user_id = u.id
        WHERE u.id = :portfolio_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('portfolio_id', $portfolioId, PDO::PARAM_INT);
        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            $portfolio_id = $row['user_id'];
            $portfolio_author = $row['email'];
            $portfolio_title = $row['title'];
            $portfolio_description = $row['description'];

            //With the portfolioId, get all the albums of the portfolio
            $query = <<<'QUERY'
            SELECT * FROM album AS a
            LEFT JOIN photo AS p ON p.album_id = a.id
            WHERE a.portfolio_id = :portfolio_id
                AND p.id IN (
                    SELECT min(p.id) FROM photo AS p
                    INNER JOIN album AS a ON a.id = p.album_id
                    WHERE a.portfolio_id = :portfolio_id
                    GROUP BY a.id
                );
            QUERY;

            $statement = $this->databaseConnection->prepare($query);
            $statement->bindParam('portfolio_id', $portfolio_id, PDO::PARAM_INT);
            $statement->execute();

            $albums = [];
            $count = $statement->rowCount();
            for($i = 0; $i < $count; $i++){
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                array_push($albums, new Album(intval($row['album_id']), $portfolioId, $row['title'], array($row['link'])));
            }

            return new Portfolio($portfolio_title, $portfolio_author, $portfolio_description, $albums);
        }

        return null;
    }


    public function deletePortfolio(string $userLoggedInEmail): bool
    {
        // TODO: Implement deletePortfolio() method.
        return false;
    }


    public function getRecommendedAlbums(): array{
        //Currently, returns 4 random albums
        //In the future, some system could be implemented to evaluate the punctuation or visits of an album

        $query = <<<'QUERY'
        SELECT a.id, a.portfolio_id, a.title, p.link FROM album AS a
        LEFT JOIN photo AS p ON p.album_id = a.id
            AND p.id IN (
                SELECT min(p.id) FROM photo AS p
                INNER JOIN album AS a2 ON a2.id = p.album_id
                WHERE a2.portfolio_id = a.portfolio_id
                GROUP BY a2.id
            )
        ORDER BY rand()
        LIMIT 4
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->execute();

        $albums = [];
        $count = $statement->rowCount();
        for($i = 0; $i < $count; $i++){
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            array_push($albums, new Album(intval($row['id']), intval($row['portfolio_id']), $row['title'], array($row['link'])));
        }

        return $albums;
    }


    public function addAlbumToPortfolio(string $userLoggedInEmail, Album $album): bool
    {
        $query = "
            INSERT INTO album(portfolio_id, title)
            VALUES((SELECT id FROM users WHERE email = :email), :title);
            INSERT INTO photo(album_id, link)
            VALUES((SELECT LAST_INSERT_ID()), :link);
        ";

        $statement = $this->databaseConnection->prepare($query);

        $title = $album->title();
        $firstPhotoLink = $album->photos()[0];

        $statement->bindParam('email', $userLoggedInEmail, PDO::PARAM_STR);
        $statement->bindParam('title', $title, PDO::PARAM_STR);
        $statement->bindParam('link', $firstPhotoLink, PDO::PARAM_STR);

        return $statement->execute();
    }


    public function getAlbum(int $albumId): ?Album{
        $query = <<<'QUERY'
        SELECT a.title, p.id, p.link, u.email, po.user_id
        FROM album AS a
        INNER JOIN photo AS p ON p.album_id = a.id
        INNER JOIN portfolio AS po ON po.user_id = a.portfolio_id
        INNER JOIN users AS u ON u.id = po.user_id
        WHERE a.id = :album_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('album_id', $albumId, PDO::PARAM_INT);
        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = null;
            $photos = [];
            for($i = 0; $i < $count; $i++){
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                array_push($photos, new Photo(intval($row['id']), $row['link']));
            }

            return new Album($albumId, intval($row['user_id']), $row['title'], $photos, $row['email']);
        }

        return null;
    }


    public function removeAlbumFromPortfolio(string $userLoggedInEmail, int $albumId): bool
    {
        // TODO: Implement removeAlbumFromPortfolio() method.
        return false;
    }


    public function addPhotoToAlbum(string $userLoggedInEmail, int $albumId, string $photoLink): int
    {
        //Check if the user has permission to add to this album. If not, return
        if(!$this->isAlbumFromUser($albumId, $userLoggedInEmail)) return 1;

        //If it has permission, insert
        $query = <<<'QUERY'
        INSERT INTO photo(album_id, link) 
        VALUES (:album_id, :link)
        QUERY;


        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('album_id', $albumId, PDO::PARAM_INT);
        $statement->bindParam('link', $photoLink, PDO::PARAM_STR);
        $success = $statement->execute();

        if($success) return 0; //Everything ok
        return 2; //Internal Error. Can't add the photo
    }


    public function removePhotoFromAlbum(string $userLoggedInEmail, int $albumId, int $photoId): int
    {
        //Check if the user has permission to add to this album. If not, return
        if(!$this->isAlbumFromUser($albumId, $userLoggedInEmail)) return 1;

        //Check how many photos has the album. If the album has only one photo, don't delete it
        $query = <<<'QUERY'
        SELECT COUNT(p.id) AS num_photos
        FROM album AS a
        INNER JOIN photo AS p ON p.album_id = a.id
        WHERE a.id = :album_id
        GROUP BY a.id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('album_id', $albumId, PDO::PARAM_INT);
        $statement->execute();
        if($statement->rowCount() > 0){
            $numPhotos = $statement->fetch(PDO::FETCH_ASSOC)['num_photos'];
            if($numPhotos <= 1) return 2;
        }
        else return 3;


        //If everything is OK, delete
        $query = <<<'QUERY'
        DELETE FROM photo
        WHERE id = :photo_id
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('photo_id', $photoId, PDO::PARAM_INT);
        $success = $statement->execute();

        if($success) return 0; //Everything ok
        return 3; //Internal Error. Can't delete the photo
    }


    public function getSystemPhotos(int $offset = 0, int $items = -1): array
    {
        //In a future, offset and items could be used as a way not to load all the photos at the same time
        //Load more of them just when the user scrolls down, for example
        $query = <<<'QUERY'
        SELECT p.id AS id, link, p.album_id AS album_id, u.email AS email, po.user_id
        FROM photo AS p
        INNER JOIN album AS a ON a.id = p.album_id
        INNER JOIN portfolio AS po ON po.user_id = a.portfolio_id
        INNER JOIN users AS u ON u.id = po.user_id;
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->execute();

        $photos = [];
        $rowCount = $statement->rowCount();
        for($i = 0; $i < $rowCount; $i++){
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            array_push($photos, new Photo(intval($row['id']), $row['link'], intval($row['album_id']), intval($row['user_id']), $row['email']));
        }

        return $photos;
    }



    private function isAlbumFromUser(int $albumId, string $userLoggedInEmail): bool{
        //Check if the album id passed is an album from the user
        $query = <<<'QUERY'
        SELECT *
        FROM album AS a
        INNER JOIN portfolio AS p ON p.user_id = a.portfolio_id
        INNER JOIN users AS u ON u.id = p.user_id
        WHERE a.id = :album_id
            AND u.email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('album_id', $albumId, PDO::PARAM_INT);
        $statement->bindParam('email', $userLoggedInEmail, PDO::PARAM_STR);
        $statement->execute();

        if($statement->rowCount() > 0) return true;
        return false;
    }
}
