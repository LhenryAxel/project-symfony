<?php

namespace App\DataFixtures;

use App\Entity\Stat;
use App\Entity\Image;
use App\Entity\TypeStat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class StatFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $images = $manager->getRepository(Image::class)->findAll();

        foreach ($images as $image) {
            $numStats = random_int(1, 30);

            for ($i = 0; $i < $numStats; $i++) {
                $stat = new Stat();
                $stat->setImage($image);
                $stat->setHitAt(new \DateTimeImmutable(sprintf('-%d days', random_int(0, 60)))); // Date aléatoire dans les 60 derniers jours

                $typeId = random_int(1, 3);
                $type = $manager->getReference(TypeStat::class, $typeId); // Référence vers TypeStat existant
                $stat->setIdType($type);

                $manager->persist($stat);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ImageFixtures::class,
        ];
    }
}
