<?php

namespace App\Service;

use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavoriteService
{
    private EntityManagerInterface $entityManager;
    private FavoriteRepository $favoriteRepository;

    public function __construct(EntityManagerInterface $entityManager, FavoriteRepository $favoriteRepository)
    {
        $this->entityManager = $entityManager;
        $this->favoriteRepository = $favoriteRepository;
    }

    /**
     * Ajouter un favori pour un utilisateur.
     *
     * @param User $user
     * @param array $data
     * @return Favorite
     * @throws \Exception
     */
    public function addFavorite(User $user, array $data): Favorite
    {
        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setMediaType($data['mediaType']);
        $favorite->setMediaId($data['mediaId']);
        $favorite->setMediaTitle($data['mediaTitle']);
        $favorite->setMediaPoster($data['mediaPoster']);
        $favorite->setMediaRate($data['mediaRate']);

        // Vérification si ce favori existe déjà
        $existingFavorite = $this->favoriteRepository->findOneBy([
            'user' => $user,
            'mediaId' => $data['mediaId'],
            'mediaType' => $data['mediaType'],
        ]);

        if ($existingFavorite) {
            throw new \Exception('Favorite already exists');
        }

        $this->entityManager->persist($favorite);
        $this->entityManager->flush();

        return $favorite;
    }

    /**
     * Supprimer un favori.
     *
     * @param Favorite $favorite
     * @return void
     */
    public function removeFavorite(Favorite $favorite): void
    {
        $this->entityManager->remove($favorite);
        $this->entityManager->flush();
    }

    /**
     * Supprimer un favori par ID et utilisateur.
     *
     * @param int $favoriteId
     * @param User $user
     * @return void
     */
    public function removeFavoriteById(int $favoriteId, User $user): void
    {
        $favorite = $this->favoriteRepository->findOneBy([
            'id' => $favoriteId,
            'user' => $user,
        ]);

        if (!$favorite) {
            throw new \Exception('Favorite not found');
        }

        $this->removeFavorite($favorite);
    }

    /**
     * Récupérer les favoris d'un utilisateur.
     *
     * @param User $user
     * @return array
     */
    public function getFavoritesOfUser(User $user): array
    {
        return $this->favoriteRepository->findBy(['user' => $user]);
    }

    /**
     * Trouver un favori par ID et utilisateur.
     *
     * @param int $favoriteId
     * @param User $user
     * @return Favorite|null
     */
    public function findFavoriteByIdAndUser(int $favoriteId, User $user): ?Favorite
    {
        return $this->favoriteRepository->findOneBy([
            'id' => $favoriteId,
            'user' => $user,
        ]);
    }
}
