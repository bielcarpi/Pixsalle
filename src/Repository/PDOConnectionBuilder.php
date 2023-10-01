<?php
declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use PDO;

final class PDOConnectionBuilder
{
    private const CONNECTION_STRING = 'mysql:host=%s;port=%s;dbname=%s';

    public function __construct() {
    }

    public function build(
        string $username,
        string $password,
        string $host,
        string $port,
        string $database
    ) {
        $pdoConnection = new PDO(
            sprintf(self::CONNECTION_STRING, $host, $port, $database),
            $username,
            $password
        );

        $pdoConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdoConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdoConnection;
    }
}
