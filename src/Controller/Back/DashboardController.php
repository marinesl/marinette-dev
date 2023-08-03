<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Repository\PostCategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/manager/dashboard', name: 'back_dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name: '')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        PostCategoryRepository $postCategoryRepository,
        PostRepository $postRepository
    ): Response {
        // On récupère les 10 derniers posts publiés ou mis à jour
        $posts = $postRepository->findLast10Edited();

        // On récupère le nombre de posts par catégorie
        $categories = $postCategoryRepository->findPublishOrderByAlpha();

        return $this->render('back/dashboard/index.twig', compact(['categories', 'posts']));
    }
}
