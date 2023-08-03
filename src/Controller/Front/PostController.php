<?php

/**
 * Controller permettant d'afficher les posts en front.
 *
 * Méthodes :
 * - preview() : Prévisualisation du post en front
 */

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', name: 'front_post')]
class PostController extends AbstractController
{
    /**
     * Prévisualisation du post en front.
     *
     * @param Post post
     *
     * @return Response front/post/preview.twig
     */
    #[Route('/preview/post/{slug}', name: '_preview', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function preview(Post $post): Response
    {
        return $this->render('front/post/preview.twig', compact('post'));
    }
}
