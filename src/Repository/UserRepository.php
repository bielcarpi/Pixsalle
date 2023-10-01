<?php

declare(strict_types=1);

namespace Salle\PixSalle\Repository;

use Salle\PixSalle\Model\User;

interface UserRepository {
    public function createUser(User $user): void;
    public function getUserByEmail(string $email);
    public function getUserMembership(string $userEmail): ?int;
    public function getUserFunds(string $userEmail): ?float;
    public function updateUserMembership(string $userEmail, int $newMembership): bool;
    public function updateUserFunds(string $userEmail, float $fundsToSum): bool;
    public function getPicByEmail(string $email);
    public function updateUserProfilePicture(string $email, string $fileName);
    public function updatePhoneNumber(string $userEmail, string $newPhoneNum);
    public function updatePassword(string $userEmail, string $newPassword);
    public function updateUsername(string $userEmail, string $newUsername);
    }
