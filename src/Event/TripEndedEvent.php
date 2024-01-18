<?php

namespace App\Event;

use App\Entity\Trip;
use Symfony\Contracts\EventDispatcher\Event;

class TripEndedEvent extends Event
{
    public const NAME = 'trip.ended';

    public function __construct(protected Trip $trip)
    {
    }

    public function getTrip(): Trip
    {
        return $this->trip;
    }
}
