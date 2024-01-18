<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Scooter;
use App\Entity\Trip;
use App\Event\TripStartedEvent;
use App\Repository\ScooterRepository;
use App\Service\TripService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StartTripStateProcessor implements ProcessorInterface
{
    public function __construct(protected ScooterRepository $scooterRepository, protected TripService $tripService, protected EventDispatcherInterface $eventDispatcher)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Trip
    {
        $scooter = $this->scooterRepository->findOneBy(['id' => $data->getScooterId()]);

        if (!$scooter) {
            throw new NotFoundHttpException();
        }

        $data->setScooter($scooter);

        if ($scooter->getStatus() !== Scooter::SCOOTER_STATUS_AVAILABLE) {
            throw new ConflictHttpException('Scooter is not available at the moment');
        }

        $trip = $this->tripService->createTrip($scooter);

        $this->eventDispatcher->dispatch(new TripStartedEvent($trip));

        return $trip;
    }
}
