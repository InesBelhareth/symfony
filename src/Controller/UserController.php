<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\FavoriteService;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(UserService $userService,  EntityManagerInterface $entityManager)
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
                new Assert\NotBlank(['message' => 'username is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'username minimum 8 characters']),
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'password is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'password minimum 8 characters']),
            ],
            'confirmPassword' => [
                new Assert\NotBlank(['message' => 'confirmPassword is required']),
                new Assert\Callback(function ($value, $context) use ($data) {
                    if ($value !== ($data['password'] ?? null)) {
                        $context->buildViolation('confirmPassword not match')->addViolation();
                    }
                }),
            ],
            'displayName' => [
                new Assert\NotBlank(['message' => 'displayName is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'displayName minimum 8 characters']),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($data['username'], $data['password'], $data['displayName']);
            return $this->json([
                'message' => 'User created successfully',
                // 'token' => $this->userService->generateToken($user),
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
                new Assert\NotBlank(['message' => 'username is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'username minimum 8 characters']),
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'password is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'password minimum 8 characters']),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->authenticate($data['username'], $data['password']);
            
            return $this->json([
                'message' => 'Login successful',
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

    #[Route('/user/update-password', name: 'user_update_password', methods: ['PUT'])]
    public function updatePassword(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'password' => [
                new Assert\NotBlank(['message' => 'password is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'password minimum 8 characters']),
            ],
            'newPassword' => [
                new Assert\NotBlank(['message' => 'newPassword is required']),
                new Assert\Length(['min' => 8, 'minMessage' => 'newPassword minimum 8 characters']),
            ],
            'confirmNewPassword' => [
                new Assert\NotBlank(['message' => 'confirmNewPassword is required']),
                new Assert\Callback(function ($value, $context) use ($data) {
                    if ($value !== ($data['newPassword'] ?? null)) {
                        $context->buildViolation('confirmNewPassword not match')->addViolation();
                    }
                }),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            return $this->json(['errors' => (string)$violations], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->userService->updatePassword($user, $data['password'], $data['newPassword']);
            return $this->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/info', name: 'user_info', methods: ['GET'])]
    public function getInfo(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
        ]);
    }

//     #[Route('/favorites', name: 'get_favorites', methods: ['GET'])]
//     public function getFavorites(): JsonResponse
//     {
//         $user = $this->getUser();

//         if (!$user instanceof User) {
//             return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
//         }

//         $favorites = $this->favoriteService->getFavoritesOfUser($user);

//         return $this->json($favorites);
//     }

//     #[Route('/favorites', name: 'add_favorite', methods: ['POST'])]
//     public function addFavorite(Request $request, ValidatorInterface $validator): JsonResponse
//     {
//         $data = json_decode($request->getContent(), true);

//         $constraints = new Assert\Collection([
//             'mediaType' => [
//                 new Assert\NotBlank(['message' => 'mediaType is required']),
//                 new Assert\Choice(['choices' => ['movie', 'tv'], 'message' => 'mediaType invalid']),
//             ],
//             'mediaId' => [
//                 new Assert\NotBlank(['message' => 'mediaId is required']),
//             ],
//             'mediaTitle' => [
//                 new Assert\NotBlank(['message' => 'mediaTitle is required']),
//             ],
//             'mediaPoster' => [
//                 new Assert\NotBlank(['message' => 'mediaPoster is required']),
//             ],
//             'mediaRate' => [
//                 new Assert\NotBlank(['message' => 'mediaRate is required']),
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
//             $favorite = $this->favoriteService->addFavorite($user, $data);
//             return $this->json($favorite, Response::HTTP_CREATED);
//         } catch (\Exception $e) {
//             return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
//         }
//     }

//     #[Route('/favorites/{favoriteId}', name: 'remove_favorite', methods: ['DELETE'])]
//     public function removeFavorite(string $favoriteId): JsonResponse
//     {
//         $user = $this->getUser();

//         if (!$user instanceof User) {
//             return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
//         }

//         try {
//             $this->favoriteService->removeFavorite($user, $favoriteId);
//             return $this->json(['message' => 'Favorite removed successfully']);
//         } catch (\Exception $e) {
//             return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
//         }
//     }
}
