<?php

/**
 * Service de gestion du changement de statut Corbeille des éléments (page, post, etc.).
 *
 * Méthodes :
 * - changeStatusConfirm() : Pop-up de confirmation du changement de statut d'un ou plusieurs éléments
 * - changeStatus() : Changement du statut d'un ou plusieurs éléments
 */

declare(strict_types=1);

namespace App\Service;

use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ChangeStatusService
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em,
        private readonly StatusRepository $statusRepository
    ) {
        $this->request = $this->request_stack->getCurrentRequest();
    }

    /**
     * Pop-up de confirmation du changement de statut d'un ou plusieurs éléments.
     *
     * @param object repository : repository de l'élement en cours
     * @param string name : string du nom de l'élément
     * @param bool is_female : si la string du nom de l'élément est au féminin ou au masculin
     *
     * @return string message
     */
    public function changeStatusConfirm(
        $repository,
        $name,
        $is_female = true
    ): string {
        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $this->request->query->get('ids');

        // On récupère le statut envoyé en GET
        $status = $this->request->query->get('status');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',', $ids);

        // On personnalise le message
        switch (count($ids)) {
            case 0:
                $message = 'Vous devez sélectionner un'.(($is_female) ? 'e' : '').' ou plusieurs '.$name.'s pour changer le statut.';
                break;

            case 1:
                $message = 'Êtes-vous sûr.e de changer le statut de '.(($is_female) ? 'cette' : 'ce').' $name ?';
                break;

            default:
                $message = 'Êtes-vous sûr.e de changer le statut de ces '.$name.'s en $status ?';
                break;
        }

        // S'il y a un ou plusieurs id
        if (0 !== count($ids)) {
            foreach ($ids as $id) {
                // On recherche l'élément
                $element = $repository->find($id);

                // On vérifie que l'élément a été trouvé
                if (!$element) {
                    $message = (($is_female) ? 'La ' : 'Le ').$name.' n\'a pas été trouvé'.(($is_female) ? 'e' : '').', veuillez recommencer.';
                    break;
                }

                $message .= '<br>'.$element->getTitle();
            }
        }

        return $message;
    }

    /**
     * Changement du statut d'un ou plusieurs éléments.
     *
     * @param object repository : repository de l'élement en cours
     */
    public function changeStatus($repository): void
    {
        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $this->request->query->get('ids');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',', $ids);

        foreach ($ids as $id) {
            // On recherche l'élément
            $element = $repository->find($id);

            // Changement du statut en brouillon
            $element->setStatus($this->statusRepository->find(2));
            $repository->save($element, true);
        }
    }
}
