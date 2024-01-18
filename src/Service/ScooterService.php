<?php

namespace App\Service;

use App\Entity\Scooter;
use App\Repository\ScooterRepository;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\EntityManagerInterface;

class ScooterService
{
    public function __construct(protected EntityManagerInterface $entityManager, protected ScooterRepository $scooterRepository)
    {
    }

    public function getScootersInRectangle(Point $southWestLocation, Point $northEastLocation, int $status): array
    {
        return $this->scooterRepository->getScootersInRectangle($southWestLocation, $northEastLocation, $status);
    }

    public function updateScooterStatus(Scooter $scooter, int $status): void
    {
        $scooter->setStatus($status);
        $this->entityManager->flush();
    }

    public function updateScooterLocation(Scooter $scooter, Point $newLocation): void
    {
        $scooter->setLocation($newLocation);
        $this->entityManager->flush();
    }
}
