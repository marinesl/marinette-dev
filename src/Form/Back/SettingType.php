<?php

declare(strict_types=1);

namespace App\Form\Back;

use App\Entity\Page;
use App\Entity\Setting;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Titre du site',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Titre du site',
                ],
            ])

            ->add('url', UrlType::class, [
                'required' => true,
                'label' => 'URL du site',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'URL du site',
                ],
            ])

            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email de l\'administrateur',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Email de l\'administrateur',
                ],
            ])

            ->add('home', EntityType::class, [
                'required' => true,
                'label' => 'Page d\'accueil',
                'attr' => [
                    'class' => 'form-control',
                ],
                'class' => Page::class,
                'choice_label' => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->findAllQuery();
                },
            ])

            ->add('google_analytics_code', TextareaType::class, [
                'label' => 'Code Google Analytics',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Code Google Analytics',
                    'rows' => 10
                ],
            ])

            ->add('send', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
        ]);
    }
}
