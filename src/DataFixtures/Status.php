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
        $arrayStatus = ['Publié', 'Brouillon', 'En relecture', 'Corbeille', 'Supprimé'];

        foreach ($arrayStatus as $status) {
            $this->createStatus($status, $manager);
        }

        $manager->flush();
    }

    public function createStatus($name, $manager)
    {
        $status = new EntityStatus();
        $status->setName($name);
        $manager->persist($status);

        $this->addReference('status-'.str_replace(' ', '-', strtolower($name)), $status);
    }
}
