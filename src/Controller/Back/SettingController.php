<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Form\Back\SettingType;
use App\Repository\SettingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/manager/setting', name: 'back_setting')]
class SettingController extends AbstractController
{
    #[Route('/', name: '')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        SettingRepository $settingRepository,
        Request $request
    ): Response
    {
        // On récupère les settings
        $setting = $settingRepository->find(1);

        // On récupère le formulaire
        $form = $this->createForm(SettingType::class, $setting);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $settingRepository->save($setting, true);
        }

        return $this->render('back/setting/index.html.twig', compact('form'));
    }
}
