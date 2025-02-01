<?php

namespace App\Repository;

use App\Entity\Kartenn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Kartenn>
 */
class KartennRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kartenn::class);
    }


    public function deleteAllKartennou(){
        $query = $this->createQueryBuilder('k')
            ->delete()
            ->getQuery()
            ->execute();
        return $query;
    }

}
