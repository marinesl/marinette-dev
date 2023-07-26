<?php

/**
 * Controller permettant de gérer les statuts de tous les éléments de l'application.
 *
 * Méthodes :
 * - index() : La liste des statuts pour la page Corbeille des catégories
 */

declare(strict_types=1);

namespace App\Controller\Back;

use App\Repository\StatusRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/manager/status', name: 'back_status')]
class StatusController extends AbstractController
{
    /**
     * La liste des statuts pour la page Corbeille des catégories.
     *
     * @param StatusRepository statusRepository
     * @param Request request
     *
     * @return JsonResponse arrayCollection
     */
    #[Route('/ajax/', name: '_category_ajax', options: ['expose' => true])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        StatusRepository $statusRepository,
        Request $request
    ): JsonResponse {
        // Tableau associatif qui va contenir les données
        $arrayCollection = [];

        // On récupère le statut Publié
        $status = $statusRepository->find(1);

        $arrayCollection[] = [
            'id' => $status->getId(),
            'name' => $status->getName(),
        ];

        // On récupère le statut Corbeille
        $status = $statusRepository->find(4);

        $arrayCollection[] = [
            'id' => $status->getId(),
            'name' => $status->getName(),
        ];

        if ('true' === $request->query->get('isCorbeille')) {
            // On récupère le statut Supprimé
            $status = $statusRepository->find(5);

            $arrayCollection[] = [
                'id' => $status->getId(),
                'name' => $status->getName(),
            ];
        }

        // On retour le tableau en JSON
        return $this->json($arrayCollection);
    }
}
