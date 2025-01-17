<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FavoriteService; // Assurez-vous que ce service est bien défini
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

#[Route('api/user/favorites')]
class FavoriteController extends AbstractController
{
    private FavoriteService $favoriteService;

    public function __construct(FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    #[Route('/{id}', name: 'remove_favorite_by_id', methods: ['DELETE'])]
    public function removeFavoriteById(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $favorite = $this->favoriteService->findFavoriteByIdAndUser($id, $user);

        if (!$favorite) {
            return $this->json(['error' => 'Favorite not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->favoriteService->removeFavorite($favorite);

        return $this->json(['message' => 'Favorite removed successfully']);
    }

    #[Route('/favorites', name: 'get_favorites', methods: ['GET'])]
    public function getFavorites(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $favorites = $this->favoriteService->getFavoritesOfUser($user);

        return $this->json($favorites);
    }

    #[Route('/favorites', name: 'add_favorite', methods: ['POST'])]
    public function addFavorite(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'mediaType' => [
                new Assert\NotBlank(['message' => 'mediaType is required']),
                new Assert\Choice(['choices' => ['movie', 'tv'], 'message' => 'mediaType invalid']),
            ],
            'mediaId' => [
                new Assert\NotBlank(['message' => 'mediaId is required']),
            ],
            'mediaTitle' => [
                new Assert\NotBlank(['message' => 'mediaTitle is required']),
            ],
            'mediaPoster' => [
                new Assert\NotBlank(['message' => 'mediaPoster is required']),
            ],
            'mediaRate' => [
                new Assert\NotBlank(['message' => 'mediaRate is required']),
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
            $favorite = $this->favoriteService->addFavorite($user, $data);
            return $this->json($favorite, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/favorites/{favoriteId}', name: 'remove_favorite_by_id_param', methods: ['DELETE'])]
    public function removeFavoriteByIdParam(string $favoriteId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->favoriteService->removeFavoriteById( $favoriteId,$user);
            return $this->json(['message' => 'Favorite removed successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
