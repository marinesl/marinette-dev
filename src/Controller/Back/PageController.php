<?php

/**
 * Controller permettant de gérer les pages
 * 
 * Méthodes :
 * - list() : La liste des pages qui n'ont pas le statut "Corbeille"
 * - listCorbeille() : La liste des pages qui ont le statut "Corbeille"
 * - create() : Page de création d'une page
 * - edit() : Page de modification d'une page
 * - deleteConfirm() : Pop-up de confirmation de la suppression d'une ou plusieurs pages
 * - delete() : Suppression d'une ou plusieurs pages
 * - changeStatusConfirm() : Pop-up de confirmation du changement de status d'une ou plusieurs pages
 * - changeStatus() : Changement du statut d'une ou plusieurs pages
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Page;
use App\Form\Back\PageType;
use App\Repository\PageRepository;
use App\Service\ElementDeleteService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ElementChangeStatusService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/manager/page', name: 'back_page')]
class PageController extends AbstractController
{
    private $element_toString;
    private $is_female;
    private $pageLength;

    public function __construct()
    {
        $this->element_toString = "page";
        $this->is_female = true;
        $this->pageLength = 10;
    }

    /**
     * La liste des pages qui n'ont pas le statut "Corbeille"
     * 
     * @param PageRepository pageRepository
     * 
     * @return Response back/element/list.html.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(PageRepository $pageRepository): Response
    {
        // On récupère les pages dont le statut est différent de "Corbeille"
        $pages = $pageRepository->findNotCorbeille();

        // Pour le template
        $is_corbeille = false;

        return $this->render('back/element/list.html.twig', [
            'pages' => $pages,
            'element_toString' => $this->element_toString,
            'is_female' => $this->is_female,
            'pageLength' => $this->pageLength,
            'is_corbeille' => $is_corbeille
        ]);
    }


    /**
     * La liste des pages qui ont le statut "Corbeille"
     * 
     * @param PageRepository pageRepository
     * 
     * @return Response back/element/list.html.twig
     */
    #[Route('/corbeille', name: '_corbeille', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function listCorbeille(PageRepository $pageRepository): Response
    {
        // On récupère les pages dont le statut est "Corbeille"
        $pages = $pageRepository->findByStatus(4);

        // Pour le template
        $is_corbeille = true;

        return $this->render('back/element/list.html.twig', [
            'pages' => $pages,
            'element_toString' => $this->element_toString,
            'is_female' => $this->is_female,
            'pageLength' => $this->pageLength,
            'is_corbeille' => $is_corbeille
        ]);
    }


    /**
     * Page de création d'une page
     * 
     * @param bool is_preview pour savoir si l'utilisateur souhaite prévisualiser la page
     * @param EntityManagerInterface em
     * @param Request request
     * 
     * @return Response back/page/create_edit.html.twig
     */
    #[Route('/create/{is_preview}', name: '_create', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        bool $is_preview,
        EntityManagerInterface $em, 
        Request $request
    ): Response
    {
        // On crée une nouvelle page
        $page = new Page();

        // On récupère le formulaire
        $form = $this->createForm(PageType::class, $page);

        // On gère la requête du formulaire
        $form->handleRequest($request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $page->setCreatedAt(new \DateTimeImmutable());
            $page->setEditedAt(new \DateTimeImmutable());
            $em->persist($page);
            $em->flush();

            // Message flash
            $this->addFlash('success', "La page a été créée.");

            // On récupère si le bouton Visualiser a été cliqué
            $is_preview = ($form->get('preview')->isClicked()) ? 1 : 0;

            // Redirection vers la page de modification
            return $this->redirectToRoute('back_page_edit', [
                'slug' => $page->getSlug() ,
                'is_preview' => $is_preview
            ]);
        }

        return $this->render('back/page/create_edit.html.twig', [
            'form' => $form->createView(),
            'is_preview' => $is_preview
        ]);
    }


    /**
     * Page de modification d'une page
     * 
     * @param Page page
     * @param bool is_preview pour savoir si l'utilisateur souhaite prévisualiser la page
     * @param EntityManagerInterface em
     * @param Request request
     * 
     * @return Response back/page/create_edit.html.twig
     */
    #[Route('/edit/{slug}/{is_preview}', name: '_edit', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Page $page, 
        bool $is_preview,
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        // On récupère le formulaire
        $form = $this->createForm(PageType::class, $page);

        // On gère la requête du formulaire
        $form->handleRequest($request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $page->setEditedAt(new \DateTimeImmutable());
            $em->persist($page);
            $em->flush();

            // Message flash
            $this->addFlash('success', "Les informations ont été enregistrées.");

            // On récupère si le bouton Visualiser a été cliqué
            $is_preview = ($form->get('preview')->isClicked()) ? 1 : 0;

            // Redirection vers la page de modification
            return $this->redirectToRoute('back_page_edit', [
                'slug' => $page->getSlug(),
                'is_preview' => $is_preview
            ]);
        }

        return $this->render('back/page/create_edit.html.twig', [
            'page' => $page,
            'form' => $form->createView() ,
            'is_preview' => $is_preview
        ]);
    }


    /**
     * Pop-up de confirmation de la suppression d'une ou plusieurs pages
     * 
     * @param ElementDeleteService elementDeleteService
     * @param PageRepository pageRepository
     * 
     * @return Response back/_popup/_yes_no_popup.html.twig
     */
    #[Route('/delete/confirm', name: '_delete_confirm', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteConfirm(
        ElementDeleteService $elementDeleteService,
        PageRepository $pageRepository
    ): Response
    {
        // Service ElementDeleteService
        $message = $elementDeleteService->deleteConfirm($pageRepository, 'page');

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.html.twig', compact('message')),
                'titre' => 'Suppression'
            ])
        );
    }


    /**
     * Suppression d'une ou plusieurs pages
     * 
     * @param ElementDeleteService elementDeleteService
     * @param PageRepository pageRepository
     * 
     * @return Response back/page/list_corbeille.html.twig
     */
    #[Route('/delete', name: '_delete', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        ElementDeleteService $elementDeleteService,
        PageRepository $pageRepository
    ): Response
    {
        // Service ElementDeleteService
        $elementDeleteService->delete($pageRepository);

        return $this->redirectToRoute('back_page_corbeille');
    }


    /**
     * Pop-up de confirmation du changement de statut d'une ou plusieurs pages
     * 
     * @param ElementChangeStatusService elementChangeStatusService
     * @param PageRepository pageRepository
     * 
     * @return Response back/_popup/_yes_no_popup.html.twig
     */
    #[Route('/change_status/confirm', name: '_change_status_confirm', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatusConfirm(
        ElementChangeStatusService $elementChangeStatusService,
        PageRepository $pageRepository
    ): Response
    {
        // Service ElementChangeStatusService
        $message = $elementChangeStatusService->changeStatusConfirm($pageRepository, 'page');

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.html.twig', compact('message')),
                'titre' => 'Changement de statut'
            ])
        );
    }


    /**
     * Changement du statut d'une ou plusieurs pages
     * 
     * @param ElementChangeStatusService elementChangeStatusService
     * @param PageRepository pageRepository
     * 
     * @return Response back/page/list.html.twig
     */
    #[Route('/change_status', name: '_change_status', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatus(
        ElementChangeStatusService $elementChangeStatusService,
        PageRepository $pageRepository
    ): Response
    {
        // Service ElementChangeStatusService
        $elementChangeStatusService->changeStatus($pageRepository);

        return $this->redirectToRoute('back_page');
    }
}
