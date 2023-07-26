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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'front_page')]
class PageController extends AbstractController
{
    /**
     * Prévisualisation de la page en front.
     *
     * @param Page page
     *
     * @return Response front/page/preview.html.twig
     */
    #[Route('/preview/page/{slug}', name: '_preview', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function preview(Page $page): Response
    {
        return $this->render('front/page/preview.html.twig', compact('page'));
    }
}
