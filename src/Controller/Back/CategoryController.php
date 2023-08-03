<?php

/**
 * Controller permettant de gérer les catégories des posts.
 *
 * Méthodes :
 * - list() : La liste des catégories qui n'ont pas le statut "Corbeille"
 * - listCorbeille() : La liste des catégories qui ont le statut "Corbeille"
 * - create() : Création d'une catégorie
 * - edit() : Modification d'une catégorie
 * - changeStatusConfirm() : Pop-up de confirmation du changement de statut
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\PostCategory;
use App\Entity\Status;
use App\Repository\PostCategoryRepository;
use App\Security\Voter\CategoryVoter;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/manager/category', name: 'back_category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PostCategoryRepository $postCategoryRepository,
        private readonly CategoryService $categoryService
    ) {
    }

    /**
     * La liste des catégories qui n'ont pas le statut "Corbeille".
     *
     * @return Response back/category/list.twig
     */
    #[Route('/', name: '', options: ['expose' => true])]
    #[IsGranted(CategoryVoter::VIEW)]
    public function list(): Response
    {
        // On récupère les catégories dont le statut est différent de "Corbeille"
        $categories = $this->postCategoryRepository->findNotCorbeille();

        // Si c'est la page corbeille
        $is_corbeille = false;

        return $this->render('back/category/list.twig', compact('categories', 'is_corbeille'));
    }

    /**
     * La liste des catégories qui ont le statut "Corbeille".
     *
     * @return Response back/page/list.twig
     */
    #[Route('/corbeille', name: '_corbeille', options: ['expose' => true])]
    #[IsGranted(CategoryVoter::VIEW)]
    public function listCorbeille(): Response
    {
        // On récupère les pages dont le statut est "Corbeille"
        $categories = $this->postCategoryRepository->findByStatus(4);

        // Si c'est la page corbeille
        $is_corbeille = true;

        return $this->render('back/category/list.twig', compact('categories', 'is_corbeille'));
    }

    /**
     * Création d'une catégorie.
     *
     * @return JsonResponse success
     */
    #[Route('/create', name: '_create', options: ['expose' => true])]
    #[IsGranted(CategoryVoter::CREATE)]
    public function create(): JsonResponse
    {
        return $this->json($this->categoryService->create());
    }

    /**
     * Modification d'une catégorie.
     *
     * @param PostCategory postCategory
     *
     * @return JsonResponse success
     */
    #[Route('/edit/{slug}', name: '_edit', options: ['expose' => true])]
    #[IsGranted(CategoryVoter::EDIT)]
    public function edit(
        PostCategory $postCategory
    ): JsonResponse {
        return $this->json($this->categoryService->edit($postCategory));
    }

    /**
     * Pop-up de confirmation du changement de statut.
     *
     * @param PostCategory postCategory
     * @param Status status
     *
     * @return Response back/_popup/_yes_no_popup.twig
     */
    #[Route('/change_status/confirm/{postCategory}/{status}', name: '_change_status_confirm', options: ['expose' => true])]
    #[ParamConverter('postCategory', options: ['mapping' => ['postCategory' => 'slug']])]
    #[Entity('status', options: ['id' => 'status'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatusConfirm(
        PostCategory $postCategory,
        Status $status,
    ): Response {
        // Le message à afficher sur le pop-up
        switch ($status->getId()) {
            case 5:
                $message = '<p>Êtes-vous sûr.e de supprimer définitivement cette catégorie ?</p><p>'.$postCategory->getName().'</p><p>Les posts seront aussi supprimés.</p>';
                break;

            case 1:
                $message = '<p>Êtes-vous sûr.e de changer le statut de cette categorie en '.$status->getName().' ?</p><p>'.$postCategory->getName().'</p><p>Les posts auront un statut Brouillon.</p>';
                break;

            default:
                $message = '<p>Êtes-vous sûr.e de changer le statut de cette categorie en '.$status->getName().' ?</p><p>'.$postCategory->getName().'</p><p>Les posts changeront aussi de statut.</p>';
                break;
        }

        return new Response(
            json_encode([
                'content' => $this->renderView('back/_popup/_yes_no_popup.twig', compact('message')),
                'titre' => 'Changement de statut',
            ])
        );
    }
}
