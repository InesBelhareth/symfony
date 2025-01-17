<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\Environment\Console;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

#[Route('/api')]
class UserController extends AbstractController
{
    private UserService $userService;
    private EntityManagerInterface $entityManager;

    public function __construct(UserService $userService, EntityManagerInterface $entityManager)
    {
        $this->userService = $userService;
        $this->entityManager = $entityManager;
    }

    #[Route('/user/signup', name: 'user_signup', methods: ['POST'])]
    public function signup(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'username' => [
                new Assert\NotBlank(['message' => 'Le nom d’utilisateur est obligatoire.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le nom d’utilisateur doit comporter au moins 8 caractères.']),
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le mot de passe doit comporter au moins 8 caractères.']),
            ],
            'confirmPassword' => [
                new Assert\NotBlank(['message' => 'La confirmation du mot de passe est obligatoire.']),
                new Assert\Callback(function ($value, $context) use ($data) {
                    if ($value !== ($data['password'] ?? null)) {
                        $context->buildViolation('La confirmation du mot de passe ne correspond pas.')->addViolation();
                    }
                }),
            ],
            'displayName' => [
                new Assert\NotBlank(['message' => 'Le nom affiché est obligatoire.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le nom affiché doit comporter au moins 8 caractères.']),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($data['username'], $data['password'], $data['displayName']);
            return $this->json([
                'message' => 'Utilisateur créé avec succès.',
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'displayName' => $user->getDisplayName(),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/signin', name: 'user_signin', methods: ['POST'])]
    public function signin(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'username' => [
                new Assert\NotBlank(['message' => 'Le nom d’utilisateur est obligatoire.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le nom d’utilisateur doit comporter au moins 8 caractères.']),
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le mot de passe doit comporter au moins 8 caractères.']),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->authenticate($data['username'], $data['password']);
            return $this->json([
                'message' => 'Connexion réussie.',
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'displayName' => $user->getDisplayName(),
                ]
            ]);
        } catch (AuthenticationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/user/{id}/update-password', name: 'user_update_password', methods: ['PUT'])]
    public function updatePassword(string $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $constraints = new Assert\Collection([
            'password' => [
                new Assert\NotBlank(['message' => 'L’ancien mot de passe est obligatoire.']),
            ],
            'newPassword' => [
                new Assert\NotBlank(['message' => 'Le nouveau mot de passe est obligatoire.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le nouveau mot de passe doit comporter au moins 8 caractères.']),
            ],
            'confirmNewPassword' => [
                new Assert\NotBlank(['message' => 'La confirmation du nouveau mot de passe est obligatoire.']),
                new Assert\Callback(function ($value, $context) use ($data) {
                    if ($value !== ($data['newPassword'] ?? null)) {
                        $context->buildViolation('La confirmation du mot de passe ne correspond pas.')->addViolation();
                    }
                }),
            ],
        ]);
    
        $violations = $validator->validate($data, $constraints);
    
        if (count($violations) > 0) {
            return $this->json(['errors' => (string) $violations], Response::HTTP_BAD_REQUEST);
        }
    
        $user = $this->userService->getUser($id);
    
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur non trouvé ou non autorisé.'], Response::HTTP_UNAUTHORIZED);
        }
    
        try {
            $this->userService->updatePassword($user, $data['password'], $data['newPassword']);
            return $this->json(['message' => 'Mot de passe mis à jour avec succès.']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    #[Route('/user/{id}/info', name: 'user_info', methods: ['GET'])]
    public function getInfo(string $id): JsonResponse
    {
        $user = $this->userService->getUser($id);
    
        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur non trouvé ou non autorisé.'], Response::HTTP_UNAUTHORIZED);
        }
    
        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
        ]);
    }
    
}


// namespace App\Controller;

// use App\Entity\User;
// use App\Service\UserService;
// use App\Service\FavoriteService;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Component\Validator\Validator\ValidatorInterface;
// use Symfony\Component\Security\Core\Exception\AuthenticationException;

// #[Route('/api')]
// class UserController extends AbstractController
// {
//     private UserService $userService;
//     private EntityManagerInterface $entityManager;

//     public function __construct(UserService $userService,  EntityManagerInterface $entityManager)
//     {
//         $this->userService = $userService;
//         $this->entityManager = $entityManager;
//     }

//     #[Route('/user/signup', name: 'user_signup', methods: ['POST'])]
//     public function signup(Request $request, ValidatorInterface $validator): JsonResponse
//     {
//         $data = json_decode($request->getContent(), true);

//         $constraints = new Assert\Collection([
//             'username' => [
//                 new Assert\NotBlank(['message' => 'username is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'username minimum 8 characters']),
//             ],
//             'password' => [
//                 new Assert\NotBlank(['message' => 'password is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'password minimum 8 characters']),
//             ],
//             'confirmPassword' => [
//                 new Assert\NotBlank(['message' => 'confirmPassword is required']),
//                 new Assert\Callback(function ($value, $context) use ($data) {
//                     if ($value !== ($data['password'] ?? null)) {
//                         $context->buildViolation('confirmPassword not match')->addViolation();
//                     }
//                 }),
//             ],
//             'displayName' => [
//                 new Assert\NotBlank(['message' => 'displayName is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'displayName minimum 8 characters']),
//             ],
//         ]);

//         $violations = $validator->validate($data, $constraints);

//         if (count($violations) > 0) {
//             return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
//         }

//         try {
//             $user = $this->userService->createUser($data['username'], $data['password'], $data['displayName']);
//             return $this->json([
//                 'message' => 'User created successfully',
//                 // 'token' => $this->userService->generateToken($user),
//                 'user' => [
//                     'id' => $user->getId(),
//                     'username' => $user->getUsername(),
//                     'displayName' => $user->getDisplayName(),
//                 ]
//             ], Response::HTTP_CREATED);
//         } catch (\Exception $e) {
//             return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
//         }
//     }

//     #[Route('/user/signin', name: 'user_signin', methods: ['POST'])]
//     public function signin(Request $request, ValidatorInterface $validator): JsonResponse
//     {
//         $data = json_decode($request->getContent(), true);

//         $constraints = new Assert\Collection([
//             'username' => [
//                 new Assert\NotBlank(['message' => 'username is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'username minimum 8 characters']),
//             ],
//             'password' => [
//                 new Assert\NotBlank(['message' => 'password is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'password minimum 8 characters']),
//             ],
//         ]);

//         $violations = $validator->validate($data, $constraints);

//         if (count($violations) > 0) {
//             return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
//         }

//         try {
//             $user = $this->userService->authenticate($data['username'], $data['password']);
//             return $this->json([
//                 'message' => 'Login successful',
//                 // 'token' => $this->userService->generateToken($user),
//                 'user' => [
//                     'id' => $user->getId(),
//                     'username' => $user->getUsername(),
//                     'displayName' => $user->getDisplayName(),
//                 ]
//             ]);
//         } catch (AuthenticationException $e) {
//             return $this->json(['error' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
//         }
//     }

//     #[Route('/user/update-password', name: 'user_update_password', methods: ['PUT'])]
//     public function updatePassword(Request $request, ValidatorInterface $validator): JsonResponse
//     {
//         $data = json_decode($request->getContent(), true);

//         $constraints = new Assert\Collection([
//             'password' => [
//                 new Assert\NotBlank(['message' => 'password is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'password minimum 8 characters']),
//             ],
//             'newPassword' => [
//                 new Assert\NotBlank(['message' => 'newPassword is required']),
//                 new Assert\Length(['min' => 8, 'minMessage' => 'newPassword minimum 8 characters']),
//             ],
//             'confirmNewPassword' => [
//                 new Assert\NotBlank(['message' => 'confirmNewPassword is required']),
//                 new Assert\Callback(function ($value, $context) use ($data) {
//                     if ($value !== ($data['newPassword'] ?? null)) {
//                         $context->buildViolation('confirmNewPassword not match')->addViolation();
//                     }
//                 }),
//             ],
//         ]);

//         $violations = $validator->validate($data, $constraints);

//         if (count($violations) > 0) {
//             return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
//         }

//         $user = $this->getUser();

//         if (!$user instanceof User) {
//             return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
//         }

//         try {
//             $this->userService->updatePassword($user, $data['password'], $data['newPassword']);
//             return $this->json(['message' => 'Password updated successfully']);
//         } catch (\Exception $e) {
//             return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
//         }
//     }

//     #[Route('/user/info', name: 'user_info', methods: ['GET'])]
//     public function getInfo(): JsonResponse
//     {
//         $user = $this->getUser();

//         if (!$user instanceof User) {
//             return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
//         }

//         return $this->json([
//             'id' => $user->getId(),
//             'username' => $user->getUsername(),
//             'displayName' => $user->getDisplayName(),
//         ]);
//     }} 