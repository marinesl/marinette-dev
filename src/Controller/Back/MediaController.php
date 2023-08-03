<?php

/**
 * Controller permettant de gérer les médias :
 * - index() : Page des médias
 * - info() : Page des informations d'un média
 * - deleteConfirm() : Pop-up de confirmation de la suppression d'un média
 * - delete() : Suppression d'un média.
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Media;
use App\Form\Back\DragAndDropType;
use App\Form\Back\FilterType;
use App\Repository\MediaRepository;
use App\Security\Voter\MediaVoter;
use App\Service\DeleteService;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/manager/media', name: 'back_media')]
class MediaController extends AbstractController
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly MediaRepository $mediaRepository,
        private readonly EntityManagerInterface $em,
        private readonly DeleteService $deleteService,
        private readonly MediaService $mediaService
    ) {
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * Page des médias.
     *
     * @param PaginatorInterface paginator
     *
     * @return Reponse back/media/list.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted(MediaVoter::VIEW)]
    public function index(PaginatorInterface $paginator): Response
    {
        /**
         * Gestion du formulaire de drag and drop.
         */

        // On récupère le formulaire de drag and drop
        $formDragAndDrop = $this->createForm(DragAndDropType::class);

        // On gère la requête du formulaire
        $formDragAndDrop->handleRequest($this->request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($formDragAndDrop->isSubmitted() && $formDragAndDrop->isValid()) {
            // On upload le fichier
            $uploadedFile = $this->mediaService->uploadFile();

            if ('done' === $uploadedFile->status) {
                // On crée un média
                $this->mediaService->create($uploadedFile->data);
            }
        }

        /***/

        /**
         * Gestion du formulaire de filtre.
         */

        // On récupère le formulaire de filtre
        $formFilter = $this->createForm(FilterType::class);

        // On gère la requête du formulaire
        $formFilter->handleRequest($this->request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {

            // On récupère les données du formulaire
            $data = $formFilter->getData();

            // On récupère les médias avec le statut Publié
            $medias = $this->mediaRepository->findByTitle($data['title']);

        } else {
            // On récupère les médias avec le statut Publié
            $medias = $this->mediaRepository->findBy(['status' => 1], ['createdAt' => 'DESC']);
        }

        /***/

        // Knp Paginator
        $pagination = $paginator->paginate(
            $medias, /* query NOT result */
            $this->request->query->getInt('page', 1), /* page number */
            20 /* limit per page */
        );

        return $this->render('back/media/list.twig', compact('pagination', 'formFilter', 'formDragAndDrop'));
    }

    /**
     * Page des informations d'un média.
     *
     * @param Media media
     *
     * @return Reponse back/media/info.twig
     */
    #[Route('/info/{slug}', name: '_info')]
    #[IsGranted(MediaVoter::INFO)]
    public function info(Media $media): Response
    {
        return $this->render('back/media/info.twig', compact('media'));
    }

    /**
     * Pop-up de confirmation de la suppression d'un média.
     *
     * @return Response back/_popup/_yes_no_popup.twig
     */
    #[Route('/delete/confirm', name: '_delete_confirm', options: ['expose' => true])]
    #[IsGranted(MediaVoter::DELETE)]
    public function deleteConfirm(): Response
    {
        // Service DeleteService
        $message = $this->deleteService->deleteConfirm($this->mediaRepository, 'media', false).'<p>Si le média est utilisé dans une page ou un post, une erreur s’affichera sur le site.</p>';

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.twig', compact('message')),
                'titre' => 'Suppression',
            ])
        );
    }

    /**
     * Suppression d'un média.
     *
     * @return Response back/page/list_corbeille.twig
     */
    #[Route('/delete', name: '_delete', options: ['expose' => true])]
    #[IsGranted(MediaVoter::DELETE)]
    public function delete(): Response
    {
        // On récupère le média
        $media = $this->mediaRepository->find($this->request->query->get('ids'));

        // On supprime le média
        $service = $this->mediaService->delete($media);

        $this->addFlash($service->status, $service->message);

        return $this->redirectToRoute('back_media');
    }
}
