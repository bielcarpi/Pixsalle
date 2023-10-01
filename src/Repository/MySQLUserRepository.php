<?php

declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use DateTime;
use PDO;
use Salle\PixSalle\Model\Membership;
use Salle\PixSalle\Model\User;
use Salle\PixSalle\Repository\UserRepository;

final class MySQLUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDO $databaseConnection;

    public function __construct(PDO $database){
        $this->databaseConnection = $database;
    }

    public function createUser(User $user): void{
        $query = <<<'QUERY'
        INSERT INTO users(email, password, username, phoneNumber, profilePic,createdAt, updatedAt)
        VALUES(:email, :password, :username, :phoneNumber, :profilePic,:createdAt, :updatedAt)
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $email = $user->email();
        $password = $user->password();
        $username = $user->username();
        $profilePic = $user->profilePic();
        $phoneNumber = $user->phoneNumber();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $updatedAt = $user->updatedAt()->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->bindParam('phoneNumber', $phoneNumber, PDO::PARAM_STR);
        $statement->bindParam('profilePic', $profilePic, PDO::PARAM_STR);
        $statement->bindParam('createdAt', $createdAt, PDO::PARAM_STR);
        $statement->bindParam('updatedAt', $updatedAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getUserByEmail(string $email){
        $query = <<<'QUERY'
        SELECT * FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('email', $email, PDO::FETCH_ASSOC);
        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            return new User(intval($row['id']), $row['email'], $row['password'], $row['username'], $row['phoneNumber'], $row['profilePic'], DateTime::createFromFormat("Y-m-d H:i:s", $row['createdAt']), DateTime::createFromFormat("Y-m-d H:i:s", $row['updatedAt']));
        }
        return null;
    }

    public function getUserMembership(string $userEmail): ?int{
        $query = <<<'QUERY'
        SELECT membership FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0)
            return intval($statement->fetch(PDO::FETCH_COLUMN));
        return null;
    }

    public function getUserFunds(string $userEmail): ?float{
        $query = <<<'QUERY'
        SELECT funds FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0)
            return floatval($statement->fetch(PDO::FETCH_COLUMN));
        return null;
    }

    public function updateUserMembership(string $userEmail, int $newMembership): bool{
        $query = <<<'QUERY'
        UPDATE users SET membership = :membership WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('membership', $newMembership, PDO::PARAM_INT);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        return $statement->execute();
    }

    public function updateUserFunds(string $userEmail, float $fundsToSum): bool{
        $currentFunds = $this->getUserFunds($userEmail);
        $currentFunds += $fundsToSum;

        $query = <<<'QUERY'
        UPDATE users SET funds = :currentFunds WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('currentFunds', $currentFunds, PDO::PARAM_INT);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        return $statement->execute();
    }

    public function getPicByEmail(string $email)
    {
        $query = <<<'QUERY'
        SELECT profilePic FROM users WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $count = $statement->rowCount();
        if ($count > 0) {
            $pic = $statement->fetchColumn();
            return $pic;
        }
        return null;
    }

    public function updateUserProfilePicture(string $userEmail, string $newFilename){
        $query = <<<'QUERY'
        UPDATE users SET profilePic = :filename WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('filename', $newFilename, PDO::PARAM_STR);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        return $statement->execute();
    }

    public function updateUsername(string $userEmail, string $newUsername){
        $query = <<<'QUERY'
        UPDATE users SET username = :username WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('username', $newUsername, PDO::PARAM_STR);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        return $statement->execute();
    }

    public function updatePhoneNumber(string $userEmail, string $newPhoneNum){
        $query = <<<'QUERY'
        UPDATE users SET phoneNumber = :phone WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('phone', $newPhoneNum, PDO::PARAM_STR);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        return $statement->execute();
    }

    public function updatePassword(string $userEmail, string $newPassword){
        $query = <<<'QUERY'
        UPDATE users SET password = :password WHERE email = :email
        QUERY;

        $statement = $this->databaseConnection->prepare($query);
        $statement->bindParam('password', $newPassword, PDO::PARAM_STR);
        $statement->bindParam('email', $userEmail, PDO::PARAM_STR);
        return $statement->execute();
    }
}
