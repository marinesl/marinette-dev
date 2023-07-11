<?php 

/**
 * Service de gestion des posts
 * 
 * Méthodes :
 * - create() : Création d'un post
 */

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class PostService
{
    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly Request $request,
        private readonly EntityManagerInterface $em
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

        $this->em->persist($post);
        $this->em->flush();

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

        $this->em->persist($post);
        $this->em->flush();

        return $this->json([ 'status' => 'success', 'message' => 'Les informations ont été enregistrées.' ]);
    }
}