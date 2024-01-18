<?php

namespace App\EventSubscriber;

use App\Entity\Scooter;
use App\Event\ScooterLocationUpdatedEvent;
use App\Event\TripEndedEvent;
use App\Event\TripStartedEvent;
use App\Service\ScooterService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TripEventSubscriber implements EventSubscriberInterface
{
    public function __construct(protected ScooterService $scooterService, protected EntityManagerInterface $entityManager)
    {
    }

    #[AsEventListener(event: TripStartedEvent::class)]
    public function onTripStartedEvent(TripStartedEvent $event): void
    {
        $scooter = $event->getTrip()->getScooter();

        $this->scooterService->updateScooterStatus($scooter, Scooter::SCOOTER_STATUS_OCCUPIED);
    }

    #[AsEventListener(event: TripEndedEvent::class)]
    public function onTripEndedEvent(TripEndedEvent $event): void
    {
        $scooter = $event->getTrip()->getScooter();


        $this->entityManager->beginTransaction();

        try {
            $this->scooterService->updateScooterStatus($scooter, Scooter::SCOOTER_STATUS_AVAILABLE);
            $this->scooterService->updateScooterLocation($scooter, $event->getTrip()->getCurrentLocation());

            $this->entityManager->persist($scooter);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }

    #[AsEventListener(event: ScooterLocationUpdatedEvent::class)]
    public function onScooterLocationUpdatedEvent(ScooterLocationUpdatedEvent $event): void
    {
        $this->scooterService->updateScooterLocation($event->getScooter(), $event->getNewLocation());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TripStartedEvent::NAME => 'onTripStartedEvent',
            TripEndedEvent::NAME => 'onTripEndedEvent',
            ScooterLocationUpdatedEvent::NAME => 'onScooterLocationUpdatedEvent',
        ];
    }
}
