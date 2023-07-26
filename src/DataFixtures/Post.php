<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Post as EntityPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class Post extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->createPost($manager, 'brouillon', 'html');
        $this->createPost($manager, 'publié', 'html');
        $this->createPost($manager, 'publié', 'html');
        $this->createPost($manager, 'brouillon', 'css');
        $this->createPost($manager, 'en-relecture', 'css');
        $this->createPost($manager, 'publié', 'css');
        $this->createPost($manager, 'publié', 'css');
        $this->createPost($manager, 'brouillon', 'php');
        $this->createPost($manager, 'corbeille', 'php');
        $this->createPost($manager, 'publié', 'php');
        $this->createPost($manager, 'publié', 'php');
        $this->createPost($manager, 'en-relecture', 'php');
        $this->createPost($manager, 'brouillon', 'js');
        $this->createPost($manager, 'publié', 'js');
        $this->createPost($manager, 'publié', 'js');
        $this->createPost($manager, 'publié', 'js');
        $this->createPost($manager, 'corbeille', 'js');
        $this->createPost($manager, 'corbeille', 'anglais');

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PostCategory::class,
            Status::class,
        ];
    }

    public function createPost($manager, $status, $category)
    {
        $faker = Factory::create('fr_FR');

        $post = new EntityPost();
        $title = $faker->sentence(3);
        $post->setTitle($title);
        $post->setSlug(strtolower(str_replace(' ', '-', $title)));
        $post->setContent($faker->text(300));
        $post->setMetaDescription($faker->paragraph(1));
        $post->setMetaKeyword(strtolower(str_replace(' ', ',', $title)));
        $post->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $post->setEditedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $post->setCategory($this->getReference('category-'.$category));
        $post->setStatus($this->getReference('status-'.$status));
        $manager->persist($post);
    }
}
