<?php

namespace App\Repository;

use App\Entity\Scooter;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scooter>
 *
 * @method Scooter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scooter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scooter[]    findAll()
 * @method Scooter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScooterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scooter::class);
    }

    public function getScootersInRectangle(Point $southWestLocation, Point $northEastLocation, int $status): array
    {
        return $this->createQueryBuilder('scooter')
            ->select('scooter.id, scooter.location, scooter.status')
            ->where('X(scooter.location) BETWEEN :southWestLongitude AND :northEastLongitude')
            ->andWhere('Y(scooter.location) BETWEEN :southWestLatitude AND :northEastLatitude')
            ->andWhere('scooter.status = :status')
            ->setParameters([
                'southWestLongitude' => $southWestLocation->getLongitude(),
                'northEastLongitude' => $northEastLocation->getLongitude(),
                'southWestLatitude' => $southWestLocation->getLatitude(),
                'northEastLatitude' => $northEastLocation->getLatitude(),
                'status' => $status
            ])
            ->getQuery()
            ->getResult();
    }
}
