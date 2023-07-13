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

class DeleteService
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em
    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }


    /**
     * Pop-up de confirmation de la suppression d'un ou plusieurs éléments
     * 
     * @param Object repository : repository de l'élement en cours
     * @param string name : string du nom de l'élément
     * @param bool is_female : si la string du nom de l'élément est au féminin ou au masculin
     * 
     * @return string message
     */
    public function deleteConfirm(
        $repository,
        $name, 
        $is_female = true,
    ): string 
    {
        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $this->request->query->get('ids');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',',$ids);

        // On personnalise le message
        switch (count($ids)) {
            case 0:
                $message = "<p>Vous devez sélectionner un".(($is_female) ? "e" : "")." ou plusieurs ".$name."s à supprimer.</p>";
                break;

            case 1:
                $message = "<p>Êtes-vous sûr.e de supprimer définitivement ".(($is_female) ? "cette" : "ce")." $name ?</p>";
                break;
            
            default:
                $message = "<p>Êtes-vous sûr.e de supprimer définitivement ces ".$name."s ?</p>";
                break;
        }
        
        // S'il y a un ou plusieurs id
        if (count($ids) != 0) {

            foreach ($ids as $id) {

                // On recherche l'élément
                $element = $repository->find($id);

                // On vérifie que l'élément a été trouvé
                if (!$element) {
                    $message = (($is_female) ? "<p>La " : "<p>Le ").$name." n'a pas été trouvé".(($is_female) ? "e" : "").", veuillez recommencer.</p>";
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
     * @param Object repository : repository de l'élement en cours
     */
    public function delete($repository): void
    {
        // On récupère le(s) identifiant(s) envoyé(s) en GET
        $ids = $this->request->query->get('ids');

        // On vérifie combien il y a d'identifiant
        $ids = explode(',',$ids);

        foreach ($ids as $id) {

            // On recherche l'élément
            $element = $repository->find($id);

            // Suppression de l'élément
            $this->em->remove($element);
        }

        $this->em->flush();
    }
}