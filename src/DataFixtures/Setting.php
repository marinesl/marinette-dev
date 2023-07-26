<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Setting as EntitySetting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class Setting extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $setting = new EntitySetting();
        $setting->setTitle('Marinette.dev');
        $setting->setUrl('http://localhost:33000/');
        $setting->setEmail('lancelinmarine@gmail.com');
        $setting->setHome($this->getReference('home'));
        $manager->persist($setting);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            Page::class,
        ];
    }
}
