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
use App\Repository\MediaRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaService
{
    private $request;

    public function __construct(
        private readonly RequestStack $request_stack,
        private readonly EntityManagerInterface $em,
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

        // On réupère les informations du fichier
        $extension = $uploadedFile->getClientOriginalExtension();
        $name = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) . '_' . date('YmdHis');
        $fileName = $name . '.' . $extension;

        // On crée le lien de l'image
        $path = $this->getParameter('public_directory').'/'.$this->getParameter('uploads_directory');
        $pathFileName = $path.'/'.$fileName;

        // Enregistrer le fichier physique dans le dossier uploads
        if ($uploadedFile->move(
            '../'.$path,
            $fileName
        )) return $this->json(['status' => 'done', 'data' => [ 'mimeType' => $mimeType, 'pathFileName' => $pathFileName, 'name' => $name, 'extension' => $extension ] ]);

        return $this->json(['status' => 'fail']);
    }


    /**
     * Création d'un média
     * 
     * @param array data ['mimeType', 'pathFileName', 'name', 'extension']
     * 
     * @return JsonResponse success
     */
    public function create($data): JsonResponse
    {
        // On crée un nouveau média
        $media = new Media();
        $media->setStatus($this->statusRepository->find(1));
        $media->setCreatedAt(new \DateTimeImmutable());
        $media->setEditedAt(new \DateTimeImmutable());

        // Si le fichier est une image
        if (str_contains($data->mimeType, 'image')) {
            list($width, $height) = getimagesize($data->pathFileName);
            $media->setOriginalHeight($height);
            $media->setOriginalWidth($width);

        }
        $media->setPath($data->pathFileName);
        $media->setSize(filesize($data->pathFileName));
        $media->setName($data->name);
        $media->setSlug($this->slugService->slug($data->name));
        $media->setExtension($data->extension);

        $this->mediaRepository->save($media, true);

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
        // On récupère le chemin du media dans le cache
        $pathCache = '../'.$this->getParameter('public_directory')
                    .'/'.$this->getParameter('cache_media_directory');

        // Supprimer le fichier physique
        $delete = unlink('../'.$media->getPath());

        // Si la suppression ne s'est pas bien passée
        if (!$delete) {
            return $this->json([ 'status' => 'danger', 'message' => 'Un problème est survenu, veuillez recommencer.']);

        // Si la suppression s'est bien passée
        } else {
            // On supprime l'image dans le cache des filtres
            unlink($pathCache
                    .'/'.$this->getParameter('liip_filter_1')
                    .'/'.$media->getPath());

            unlink($pathCache
                    .'/'.$this->getParameter('liip_filter_2')
                    .'/'.$media->getPath());

            // Supprimer le fichier
            // Service DeleteService
            $this->deleteService->delete($this->mediaRepository);

            return $this->json([ 'status' => 'success', 'message' => 'Le fichier a bien été supprimé.']);
        }
    }
}