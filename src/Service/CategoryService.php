<?php

/**
 * Service de gestion des catégories
 * 
 * Méthodes :
 * - checkData() : Vérification des données envoyées du formulaire de création et de modification
 * - create() : Création d'une catégorie
 * - edit() : Modification d'une catégorie
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\PostCategory;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostCategoryRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CategoryService
{
    private $request;
    
    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly PostCategoryRepository $postCategoryRepository,
        private readonly StatusRepository $statusRepository,
        private readonly EntityManagerInterface $em,
    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }


    /**
     * Vérification des données envoyées du formulaire de création et de modification
     * 
     * @return message || tableau de données
     */
    public function checkData()
    {
        $message = '';

        // On récupère dans les paramètres la valeur du nom
        $new_name = $this->request->query->get('name');
        // Si le paramètre nom n'existe pas
        if ($new_name == null && $new_name == "") $message = 'Le nom ne peut pas être vide.';

        // On récupère dans les paramètres la valeur du statut
        $new_status_id = $this->request->query->get('status');
        // Si le paramètre status n'existe pas
        if ($new_status_id == null && $new_status_id == "") $message = 'Le statut ne peut pas être vide.';

        // On récupère dans les paramètres la valeur du slug
        $new_slug = $this->request->query->get('slug');
        // Si le paramètre slug n'existe pas
        if ($new_slug == null && $new_slug == "") $message = 'Le nom ne peut pas être vide.';

        return ($message != '') ? $message : compact('new_name', 'new_status_id', 'new_slug');
    }


    /**
     * Création d'une catégorie
     * 
     * @return string message
     */
    public function create(): string
    {
        // On vérifie les données récupérées
        $checkData = $this->checkData();

        // S'il y a une erreur dans les données, on retourne le message
        if (gettype($checkData) == 'string') return $checkData;

        // On recherche une catégorie qui a le même slug
        $category_findBySlug = $this->postCategoryRepository->findOneBySlug($checkData['new_slug']);

        // S'il existe un slug déjà existant
        if ($category_findBySlug != null) {
            return 'Le slug de cette catégorie existe déjà. Veuillez modifier le nom.';

        // Si le slug n'existe pas
        } else {
            // On crée une nouvelle catégorie
            $postCategory = new PostCategory();
            $postCategory->setName($checkData['new_name']);
            $postCategory->setSlug($checkData['new_slug']);
            $postCategory->setStatus($this->statusRepository->find($checkData['new_status_id']));
            $postCategory->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($postCategory);
            $this->em->flush();
        }

        return 'success';
    }


    /**
     * Modification d'une catégorie
     * 
     * @param PostCategory postCategory
     * 
     * @return string message
     */
    public function edit(PostCategory $postCategory): string
    {
        // On vérifie les données récupérées
        $checkData = $this->checkData();

        // S'il y a une erreur dans les données, on retourne le message
        if (gettype($checkData) == 'string') return $checkData;

        // On recherche une catégorie qui a le même slug
        $category_findBySlug = $this->postCategoryRepository->findOneBySlug($checkData['new_slug']);

        // On récupère l'ancien statut de la catégorie
        $old_status_id = $postCategory->getStatus()->getId();

        // S'il existe un slug déjà existant et la catégory trouvée n'est pas la catégorie en cours
        if ($category_findBySlug != null && $category_findBySlug->getId() != $postCategory->getId()) {
            return 'Le slug de cette catégorie existe déjà. Veuillez modifier le nom.';

        // Si le slug n'existe pas ou le slug est celui de la catégorie en cours
        } else {

            // Si le statut à changer de Corbeille à Supprimé
            if ($checkData['new_status_id'] == 5) {

                // On supprime tous les posts de la catégorie
                $posts = $postCategory->getPosts();
                foreach ($posts as $post) $this->em->remove($post);
                $this->em->flush();

                // On supprime la catégorie
                $this->em->remove($postCategory);
                $this->em->flush();

            } else {
                $postCategory->setName($checkData['new_name']);
                $postCategory->setSlug($checkData['new_slug']);
                $postCategory->setStatus($this->statusRepository->find($checkData['new_status_id']));
                $postCategory->setEditedAt(new \DateTimeImmutable());
                $this->em->persist($postCategory);
                $this->em->flush();

                // Si le statut a changé
                if ($checkData['new_status_id'] != $old_status_id) {

                    // On récupère tous les posts de la catégorie
                    $posts = $postCategory->getPosts();

                    /**
                     * On récupère le nouveau statut des posts
                     * De Publié à Corbeille : le statut des posts sera Corbeille
                     * De Corbeille à Publié : le statut des posts sera Brouillon
                     */
                    $post_status = ($checkData['new_status_id'] == 4) ? $postCategory->getStatus() : $this->statusRepository->find(2);

                    foreach ($posts as $post) {
                        $post->setStatus($post_status);
                        $this->em->persist($post);
                    }
                    $this->em->flush();
                }
            }
        }

        return 'success';
    }
}