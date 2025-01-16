<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/favorites')]
class FavoriteController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FavoriteRepository $favoriteRepository;

    public function __construct(EntityManagerInterface $entityManager, FavoriteRepository $favoriteRepository)
    {
        $this->entityManager = $entityManager;
        $this->favoriteRepository = $favoriteRepository;
    }




    #[Route('/{id}', name: 'remove_favorite', methods: ['DELETE'])]
    public function removeFavorite(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $favorite = $this->favoriteRepository->findOneBy([
            'id' => $id,
            'user' => $user,
        ]);

        if (!$favorite) {
            return $this->json(['error' => 'Favorite not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($favorite);
        $this->entityManager->flush();

        return $this->json(['message' => 'Favorite removed successfully']);
    }
}
