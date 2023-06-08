<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Media as EntityMedia;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Media extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Folder path to be flushed
        $folder_path = "public/uploads";

        // List of name of files inside
        // specified folder
        $files = glob($folder_path . '/*');

        // Delete all the files of the list
        foreach ($files as $file) {
            if (is_file($file)) unlink($file);
        } 

        for ($i=0; $i < 10; $i++)
            $this->createMedia($manager);

        $manager->flush();
    }

    public function getDependencies(): array 
    {
        return [
            Status::class
        ];
    }

    public function createMedia($manager) 
    {
        $faker = Factory::create('fr_FR');

        $width = 640;
        $height = 480;
        $extension = 'jpg';
        $imageUrl = $faker->image('public/uploads', $width, $height, 'animals', true, true, 'cats', true, 'jpg');
        $name = explode('.', explode('/', $imageUrl)[2])[0];

        $media = new EntityMedia();
        $media->setName($name);
        $media->setSlug($name);
        $media->setPath($imageUrl);
        $media->setHeight($height);
        $media->setWidth($width);
        $media->setSize($width * $height);
        $media->setStatus($this->getReference('status-publié'));
        $media->setExtension($extension);
        $media->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $media->setEditedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year')));
        $manager->persist($media);
    }
}
