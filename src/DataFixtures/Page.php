<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Page as EntityPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class Page extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $arrayPages = ['Accueil', 'Mentions légales', 'Recherche'];

        foreach ($arrayPages as $page) {
            $this->createPage($manager, $page);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            Status::class,
        ];
    }

    public function createPage($manager, $title)
    {
        $faker = Factory::create('fr_FR');

        $page = new EntityPage();
        $page->setTitle($title);
        $page->setSlug(strtolower(str_replace(' ', '-', $title)));
        $page->setContent($faker->text(300));
        $page->setMetaDescription($faker->paragraph(2));
        $page->setMetaKeyword(strtolower(str_replace(' ', ',', $title)));
        $page->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $page->setEditedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $page->setStatus($this->getReference('status-publié'));
        $manager->persist($page);

        if ('Accueil' === $title) {
            $this->addReference('home', $page);
        }
    }
}
