<?php

/**
 * Controller permettant de gérer les catégories des posts
 * 
 * Méthodes :
 * - list() : La liste des catégories qui n'ont pas le statut "Corbeille"
 * - listCorbeille() : La liste des catégories qui ont le statut "Corbeille"
 * - createEditCheckData() : Vérification des données envoyées du formulaire de création et de modification
 * - create() : Création d'une catégorie
 * - edit() : Modification d'une catégorie
 * - changeStatusConfirm() : Pop-up de confirmation du changement de statut
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Status;
use App\Entity\PostCategory;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/manager/category', name: 'back_category')]
class CategoryController extends AbstractController
{
    /**
     * La liste des catégories qui n'ont pas le statut "Corbeille"
     * 
     * @param PostCategoryRepository postCategoryRepository
     * 
     * @return Response back/category/list.html.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(PostCategoryRepository $postCategoryRepository): Response
    {
        // On récupère les catégories dont le statut est différent de "Corbeille"
        $categories = $postCategoryRepository->findNotCorbeille();

        // Si c'est la page corbeille
        $is_corbeille = false;

        return $this->render('back/category/list.html.twig', compact('categories', 'is_corbeille'));
    }


    /**
     * La liste des catégories qui ont le statut "Corbeille"
     * 
     * @param PostCategoryRepository postCategoryRepository
     * 
     * @return Response back/page/list.html.twig
     */
    #[Route('/corbeille', name: '_corbeille', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function listCorbeille(PostCategoryRepository $postCategoryRepository): Response
    {
        // On récupère les pages dont le statut est "Corbeille"
        $categories = $postCategoryRepository->findByStatus(4);

        // Si c'est la page corbeille
        $is_corbeille = true;

        return $this->render('back/category/list.html.twig', compact('categories', 'is_corbeille'));
    }


    /**
     * Vérification des données envoyées du formulaire de création et de modification
     * 
     * @param Request request
     * @param PostCategoryRepository postCategoryRepository
     */
    public function createEditCheckData(
        $request,
        $postCategoryRepository
    )
    {
        $message = '';

        // On récupère dans les paramètres la valeur du nom
        $new_name = $request->query->get('name');
        // Si le paramètre nom n'existe pas
        if ($new_name == null && $new_name == "") $message = 'Le nom ne peut pas être vide.';

        // On récupère dans les paramètres la valeur du statut
        $new_status_id = $request->query->get('status');
        // Si le paramètre status n'existe pas
        if ($new_status_id == null && $new_status_id == "") $message = 'Le statut ne peut pas être vide.';

        // On récupère dans les paramètres la valeur du slug
        $new_slug = $request->query->get('slug');
        // Si le paramètre slug n'existe pas
        if ($new_slug == null && $new_slug == "") $message = 'Le nom ne peut pas être vide.';

        return ($message != '') ? $message : compact('new_name', 'new_status_id', 'new_slug');
    }


    /**
     * Création d'une catégorie
     * 
     * @param EntityManagerInterface em
     * @param Request request
     * @param PostCategoryRepository postCategoryRepository
     * @param StatusRepository statusRepository
     * 
     * @return JsonResponse success
     */
    #[Route('/create', name: '_create', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        EntityManagerInterface $em, 
        Request $request,
        PostCategoryRepository $postCategoryRepository,
        StatusRepository $statusRepository
    ): JsonResponse
    {
        // On vérifie les données récupérées
        $checkData = $this->createEditCheckData($request, $postCategoryRepository);

        // S'il y a une erreur dans les données, on retourne le message
        if (gettype($checkData) == 'string') return $this->json($checkData);

        // On recherche une catégorie qui a le même slug
        $category_findBySlug = $postCategoryRepository->findOneBySlug($checkData['new_slug']);

        // S'il existe un slug déjà existant
        if ($category_findBySlug != null) {
            return $this->json('Le slug de cette catégorie existe déjà. Veuillez modifier le nom.');

        // Si le slug n'existe pas
        } else {
            // On crée une nouvelle catégorie
            $postCategory = new PostCategory();
            $postCategory->setName($checkData['new_name']);
            $postCategory->setSlug($checkData['new_slug']);
            $postCategory->setStatus($statusRepository->find($checkData['new_status_id']));
            $postCategory->setCreatedAt(new \DateTimeImmutable());
            $em->persist($postCategory);
            $em->flush();
        }

        return $this->json('success');
    }


    /**
     * Modification d'une catégorie
     * 
     * @param PostCategory postCategory
     * @param EntityManagerInterface em
     * @param Request request
     * @param PostCategoryRepository postCategoryRepository
     * @param StatusRepository statusRepository
     * 
     * @return JsonResponse success
     */
    #[Route('/edit/{slug}', name: '_edit', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        PostCategory $postCategory,
        EntityManagerInterface $em, 
        Request $request,
        PostCategoryRepository $postCategoryRepository,
        StatusRepository $statusRepository
    ): JsonResponse
    {
        // On vérifie les données récupérées
        $checkData = $this->createEditCheckData($request, $postCategoryRepository);

        // S'il y a une erreur dans les données, on retourne le message
        if (gettype($checkData) == 'string') return $this->json($checkData);

        // On recherche une catégorie qui a le même slug
        $category_findBySlug = $postCategoryRepository->findOneBySlug($checkData['new_slug']);

        // On récupère l'ancien statut de la catégorie
        $old_status_id = $postCategory->getStatus()->getId();

        // S'il existe un slug déjà existant et la catégory trouvée n'est pas la catégorie en cours
        if ($category_findBySlug != null && $category_findBySlug->getId() != $postCategory->getId()) {
            return $this->json('Le slug de cette catégorie existe déjà. Veuillez modifier le nom.');

        // Si le slug n'existe pas ou le slug est celui de la catégorie en cours
        } else {

            // Si le statut à changer de Corbeille à Supprimé
            if ($checkData['new_status_id'] == 5) {

                // On supprime tous les posts de la catégorie
                $posts = $postCategory->getPosts();
                foreach ($posts as $post) $em->remove($post);
                $em->flush();

                // On supprime la catégorie
                $em->remove($postCategory);
                $em->flush();

            } else {
                $postCategory->setName($checkData['new_name']);
                $postCategory->setSlug($checkData['new_slug']);
                $postCategory->setStatus($statusRepository->find($checkData['new_status_id']));
                $postCategory->setEditedAt(new \DateTimeImmutable());
                $em->persist($postCategory);
                $em->flush();

                // Si le statut a changé
                if ($checkData['new_status_id'] != $old_status_id) {

                    // On récupère tous les posts de la catégorie
                    $posts = $postCategory->getPosts();

                    /**
                     * On récupère le nouveau statut des posts
                     * De Publié à Corbeille : le statut des posts sera Corbeille
                     * De Corbeille à Publié : le statut des posts sera Brouillon
                     */
                    $post_status = ($checkData['new_status_id'] == 4) ? $postCategory->getStatus() : $statusRepository->find(2);

                    foreach ($posts as $post) {
                        $post->setStatus($post_status);
                        $em->persist($post);
                    }
                    $em->flush();
                }
            }
        }

        return $this->json('success');
    }


    /**
     * Pop-up de confirmation du changement de statut
     * 
     * @param PostCategory postCategory
     * @param Status status
     * @param Request request
     * @param EntityManagerInterface em
     * 
     * @return Response back/_popup/_yes_no_popup.html.twig
     */
    #[Route('/change_status/confirm/{postCategory}/{status}', name: '_change_status_confirm', options: ['expose' => true])]
    #[ParamConverter('postCategory', options: ['mapping' => ['postCategory' => 'slug']])]
    #[Entity('status', options: ['id' => 'status'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatusConfirm(
        PostCategory $postCategory,
        Status $status,
        Request $request, 
        EntityManagerInterface $em
    ): Response
    {
        // Le message à afficher sur le pop-up
        switch ($status->getId()) {
            case 5:
                $message = "<p>Êtes-vous sûr.e de supprimer définitivement cette catégorie ?</p><p>".$postCategory->getName()."</p><p>Les posts seront aussi supprimés.</p>";
                break;

            case 1:
                $message = "<p>Êtes-vous sûr.e de changer le statut de cette categorie en ".$status->getName()." ?</p><p>".$postCategory->getName()."</p><p>Les posts auront un statut Brouillon.</p>";
                break;
            
            default:
                $message = "<p>Êtes-vous sûr.e de changer le statut de cette categorie en ".$status->getName()." ?</p><p>".$postCategory->getName()."</p><p>Les posts changeront aussi de statut.</p>";
                break;
        }
        

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.html.twig', compact('message')),
                'titre' => 'Changement de statut'
            ])
        );
    }
}
