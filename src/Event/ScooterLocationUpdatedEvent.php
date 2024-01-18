<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Scooter;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class ScooterLocationUpdatedEvent extends Event
{
    public const NAME = 'scooter.location.updated';

    public function __construct(protected Scooter $scooter, protected Point $newLocation)
    {
    }

    public function getScooter(): Scooter
    {
        return $this->scooter;
    }

    public function getNewLocation(): Point
    {
        return $this->newLocation;
    }
}
