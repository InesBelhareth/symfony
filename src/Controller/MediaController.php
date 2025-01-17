<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\UserRepository;
use App\Repository\FavoriteRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/{mediaType}')]
class MediaController extends AbstractController
{
    private TmdbApiService $tmdbApiService;
    private UserRepository $userRepository;
    private FavoriteRepository $favoriteRepository;
    private ReviewRepository $reviewRepository;

    public function __construct(
        TmdbApiService $tmdbApiService,
        UserRepository $userRepository,
        FavoriteRepository $favoriteRepository,
        ReviewRepository $reviewRepository
    ) {
        $this->tmdbApiService = $tmdbApiService;
        $this->userRepository = $userRepository;
        $this->favoriteRepository = $favoriteRepository;
        $this->reviewRepository = $reviewRepository;
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(string $mediaType, Request $request): JsonResponse
    {
        try {
            //$mediaType = $request->query->get('mediaType', '');
            $query = $request->query->get('query', '');
            $page = $request->query->get('page', 1);

            $response = $this->tmdbApiService->mediaSearch(
                $mediaType === 'people' ? 'person' : $mediaType,
                $query,
                $page
            );

            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Something went wrong'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/genres', name: 'genres', methods: ['GET'])]
    public function getGenres(string $mediaType, Request $request): JsonResponse
{
    try {
        //$mediaType = $request->query->get('mediaType', '');

        // Debug the incoming mediaType
        var_dump("Media Type: {$mediaType}");
        die; // Stop further execution to inspect the output

        $response = $this->tmdbApiService->mediaGenres($mediaType);

        return new JsonResponse($response, JsonResponse::HTTP_OK);
    } catch (\Exception $e) {
        //error_log('Error in getGenres method: ' . $e->getMessage());
        return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    #[Route('/detail/{mediaId}', name: 'detail', methods: ['GET'])]
    public function getDetail(string $mediaType, string $mediaId, Request $request): JsonResponse
    {
        try {
            //$mediaType = $request->query->get('mediaType', '');
            //$mediaId = $request->query->get('mediaId', '');
            
            $media = $this->tmdbApiService->mediaDetail($mediaType,$mediaId);

            $media['credits'] = $this->tmdbApiService->mediaCredits($mediaType,$mediaId);
            $media['videos'] = $this->tmdbApiService->mediaVideos($mediaType,$mediaId);
            $media['recommend'] = $this->tmdbApiService->mediaRecommend($mediaType,$mediaId)['results'];
            $media['images'] = $this->tmdbApiService->mediaImages($mediaType,$mediaId);

            $token = $request->headers->get('Authorization');
            if ($token) {
                $userId = $this->decodeToken($token); // Méthode pour décoder le token JWT
                $user = $this->userRepository->find($userId);

                if ($user) {
                    $isFavorite = $this->favoriteRepository->findOneBy(['user' => $user, 'mediaId' => $mediaId]);
                    $media['isFavorite'] = $isFavorite !== null;
                }
            }

            $reviews = $this->reviewRepository->findBy(['mediaId' => $mediaId], ['createdAt' => 'DESC']);
            $media['reviews'] = array_map(fn($review) => [
                'id' => $review->getId(),
                'content' => $review->getContent(),
                'user' => [
                    'id' => $review->getUser()->getId(),
                    'name' => $review->getUser()->getName(),
                ],
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $reviews);

            return new JsonResponse($media, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Something went wrong'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{mediaCategory}', name: 'list', methods: ['GET'])]
    public function getList(string $mediaType, string $mediaCategory, Request $request): JsonResponse
    {
        try {
            //$mediaType = $request->query->get('mediaType', '');
            $page = $request->query->get('page', 1);

            $response = $this->tmdbApiService->mediaList($mediaType, $mediaCategory, $page);

            return new JsonResponse($response, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Something went wrong'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function decodeToken(string $token): ?int
    {
        // Implémentez la logique pour décoder le token JWT et retourner l'ID utilisateur.
        return null;
    }
}
