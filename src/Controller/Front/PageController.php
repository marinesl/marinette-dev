<?php

/**
 * Controller permettant d'afficher les pages en front.
 *
 * Méthodes :
 * - preview() : Prévisualisation de la page en front
 */

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/', name: 'front_page')]
class PageController extends AbstractController
{
    /**
     * Prévisualisation de la page en front.
     *
     * @param Page page
     *
     * @return Response front/page/preview.twig
     */
    #[Route('/preview/page/{slug}', name: '_preview', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function preview(Page $page): Response
    {
        return $this->render('front/page/preview.twig', compact('page'));
    }
}
