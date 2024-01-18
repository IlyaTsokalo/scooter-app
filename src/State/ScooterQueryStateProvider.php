<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Scooter;
use App\Service\ScooterService;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;

class ScooterQueryStateProvider implements ProviderInterface
{
    public function __construct(protected ScooterService $scooterService)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $southWestLocation = trim($context['filters'][Scooter::SOUTH_WEST_LOCATION_PARAMETER_NAME]);
        $northEastLocation = trim($context['filters'][Scooter::NORTH_EAST_LOCATION_PARAMETER_NAME]);
        $status = $context['filters'][Scooter::STATUS_PARAMETER_NAME] ?? Scooter::SCOOTER_STATUS_AVAILABLE;

        $this->validateLocation($southWestLocation);
        $this->validateLocation($northEastLocation);
        $this->validateStatus($status);

        $southWestPoint = $this->createPointFromString($southWestLocation);
        $northEastPoint = $this->createPointFromString($northEastLocation);

        return $this->scooterService->getScootersInRectangle($southWestPoint, $northEastPoint, $status);
    }

    private function validateLocation(string $location): void
    {
        $constraint = new Regex([
            'pattern' => '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/',
            'message' => 'Invalid location format',
        ]);

        $violations = Validation::createValidator()->validate($location, $constraint);
        if (count($violations) > 0) {
            throw new ValidationException((string)$violations);
        }
    }

    private function createPointFromString(string $location): Point
    {
        [$latitude, $longitude] = explode(', ', $location);
        return new Point($latitude, $longitude);
    }

    private function validateStatus(int $status): void
    {
        if (!in_array($status, [Scooter::SCOOTER_STATUS_AVAILABLE, Scooter::SCOOTER_STATUS_OCCUPIED])) {
            throw new ValidationException(sprintf('Invalid status, must be %s or %s', Scooter::SCOOTER_STATUS_AVAILABLE, Scooter::SCOOTER_STATUS_OCCUPIED));
        }
    }
}
