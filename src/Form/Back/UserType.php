<?php

declare(strict_types=1);

namespace App\Form\Back;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('last_name', TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 100]),
                    new Assert\NotNull()
                ]
            ])

            ->add('first_name', TextType::class, [
                'required' => true,
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre prénom',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 100]),
                    new Assert\NotNull()
                ]
            ])

            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Identifiant',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre identifiant',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 180]),
                    new Assert\NotNull()
                ]
            ])

            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre email',
                ],
                'constraints' => [
                    new Assert\Length(['max' => 200]),
                    new Assert\NotNull()
                ]
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
