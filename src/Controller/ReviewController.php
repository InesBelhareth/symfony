<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reviews', name: 'review_')]
class ReviewController extends AbstractController
{
    #[Route('/', name: 'get_user_reviews', methods: ['GET'])]
    public function getReviewsOfUser(ReviewRepository $reviewRepository): JsonResponse
    {
        $user = $this->getUser();
        $reviews = $reviewRepository->findByUser($user->getUserIdentifier());

        return $this->json($reviews);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['mediaId'], $data['content'], $data['mediaType'], $data['mediaTitle'], $data['mediaPoster'])) {
            return $this->json(['error' => 'Invalid input'], 400);
        }

        $review = new Review();
        $review->setUser($this->getUser());
        $review->setContent($data['content']);
        $review->setMediaId($data['mediaId']);
        $review->setMediaType($data['mediaType']);
        $review->setMediaTitle($data['mediaTitle']);
        $review->setMediaPoster($data['mediaPoster']);

        $em->persist($review);
        $em->flush();

        return $this->json($review, 201);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function remove(Review $review, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if ($review->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $em->remove($review);
        $em->flush();

        return $this->json(null, 204);
    }
}
