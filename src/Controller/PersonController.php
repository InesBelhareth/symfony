<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/person', name: 'person_')]
class PersonController
{
    private TmdbApiService $tmdbApiService;

    public function __construct(TmdbApiService $tmdbApiService)
    {
        $this->tmdbApiService = $tmdbApiService;
    }

    #[Route('/{personId}', name: 'detail', methods: ['GET'])]
    public function personDetail(int $personId): JsonResponse
    {
        try {
            $person = $this->tmdbApiService->personDetail($personId);

            return new JsonResponse($person, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{personId}/medias', name: 'medias', methods: ['GET'])]
    public function personMedias(int $personId): JsonResponse
    {
        try {
            $medias = $this->tmdbApiService->personMedias($personId);

            return new JsonResponse($medias, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
