<?php

declare(strict_types=1);

namespace App\Form\Back;

use App\Entity\Post;
use App\Entity\Status;
use App\Entity\PostCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Titre du post',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Titre du post',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 60]),
                    new Assert\NotNull()
                ]
            ])

            ->add('content', TextareaType::class, [
                'required' => false,
                'label' => 'Contenu du post',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Contenu du post',
                ],
            ])

            ->add('meta_description', TextType::class, [
                'required' => true,
                'label' => 'Description du post',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description du post',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 160]),
                    new Assert\NotNull()
                ]
            ])

            ->add('meta_keyword', TextType::class, [
                'required' => true,
                'label' => 'Mots clés du post',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mots clés du post',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                    new Assert\NotNull()
                ]
            ])

            ->add('status', EntityType::class, [
                'required' => true,
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-control',
                ],
                'class' => Status::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->findAllQuery();
                },
            ])

            ->add('slug', TextType::class, [
                'required' => true,
                'label' => 'Slug de la page',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 80]),
                    new Assert\NotNull(),
                ]
            ])

            ->add('category', EntityType::class, [
                'required' => true,
                'label' => 'Catégorie',
                'attr' => [
                    'class' => 'form-control',
                ],
                'class' => PostCategory::class,
                'choice_label' => 'name'
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->add('preview', SubmitType::class, [
                'label' => 'Visualiser',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
