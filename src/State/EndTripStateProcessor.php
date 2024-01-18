<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Trip;
use App\Event\TripEndedEvent;
use App\Service\TripService;
use Psr\EventDispatcher\EventDispatcherInterface;

class EndTripStateProcessor implements ProcessorInterface
{
    public function __construct(protected TripService $tripService, protected EventDispatcherInterface $eventDispatcher)
    {

    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof Trip) {
            return;
        }

        $trip = $data;

        $this->tripService->finishTrip($trip);

        $this->eventDispatcher->dispatch(new TripEndedEvent($trip));
    }
}
