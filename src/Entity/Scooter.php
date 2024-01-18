<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Repository\ScooterRepository;
use App\State\ScooterQueryStateProvider;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Index;

#[ORM\Entity(repositoryClass: ScooterRepository::class)]
#[Index(columns: ['location'], flags: ['spatial'])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(name: 'get_scooters_collection'),
        new GetCollection(
            uriTemplate: '/scooters-in-rectangle-area',
            openapiContext: [
                'summary' => 'Get scooters in rectangle area',
                'parameters' => [
                    [
                        'name' => Scooter::SOUTH_WEST_LOCATION_PARAMETER_NAME,
                        'in' => 'query',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                            'example' => '10.755831, 20.523422',
                        ],
                    ],
                    [
                        'name' => Scooter::NORTH_EAST_LOCATION_PARAMETER_NAME,
                        'in' => 'query',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                            'example' => '85.755831, 70.523422',
                        ],
                    ],
                    [
                        'name' => Scooter::STATUS_PARAMETER_NAME,
                        'in' => 'query',
                        'required' => false,
                        'schema' => [
                            'type' => 'integer',
                            'example' => Scooter::SCOOTER_STATUS_AVAILABLE,
                        ],
                    ]
                ],
            ],
            name: 'get_scooters_in_rectangle_area',
            provider: ScooterQueryStateProvider::class,
        ),
        new Patch(
            openapiContext: [
                'summary' => 'Update scooter location and status',
                'requestBody' => [
                    'content' => [
                        'application/merge-patch+json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'location' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'longitude' => [
                                                'type' => 'number',
                                                'example' => 30.5234,
                                            ],
                                            'latitude' => [
                                                'type' => 'number',
                                                'example' => 50.4501,
                                            ],
                                        ],
                                    ],
                                    'status' => [
                                        'type' => 'integer',
                                        'example' => Scooter::SCOOTER_STATUS_AVAILABLE,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            name: 'update_scooter',
        ),
    ],
)]
class Scooter
{
    use TimestampableEntity;

    public const SCOOTER_STATUS_AVAILABLE = 1;
    public const SCOOTER_STATUS_OCCUPIED = 2;
    public const SOUTH_WEST_LOCATION_PARAMETER_NAME = 'southWestLocation';
    public const NORTH_EAST_LOCATION_PARAMETER_NAME = 'northEastLocation';
    public const STATUS_PARAMETER_NAME = 'status';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(identifier: true, example: 'acef7c1b-9183-4ddd-861f-a73706c15c83')]
    private $id;

    #[ORM\Column(type: 'point')]
    #[Assert\Type(Point::class)]
    #[Assert\NotBlank]
    #[ApiProperty(description: 'Location in format: "latitude, longitude", e.g "55.755831, 30.523422"')]
    private Point $location;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\GreaterThanOrEqual(self::SCOOTER_STATUS_AVAILABLE)]
    #[Assert\LessThanOrEqual(self::SCOOTER_STATUS_OCCUPIED)]
    private int $status;

    public function getLocation(): Point
    {
        return $this->location;
    }

    public function setLocation(Point $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }
}
