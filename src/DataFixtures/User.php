<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User as EntityUser;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class User extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasherInterface)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $user = new EntityUser();
        $user->setUsername('marinette');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->userPasswordHasherInterface->hashPassword($user, 'Visky0606!'));
        $user->setFirstName('Marine');
        $user->setLastName('Lancelin');
        $user->setEmail('lancelinmarine@gmail.com');
        $createdAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year'));
        $user->setCreatedAt($createdAt);
        $editedAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year'));
        $user->setEditedAt($editedAt);

        $manager->persist($user);
        $manager->flush();
    }
}
