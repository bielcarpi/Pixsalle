<?php
declare(strict_types=1);

namespace Salle\PixSalle\Model;

use DateTime;

class User{

    private int $id;
    private string $email;
    private string $password;
    private string $username;
    private string $phoneNumber;
    private string $profilePic;
    private Datetime $createdAt;
    private Datetime $updatedAt;

    public function __construct(
        ?int $id,
        string $email,
        string $password,
        string $username,
        string $phoneNumber,
        ?string $profilePic,
        Datetime $createdAt,
        Datetime $updatedAt
    ){
        if($id !== null) $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
        $this->phoneNumber = $phoneNumber;
        if($profilePic !== null) $this->profilePic = $profilePic;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function validMembershipValue($membership): bool{
        if($membership == 0 || $membership == 1) return true;
        return false;
    }

    public function id(): int{
        return $this->id;
    }

    public function email(): string{
        return $this->email;
    }

    public function password(): string{
        return $this->password;
    }

    public function username(): string{
        return $this->username;
    }

    public function phoneNumber(): string{
        return $this->phoneNumber;
    }

    public function profilePic(): ?string{
        return $this->profilePic;
    }

    public function createdAt(): DateTime{
        return $this->createdAt;
    }

    public function updatedAt(): DateTime{
        return $this->updatedAt;
    }
}
