<?php

/**
 * Service de gestion du changement de statut Corbeille des éléments (page, post, etc.)
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

class ElementChangeStatusService
{
    private $request_stack;
    private $em;
    private $statusRepository;
    
    public function __construct(
        RequestStack $request_stack,
        EntityManagerInterface $em,
        StatusRepository $statusRepository
    )
    {
        $this->request_stack = $request_stack;
        $this->em = $em;
        $this->statusRepository = $statusRepository;
    }


    /**
     * Pop-up de confirmation du changement de statut d'un ou plusieurs éléments
     * 
     * @param Object element_repository : repository de l'élement en cours
     * @param string element_toString : string du nom de l'élément
     * @param bool is_female : si la string du nom de l'élément est au féminin ou au masculin
     * 
     * @return string message
     */
    public function changeStatusConfirm(
        $element_repository,
        $element_toString,
        $is_female = true
    ):string
    {
        // On récupère la requête
        $request = $this->request_stack->getCurrentRequest();

        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $request->query->get('ids');

        // On récupère le statut envoyé en GET
        $status = $request->query->get('status');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',',$ids);

        // On personnalise le message
        switch (count($ids)) {
            case 0:
                $message = "Vous devez sélectionner un".(($is_female) ? "e" : "")." ou plusieurs ".$element_toString."s pour changer le statut.";
                break;

            case 1:
                $message = "Êtes-vous sûr.e de changer le statut de ".(($is_female) ? "cette" : "ce")." $element_toString ?";
                break;
            
            default:
                $message = "Êtes-vous sûr.e de changer le statut de ces ".$element_toString."s en $status ?";
                break;
        }
        
        // S'il y a un ou plusieurs id
        if (count($ids) != 0) {

            foreach ($ids as $id) {

                // On recherche l'élément
                $element = $element_repository->find($id);

                // On vérifie que l'élément a été trouvé
                if (!$element) {
                    $message = (($is_female) ? "La " : "Le ").$element_toString." n'a pas été trouvé".(($is_female) ? "e" : "").", veuillez recommencer.";
                    break;
                }

                $message .= "<br>".$element->getTitle();
            }
        }

        return $message;
    }


    /**
     * Changement du statut d'un ou plusieurs éléments
     * 
     * @param Object element_repository : repository de l'élement en cours
     */
    public function changeStatus($element_repository):void
    {
        // On récupère la requête
        $request = $this->request_stack->getCurrentRequest();

        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $request->query->get('ids');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',',$ids);

        foreach ($ids as $id) {

            // On recherche l'élément
            $element = $element_repository->find($id);

            // Changement du statut en brouillon
            $element->setStatus($this->statusRepository->find(2));
            $this->em->persist($element);
        }

        $this->em->flush();
    }
}