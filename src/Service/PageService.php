<?php

/**
 * Service de gestion des pages.
 *
 * Méthodes :
 * - create() : Création d'une page
 * - edit() : Modiciation d'une page
 */

declare(strict_types=1);

namespace App\Service;

use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class PageService
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em,
        private readonly PageRepository $pageRepository
    ) {
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * Création d'une page.
     *
     * @param Page page
     *
     * @return JsonResponse success
     */
    public function create($page): JsonResponse
    {
        $page->setCreatedAt(new \DateTimeImmutable());
        $page->setEditedAt(new \DateTimeImmutable());
        $this->pageRepository->save($page, true);

        return $this->json(['status' => 'success', 'message' => 'La page a été créée.']);
    }

    /**
     * Modiciation d'une page.
     *
     * @param Page page
     *
     * @return JsonResponse success
     */
    public function edit($page): JsonResponse
    {
        $page->setEditedAt(new \DateTimeImmutable());
        $this->pageRepository->save($page, true);

        return $this->json(['status' => 'success', 'message' => 'Les informations ont été enregistrées.']);
    }
}
