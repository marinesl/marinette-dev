<?php 

/**
 * Service de gestion des posts
 * 
 * Méthodes :
 * - create() : Création d'un post
 */

declare(strict_types=1);

namespace App\Service;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class PostService
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em,
        private readonly PostRepository $postRepository
    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }


    /**
     * Création d'un post
     * 
     * @param Post post
     * 
     * @return JsonResponse success
     */
    public function create($post): JsonResponse
    {
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setEditedAt(new \DateTimeImmutable());
        $this->postRepository->save($post, true);

        return $this->json([ 'status' => 'success', 'message' => 'Le post a été créé.' ]);
    }


    /**
     * Modification d'un post
     * 
     * @param Post post
     * 
     * @return JsonResponse success
     */
    public function edit($post): JsonResponse
    {
        $post->setEditedAt(new \DateTimeImmutable());
        $this->postRepository->save($post, true);

        return $this->json([ 'status' => 'success', 'message' => 'Les informations ont été enregistrées.' ]);
    }
}