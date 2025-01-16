<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Firebase\JWT\JWT;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private string $jwtSecret;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, string $jwtSecret)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtSecret = $jwtSecret;
    }

    public function createUser(string $username, string $password, string $displayName): User
    {
        $repository = $this->entityManager->getRepository(User::class);

        if ($repository->findOneBy(['username' => $username])) {
            throw new \Exception('Username already exists');
        }

        $user = new User();
        $user->setUsername($username);
        $user->setDisplayName($displayName);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function authenticate(string $username, string $password): User
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $username]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        return $user;
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new \Exception('Invalid current password');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();
    }

    // public function generateToken(User $user): string
    // {
    //     $payload = [
    //         'id' => $user->getId(),
    //         'username' => $user->getUsername(),
    //         'exp' => time() + 86400, // Token valid for 24 hours
    //     ];

    //     return JWT::encode($payload, $this->jwtSecret, 'HS256');
    // }
}
