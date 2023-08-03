<?php

/**
 * Controller permettant de gérer les pages.
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
use App\Security\Voter\PageVoter;
use App\Service\ChangeStatusService;
use App\Service\DeleteService;
use App\Service\PageService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/manager/page', name: 'back_page')]
class PageController extends AbstractController
{
    private $request;

    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $request_stack,
        private readonly DeleteService $deleteService,
        private readonly ChangeStatusService $changeStatusService,
        private readonly PageService $pageService,
        private readonly string $element_toString = 'page',
        private readonly bool $is_female = true,
        private readonly int $pageLength = 10
    ) {
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * La liste des pages qui n'ont pas le statut "Corbeille".
     *
     * @return Response back/element/list.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted(PageVoter::VIEW)]
    public function list(): Response
    {
        // On récupère les pages dont le statut est différent de "Corbeille"
        $pages = $this->pageRepository->findNotCorbeille();

        // Pour le template
        $is_corbeille = false;

        return $this->render('back/element/list.twig', [
            'pages' => $pages,
            'element_toString' => $this->element_toString,
            'is_female' => $this->is_female,
            'pageLength' => $this->pageLength,
            'is_corbeille' => $is_corbeille,
        ]);
    }

    /**
     * La liste des pages qui ont le statut "Corbeille".
     *
     * @return Response back/element/list.twig
     */
    #[Route('/corbeille', name: '_corbeille', options: ['expose' => true])]
    #[IsGranted(PageVoter::VIEW)]
    public function listCorbeille(): Response
    {
        // On récupère les pages dont le statut est "Corbeille"
        $pages = $this->pageRepository->findByStatus(4);

        // Pour le template
        $is_corbeille = true;

        return $this->render('back/element/list.twig', [
            'pages' => $pages,
            'element_toString' => $this->element_toString,
            'is_female' => $this->is_female,
            'pageLength' => $this->pageLength,
            'is_corbeille' => $is_corbeille,
        ]);
    }

    /**
     * Page de création d'une page.
     *
     * @param bool is_preview pour savoir si l'utilisateur souhaite prévisualiser la page
     *
     * @return Response back/page/create_edit.twig
     */
    #[Route('/create/{is_preview}', name: '_create', options: ['expose' => true])]
    #[IsGranted(PageVoter::CREATE)]
    public function create(bool $is_preview): Response
    {
        // On crée une nouvelle page
        $page = new Page();

        // On récupère le formulaire
        $form = $this->createForm(PageType::class, $page);

        // On gère la requête du formulaire
        $form->handleRequest($this->request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On crée la page
            $pageCreated = $this->pageService->create($page);

            // Message flash
            $this->addFlash($pageCreated->status, $pageCreated->message);

            // On récupère si le bouton Visualiser a été cliqué
            $is_preview = ($form->get('preview')->isClicked()) ? 1 : 0;

            // Redirection vers la page de modification
            return $this->redirectToRoute('back_page_edit', [
                'slug' => $page->getSlug(),
                'is_preview' => $is_preview,
            ]);
        }

        return $this->render('back/page/create_edit.twig', compact('form', 'is_preview'));
    }

    /**
     * Page de modification d'une page.
     *
     * @param Page page
     * @param bool is_preview pour savoir si l'utilisateur souhaite prévisualiser la page
     *
     * @return Response back/page/create_edit.twig
     */
    #[Route('/edit/{slug}/{is_preview}', name: '_edit', options: ['expose' => true])]
    #[IsGranted(PageVoter::EDIT)]
    public function edit(
        Page $page,
        bool $is_preview
    ): Response {
        // On récupère le formulaire
        $form = $this->createForm(PageType::class, $page);

        // On gère la requête du formulaire
        $form->handleRequest($this->request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On modifie la page
            $pageEdited = $this->pageService->edit($page);

            // Message flash
            $this->addFlash($pageEdited->status, $pageEdited->message);

            // On récupère si le bouton Visualiser a été cliqué
            $is_preview = ($form->get('preview')->isClicked()) ? 1 : 0;

            // Redirection vers la page de modification
            return $this->redirectToRoute('back_page_edit', [
                'slug' => $page->getSlug(),
                'is_preview' => $is_preview,
            ]);
        }

        return $this->render('back/page/create_edit.twig', compact('page', 'form', 'is_preview'));
    }

    /**
     * Pop-up de confirmation de la suppression d'une ou plusieurs pages.
     *
     * @return Response back/_popup/_yes_no_popup.twig
     */
    #[Route('/delete/confirm', name: '_delete_confirm', options: ['expose' => true])]
    #[IsGranted(PageVoter::DELETE)]
    public function deleteConfirm(): Response
    {
        // Service DeleteService
        $message = $this->deleteService->deleteConfirm($this->pageRepository, 'page');

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.twig', compact('message')),
                'titre' => 'Suppression',
            ])
        );
    }

    /**
     * Suppression d'une ou plusieurs pages.
     *
     * @return Response back/page/list_corbeille.twig
     */
    #[Route('/delete', name: '_delete', options: ['expose' => true])]
    #[IsGranted(PageVoter::DELETE)]
    public function delete(): Response
    {
        // Service DeleteService
        $this->deleteService->delete($this->pageRepository);

        return $this->redirectToRoute('back_page_corbeille');
    }

    /**
     * Pop-up de confirmation du changement de statut d'une ou plusieurs pages.
     *
     * @return Response back/_popup/_yes_no_popup.twig
     */
    #[Route('/change_status/confirm', name: '_change_status_confirm', options: ['expose' => true])]
    #[IsGranted(PageVoter::CHANGE_STATUS)]
    public function changeStatusConfirm(): Response
    {
        // Service ChangeStatusService
        $message = $this->changeStatusService->changeStatusConfirm($this->pageRepository, 'page');

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.twig', compact('message')),
                'titre' => 'Changement de statut',
            ])
        );
    }

    /**
     * Changement du statut d'une ou plusieurs pages.
     *
     * @return Response back/page/list.twig
     */
    #[Route('/change_status', name: '_change_status', options: ['expose' => true])]
    #[IsGranted(PageVoter::CHANGE_STATUS)]
    public function changeStatus(): Response
    {
        // Service ChangeStatusService
        $this->changeStatusService->changeStatus($this->pageRepository);

        return $this->redirectToRoute('back_page');
    }
}
