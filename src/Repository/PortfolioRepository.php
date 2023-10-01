<?php
declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use Salle\PixSalle\Model\Album;
use Salle\PixSalle\Model\Portfolio;

interface PortfolioRepository
{
    public function createPortfolio(string $userLoggedInEmail, Portfolio $portfolio): bool;
    public function getPortfolio(int $portfolioId): ?Portfolio; //For optimization purposes, its albums attributes contain only the first photo. If you want the whole album with all the photos, use getAlbumFromPortfolio()
    public function deletePortfolio(string $userLoggedInEmail): bool;

    public function getRecommendedAlbums(): array; //Returns an array (length 4 currently) of recommended albums
    public function addAlbumToPortfolio(string $userLoggedInEmail, Album $album): bool;
    public function getAlbum(int $albumId): ?Album;
    public function removeAlbumFromPortfolio(string $userLoggedInEmail, int $albumId): bool;

    public function addPhotoToAlbum(string $userLoggedInEmail, int $albumId, string $photoLink): int;
    public function removePhotoFromAlbum(string $userLoggedInEmail, int $albumId, int $photoId): int;

    public function getSystemPhotos(int $offset=0, int $items=-1): array;
}
