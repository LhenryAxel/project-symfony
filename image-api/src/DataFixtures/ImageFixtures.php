<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $filenames = ["php87F6-67f67b2497ead.webp", "php3BE0-67f523bf55f25.jpg", "php1897-67f66fc4c0837.jpg"];

        foreach($filenames as $filename){
            $image = new Image();
            $image->setFilename($filename);
            $image->setUploadedAt(new \DateTimeImmutable('-61 days'));
            $manager->persist($image);
        }

        $manager->flush();
    }
}
