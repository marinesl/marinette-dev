<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PostCategory as EntityPostCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostCategory extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $arrayPostCategories = ['HTML', 'CSS', 'PHP', 'JS', 'Anglais'];

        foreach ($arrayPostCategories as $cat) {
            $this->createPostCategory($cat, $manager);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            Status::class,
        ];
    }

    public function createPostCategory($name, $manager)
    {
        $faker = Factory::create('fr_FR');

        $postCategory = new EntityPostCategory();
        $postCategory->setName($name);
        $postCategory->setSlug(strtolower($name));
        $postCategory->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $postCategory->setEditedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));

        if ('Anglais' === $name) {
            $postCategory->setStatus($this->getReference('status-corbeille'));
        } else {
            $postCategory->setStatus($this->getReference('status-publié'));
        }

        $manager->persist($postCategory);

        $this->addReference('category-'.strtolower($name), $postCategory);
    }
}
