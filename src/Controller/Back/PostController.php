<?php

/**
 * Controller permettant de gérer les posts
 * 
 * Méthodes :
 * - list() : La liste des posts qui n'ont pas le statut "Corbeille"
 * - listCorbeille() : La liste des posts qui ont le statut "Corbeille"
 * - create() : Page de création d'un post
 * - edit() : Page de modification d'un post
 * - deleteConfirm() : Pop-up de confirmation de la suppression d'un ou plusieurs posts
 * - delete() : Suppression d'un ou plusieurs posts
 * - changeStatusConfirm() : Pop-up de confirmation du changement de statut d'un ou plusieurs post
 * - changeStatus() : Changement du statut d'un ou plusieurs post
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Post;
use App\Form\Back\PostType;
use App\Repository\PostRepository;
use App\Service\ElementDeleteService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ElementChangeStatusService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/manager/post', name: 'back_post')]
class PostController extends AbstractController
{
    private $element_toString;
    private $is_female;
    private $pageLength;

    public function __construct()
    {
        $this->element_toString = "post";
        $this->is_female = false;
        $this->pageLength = 20;
    }

    /**
     * La liste des posts qui n'ont pas le statut "Corbeille"
     * 
     * @param PostRepository postRepository
     * 
     * @return Response back/element/list.html.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(PostRepository $postRepository): Response
    {
        // On récupère les posts dont le statut est différent de "Corbeille"
        $posts = $postRepository->findNotCorbeille();

        // Pour le template
        $is_corbeille = false;

        return $this->render('back/element/list.html.twig', [
            'posts' => $posts,
            'element_toString' => $this->element_toString,
            'is_female' => $this->is_female,
            'pageLength' => $this->pageLength,
            'is_corbeille' => $is_corbeille
        ]);
    }


    /**
     * La liste des posts qui ont le statut "Corbeille"
     * 
     * @param PostRepository postRepository
     * 
     * @return Response back/element/list.html.twig
     */
    #[Route('/corbeille', name: '_corbeille', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function listCorbeille(PostRepository $postRepository): Response
    {
        // On récupère les posts dont le statut est "Corbeille"
        $posts = $postRepository->findByStatus(4);

        // Pour le template
        $is_corbeille = true;

        return $this->render('back/element/list.html.twig', [
            'posts' => $posts,
            'element_toString' => $this->element_toString,
            'is_female' => $this->is_female,
            'pageLength' => $this->pageLength,
            'is_corbeille' => $is_corbeille
        ]);
    }


    /**
     * Page de création d'un post
     * 
     * @param bool is_preview pour savoir si l'utilisateur souhaite prévisualiser le post
     * @param EntityManagerInterface em
     * @param Request request
     * 
     * @return Response back/post/create_edit.html.twig
     */
    #[Route('/create/{is_preview}', name: '_create', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        bool $is_preview,
        EntityManagerInterface $em, 
        Request $request
    ): Response
    {
        // On crée un nouveau post
        $post = new Post();

        // On récupère le formulaire
        $form = $this->createForm(PostType::class, $post);

        // On gère la requête du formulaire
        $form->handleRequest($request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setEditedAt(new \DateTimeImmutable());
            $em->persist($post);
            $em->flush();

            // Message flash
            $this->addFlash('success', "Le post a été créé.");

            // On récupère si le bouton Visualiser a été cliqué
            $is_preview = ($form->get('preview')->isClicked()) ? 1 : 0;

            // Redirection vers la post de modification
            return $this->redirectToRoute('back_post_edit', [
                'slug' => $post->getSlug() ,
                'is_preview' => $is_preview
            ]);
        }

        return $this->render('back/post/create_edit.html.twig', [
            'form' => $form->createView(),
            'is_preview' => $is_preview
        ]);
    }


    /**
     * Page de modification d'un post
     * 
     * @param Post post
     * @param bool is_preview pour savoir si l'utilisateur souhaite prévisualiser le post
     * @param EntityManagerInterface em
     * @param Request request
     * 
     * @return Response back/post/create_edit.html.twig
     */
    #[Route('/edit/{slug}/{is_preview}', name: '_edit', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Post $post, 
        bool $is_preview,
        EntityManagerInterface $em,
        Request $request
    ): Response
    {
        // On récupère le formulaire
        $form = $this->createForm(PostType::class, $post);

        // On gère la requête du formulaire
        $form->handleRequest($request);

        // On vérifie si le formulaire a été envoyé et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $post->setEditedAt(new \DateTimeImmutable());
            $em->persist($post);
            $em->flush();

            // Message flash
            $this->addFlash('success', "Les informations ont été enregistrées.");

            // On récupère si le bouton Visualiser a été cliqué
            $is_preview = ($form->get('preview')->isClicked()) ? 1 : 0;

            // Redirection vers la post de modification
            return $this->redirectToRoute('back_post_edit', [
                'slug' => $post->getSlug(),
                'is_preview' => $is_preview
            ]);
        }

        return $this->render('back/post/create_edit.html.twig', [
            'post' => $post,
            'form' => $form->createView() ,
            'is_preview' => $is_preview
        ]);
    }


    /**
     * Pop-up de confirmation de la suppression d'un ou plusieurs posts
     * 
     * @param ElementDeleteService elementDeleteService
     * @param PostRepository postRepository
     * 
     * @return Response back/_popup/_yes_no_popup.html.twig
     */
    #[Route('/delete/confirm', name: '_delete_confirm', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteConfirm(
        ElementDeleteService $elementDeleteService,
        PostRepository $postRepository
    ): Response
    {
        // Service ElementDeleteService
        $message = $elementDeleteService->deleteConfirm($postRepository, 'post', false);

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.html.twig', compact('message')),
                'titre' => 'Suppression'
            ])
        );
    }


    /**
     * Suppression d'un ou plusieurs posts
     * 
     * @param ElementDeleteService elementDeleteService
     * @param PostRepository postRepository
     * 
     * @return Response back/post/list_corbeille.html.twig
     */
    #[Route('/delete', name: '_delete', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        ElementDeleteService $elementDeleteService,
        PostRepository $postRepository
    ): Response
    {
        // Service ElementDeleteService
        $elementDeleteService->delete($postRepository);

        return $this->redirectToRoute('back_post_corbeille');
    }


    /**
     * Pop-up de confirmation du changement de statut d'un ou plusieurs post
     * 
     * @param ElementChangeStatusService elementChangeStatusService
     * @param PostRepository postRepository
     * 
     * @return Response back/_popup/_yes_no_popup.html.twig
     */
    #[Route('/change_status/confirm', name: '_change_status_confirm', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatusConfirm(
        ElementChangeStatusService $elementChangeStatusService,
        PostRepository $postRepository
    ): Response
    {
        // Service ElementChangeStatusService
        $message = $elementChangeStatusService->changeStatusConfirm($postRepository, 'post', false);

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.html.twig', compact('message')),
                'titre' => 'Changement de statut'
            ])
        );
    }


    /**
     * Changement du statut d'un ou plusieurs post
     * 
     * @param ElementChangeStatusService elementChangeStatusService
     * @param PostRepository postRepository
     * 
     * @return Response back/post/list.html.twig
     */
    #[Route('/change_status', name: '_change_status', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatus(
        ElementChangeStatusService $elementChangeStatusService,
        PostRepository $postRepository
    ): Response
    {
        // Service ElementChangeStatusService
        $elementChangeStatusService->changeStatus($postRepository);

        return $this->redirectToRoute('back_post');
    }
}
