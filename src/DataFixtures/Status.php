<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Status as EntityStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class Status extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $status = new EntityStatus();
        $status->setName('Publié');
        $manager->persist($status);

        $status = new EntityStatus();
        $status->setName('Brouillon');
        $manager->persist($status);

        $status = new EntityStatus();
        $status->setName('En relecture');
        $manager->persist($status);

        $status = new EntityStatus();
        $status->setName('Corbeille');
        $manager->persist($status);

        $status = new EntityStatus();
        $status->setName('Supprimé');
        $manager->persist($status);

        $manager->flush();
    }
}
