<?php

/**
 * Service de gestion de la suppression des éléments (page, post, etc.)
 * 
 * Méthodes :
 * - deleteConfirm() : Pop-up de confirmation de la suppression d'un ou plusieurs éléments
 * - delete() : Suppression d'un ou plusieurs éléments
 */

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ElementDeleteService
{
    private $request_stack;
    private $em;
    
    public function __construct(
        RequestStack $request_stack,
        EntityManagerInterface $em
    )
    {
        $this->request_stack = $request_stack;
        $this->em = $em;
    }


    /**
     * Pop-up de confirmation de la suppression d'un ou plusieurs éléments
     * 
     * @param Object element_repository : repository de l'élement en cours
     * @param string element_toString : string du nom de l'élément
     * @param bool is_female : si la string du nom de l'élément est au féminin ou au masculin
     * 
     * @return string message
     */
    public function deleteConfirm(
        $element_repository,
        $element_toString, 
        $is_female = true,
    ): string 
    {
        // On récupère la requête
        $request = $this->request_stack->getCurrentRequest();

        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $request->query->get('ids');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',',$ids);

        // On personnalise le message
        switch (count($ids)) {
            case 0:
                $message = "<p>Vous devez sélectionner un".(($is_female) ? "e" : "")." ou plusieurs ".$element_toString."s à supprimer.</p>";
                break;

            case 1:
                $message = "<p>Êtes-vous sûr.e de supprimer définitivement ".(($is_female) ? "cette" : "ce")." $element_toString ?</p>";
                break;
            
            default:
                $message = "<p>Êtes-vous sûr.e de supprimer définitivement ces ".$element_toString."s ?</p>";
                break;
        }
        
        // S'il y a un ou plusieurs id
        if (count($ids) != 0) {

            foreach ($ids as $id) {

                // On recherche l'élément
                $element = $element_repository->find($id);

                // On vérifie que l'élément a été trouvé
                if (!$element) {
                    $message = (($is_female) ? "<p>La " : "<p>Le ").$element_toString." n'a pas été trouvé".(($is_female) ? "e" : "").", veuillez recommencer.</p>";
                    break;
                }

                $message .= "<p>".$element->getTitle()."</p>";
            }
        }

        return $message;
    }


    /**
     * Suppression d'un ou plusieurs éléments
     * 
     * @param Object element_repository : repository de l'élement en cours
     */
    public function delete($element_repository): void
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

            // Suppression de l'élément
            $this->em->remove($element);
        }

        $this->em->flush();
    }
}