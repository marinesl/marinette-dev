<?php

/**
 * Service de gestion des médias
 * 
 * Méthodes :
 * - uploadFile() : Enregistrement d'un fichier physique
 * - create() : Création d'un média
 * - delete() : Suppression d'un média
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Media;
use App\Service\SlugService;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaCategoryRepository;
use App\Repository\MediaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaService
{
    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly Request $request,
        private readonly EntityManagerInterface $em,
        private readonly MediaCategoryRepository $mediaCategoryRepository,
        private readonly StatusRepository $statusRepository,
        private readonly SlugService $slugService,
        private readonly DeleteService $deleteService,
        private readonly MediaRepository $mediaRepository

    )
    {
        $this->request = $this->request_stack->getCurrentRequest();
    }


    /**
     * Enregistrement d'un fichier physique
     * 
     * @return JsonResponse success
     */
    public function uploadFile(): JsonResponse
    {
        // On récupère le fichier du formulaire
        $uploadedFile = $this->request->files->get('file');

        // On réupère le type du fichier
        $mimeType = $uploadedFile->getClientMimeType();

        // On récupère l'identifiant de la catégorie de fichier
        $category_id = 1;

        // On réupère les informations du fichier
        $extension = $uploadedFile->getClientOriginalExtension();
        $title = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) . '_' . date('YmdHis');
        $fileName = $title . '.' . $extension;

        // On récupère la catégorie du fichier
        $category = $this->mediaCategoryRepository->find($category_id);

        // On crée le lien de l'image
        $path = '../'.$this->getParameter('uploads_directory').'/'.$category->getSlug();
        $pathFileName = $path.'/'.$fileName;

        // Enregistrer le fichier physique dans le dossier uploads
        if ($uploadedFile->move(
            $path,
            $fileName
        )) return $this->json(['status' => 'done', 'data' => [ 'mimeType' => $mimeType, 'pathFileName' => $pathFileName, 'category' => $category, 'title' => $title, 'extension' => $extension ] ]);

        return $this->json(['status' => 'fail']);
    }


    /**
     * Création d'un média
     * 
     * @param array data ['mimeType', 'pathFileName', 'category', 'title', 'extension']
     * 
     * @return JsonResponse success
     */
    public function create($data): JsonResponse
    {
        // On crée un nouveau média
        $media = new Media();
        $media->setCategory($data->category);
        $media->setStatus($this->statusRepository->find(1));
        $media->setCreatedAt(new \DateTimeImmutable());
        $media->setEditedAt(new \DateTimeImmutable());

        // Si le fichier est une image
        if (str_contains($data->mimeType, 'image')) {
            list($width, $height) = getimagesize($data->pathFileName);
            $media->setOriginalHeight($height);
            $media->setOriginalWidth($width);

        }
        $media->setOriginalSize(filesize($data->pathFileName));
        // $media->setOriginalSize($data->getSize());
        $media->setTitle($data->title);
        $media->setSlug($this->slugService->slug($data->title));
        $media->setExtension($data->extension);

        $this->em->persist($media);
        $this->em->flush();

        return $this->json(['status' => 'done']);
    }


    /**
     * Suppression d'un média
     * 
     * @param Media media
     * 
     * @return JsonResponse success
     */
    public function delete($media): JsonResponse
    {
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
            return $this->json([ 'status' => 'danger', 'message' => 'Un problème est survenu, veuillez recommencer.']);

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
            // Service DeleteService
            $this->deleteService->delete($this->mediaRepository);

            return $this->json([ 'status' => 'success', 'message' => 'Le fichier a bien été supprimé.']);
        }
    }
}