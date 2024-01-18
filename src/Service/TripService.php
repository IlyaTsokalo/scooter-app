<?php

namespace App\Service;

use App\Entity\Scooter;
use App\Entity\Trip;
use App\Repository\TripRepository;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class TripService
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected Security               $security,
        protected TripRepository $tripRepository
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function createTrip(Scooter $scooter): Trip
    {
        $trip = (new Trip())
            ->setScooter($scooter)
            ->setUser($this->security->getUser())
            ->setStartLocation($scooter->getLocation())
            ->setCurrentLocation($scooter->getLocation())
            ->setEndLocation((new Point(0, 0)))
            ->setStatus(Trip::TRIP_STATUS_STARTED);


        $this->entityManager->persist($trip);
        $this->entityManager->flush();

        return $trip;
    }

    public function finishTrip(Trip $trip): void
    {
        $trip->setStatus(Trip::TRIP_STATUS_ENDED)
            ->setEndLocation($trip->getCurrentLocation());


        $this->entityManager->persist($trip);
        $this->entityManager->flush();
    }
}
