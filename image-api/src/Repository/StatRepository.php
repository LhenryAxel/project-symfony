<?php

namespace App\Repository;

use App\Entity\Stat;
use App\Entity\Image;
use App\Entity\TypeStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stat>
 */
class StatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stat::class);
    }
    /**
    * @return Stat[] Returns an array of Stat objects
    */
    public function findByImageAndType(Image $image, TypeStat $type): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.image = :image')
            ->andWhere('s.id_type = :type')
            ->setParameter('image', $image)
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Stat[] Returns an array of Stat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Stat
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function findTopDownloadedImages(int $limit = 20): array
    {
    $qb = $this->createQueryBuilder('s')
        ->select('i.filename, COUNT(s.id) AS download_count')
        ->join('s.image', 'i')
        ->where('s.idType = :type')
        ->groupBy('i.id')
        ->orderBy('download_count', 'DESC')
        ->setMaxResults($limit)
        ->setParameter('type', TypeStat::Telechargement->value);

    return $qb->getQuery()->getResult();
    }

}
