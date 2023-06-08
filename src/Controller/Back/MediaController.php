<?php

/**
 * Controller permettant de gérer les médias :
 * - index() : Page des médias
 * - info() : Page des informations d'un média
 * - deleteConfirm() : Pop-up de confirmation de la suppression d'un média
 * - delete() : Suppression d'un média
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Media;
use DateTimeImmutable;
use App\Service\SlugService;
use App\Form\Back\FilterType;
use App\Form\Back\DragAndDropType;
use App\Repository\MediaRepository;
use App\Repository\StatusRepository;
use App\Service\ElementDeleteService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/manager/media', name: 'back_media')]
class MediaController extends AbstractController
{
    /**
     * Page des médias
     * 
     * @param Request request
     * @param MediaRepository mediaRepository
     * @param PaginatorInterface paginator
     * 
     * @return Reponse back/media/list.html.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        Request $request,
        MediaRepository $mediaRepository,
        MediaCategoryRepository $mediaCategoryRepository,
        StatusRepository $statusRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $em,
        SlugService $slugService
    ): Response
    {   
        /**
         * Gestion du formulaire de drag and drop
         */

            // On récupère le formulaire de drag and drop
            $formDragAndDrop = $this->createForm(DragAndDropType::class);

            // On gère la requête du formulaire
            $formDragAndDrop->handleRequest($request);

            // On vérifie si le formulaire a été envoyé et est valide
            if ($formDragAndDrop->isSubmitted() && $formDragAndDrop->isValid()) {
                // On récupère le fichier du formulaire
                $uploadedFile = $request->files->get('file');

                // On réupère le type du fichier
                $mimeType = $uploadedFile->getClientMimeType();

                // On récupère l'identifiant de la catégorie de fichier
                $category_id = 1;

                // On réupère les informations du fichier
                $extension = $uploadedFile->getClientOriginalExtension();
                $title = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) . '_' . date('YmdHis');
                $fileName = $title . '.' . $extension;

                // On récupère la catégorie du fichier
                $category = $mediaCategoryRepository->find($category_id);

                // On crée le lien de l'image
                $path = '../'.$this->getParameter('uploads_directory').'/'.$category->getSlug();
                $pathFileName = $path.'/'.$fileName;

                // Enregistrer le fichier physique dans le dossier uploads
                $uploadedFile->move(
                    $path,
                    $fileName
                );

                // On crée un nouveau média
                $media = new Media();
                $media->setCategory($category);
                $media->setStatus($statusRepository->find(1));
                $media->setCreatedAt(new DateTimeImmutable());
                $media->setEditedAt(new \DateTimeImmutable());

                // Si le fichier est une image
                if (str_contains($mimeType, 'image')) {
                    list($width, $height) = getimagesize($pathFileName);
                    $media->setOriginalHeight($height);
                    $media->setOriginalWidth($width);

                }
                $media->setOriginalSize(filesize($pathFileName));
                // $media->setOriginalSize($uploadedFile->getSize());
                $media->setTitle($title);
                $media->setSlug($slugService->slug($title));
                $media->setExtension($extension);
                $em->persist($media);
                $em->flush();
            }

        /***/

        /**
         * Gestion du formulaire de filtre
         */

            // On récupère le formulaire de filtre
            $formFilter = $this->createForm(FilterType::class);

            // On gère la requête du formulaire
            $formFilter->handleRequest($request);

            // On vérifie si le formulaire a été envoyé et est valide
            if ($formFilter->isSubmitted() && $formFilter->isValid()) {

                // On récupère les données du formulaire
                $data = $formFilter->getData();

                // On récupère les médias avec le statut Publié
                $medias = $mediaRepository->findByTitle($data['title']);

            } else {
                // On récupère les médias avec le statut Publié
                $medias = $mediaRepository->findBy(['status' => 1], ['created_at' => 'DESC']);
            }

        /***/

        // Knp Paginator
        $pagination = $paginator->paginate(
            $medias, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/
        );

        return $this->render('back/media/list.html.twig', [
            'pagination' => $pagination,
            'formFilter' => $formFilter->createView(),
            'formDragAndDrop' => $formDragAndDrop->createView()
        ]);
    }


    /**
     * Page des informations d'un média
     * 
     * 
     * @return Reponse back/media/info.html.twig
     */
    #[Route('/info/{slug}', name: '_info')]
    #[IsGranted('ROLE_ADMIN')]
    public function info(
        Media $media
    ): Response
    {
        return $this->render('back/media/info.html.twig', compact('media'));
    }


    /**
     * Pop-up de confirmation de la suppression d'un média
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
        MediaRepository $mediaRepository
    ): Response
    {
        // Service ElementDeleteService
        $message = $elementDeleteService->deleteConfirm($mediaRepository, 'media', false)."<p>Si le média est utilisé dans une page ou un post, une erreur s’affichera sur le site.</p>";

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.html.twig', compact('message')),
                'titre' => 'Suppression'
            ])
        );
    }


    /**
     * Suppression d'un média
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
        MediaRepository $mediaRepository,
        Request $request
    ): Response
    {
        // On récupère le média
        $media = $mediaRepository->find($request->query->get('ids'));

        // On récupère le chemin du fichier à partir de uploads
        $path = '/'.$this->getParameter('uploads_directory')
                .'/'.$media->getCategory()->getSlug()
                .'/'.$media->getTitle().'.'.$media->getExtension();

        // On récupère le chemin du media original
        $pathOriginal = '../'.$this->getParameter('public_directory') . $path;

        // On récupère le chemin du media dans le cache
        $pathCache = '../'.$this->getParameter('public_directory')
                    .'/'.$this->getParameter('cache_media_directory');

        // Supprimer le fichier physique
        $delete = unlink($pathOriginal);

        // Si la suppression ne s'est pas bien passée
        if (!$delete) {
            $this->addFlash('danger', 'Un problème est survenu, veuillez recommencer.');

        // Si la suppression s'est bien passée
        } else {
            // On supprime l'image dans le cache des filtres
            unlink($pathCache
                    .'/'.$this->getParameter('liip_filter_1')
                    . $path);

            unlink($pathCache
                    .'/'.$this->getParameter('liip_filter_2')
                    . $path);

            // Supprimer le fichier
            // Service ElementDeleteService
            $elementDeleteService->delete($mediaRepository);

            $this->addFlash('success', 'Le fichier a bien été supprimé.');
        }

        return $this->redirectToRoute('back_media');
    }
}
