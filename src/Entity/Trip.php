<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\TripRepository;
use App\State\EndTripStateProcessor;
use App\State\StartTripStateProcessor;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TripRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(name: 'get_trips_collection'),
        new Post(
            openapiContext: ['summary' => 'Start a trip with concrete scooter'],
            denormalizationContext: ['groups' => ['post']],
            name: 'start_trip',
            processor: StartTripStateProcessor::class,

        ),
        new Patch(
            openapiContext: ['summary' => 'End a trip with concrete scooter'],
            denormalizationContext: ['groups' => ['patch']],
            name: 'end_trip',
            processor: EndTripStateProcessor::class
        ),
    ],
)]
class Trip
{
    use TimestampableEntity;

    public const TRIP_STATUS_STARTED = 1;

    public const TRIP_STATUS_ENDED = 2;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ApiProperty(identifier: true, example: 'acef7c1b-9183-4ddd-861f-a73706c15c83')]
    private $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Scooter $scooter = null;

    #[Groups(["post"])]
    #[ApiProperty(example: '018d0a12-f7ec-70c7-a3c6-4bc00de3601a')]
    private ?string $scooterId = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'trips')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'point')]
    private Point|null $startLocation = null;

    #[ORM\Column(type: 'point')]
    #[Assert\NotBlank(groups: ['patch'])]
    #[Groups(["patch"])]
    #[Assert\Regex(
        pattern: '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/',
        groups: ['patch'],
    )]
    #[ApiProperty(example: '{"longitude": 10.7234, "latitude": 15.8501}')]
    private Point|null $currentLocation = null;

    #[ORM\Column(type: 'point')]
    #[Assert\Type(Point::class)]
    private Point|null $endLocation = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(Scooter::SCOOTER_STATUS_AVAILABLE)]
    #[Assert\LessThanOrEqual(Scooter::SCOOTER_STATUS_OCCUPIED)]
    private ?int $status = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getScooter(): ?Scooter
    {
        return $this->scooter;
    }

    public function setScooter(?Scooter $scooter): static
    {
        $this->scooter = $scooter;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartLocation(): ?Point
    {
        return $this->startLocation;
    }

    public function setStartLocation(Point $startLocation): static
    {
        $this->startLocation = $startLocation;

        return $this;
    }

    public function getCurrentLocation(): ?Point
    {
        return $this->currentLocation;
    }

    public function setCurrentLocation(Point $currentLocation): static
    {
        $this->currentLocation = $currentLocation;

        return $this;
    }

    public function getEndLocation(): ?Point
    {
        return $this->endLocation;
    }

    public function setEndLocation(Point $endLocation): static
    {
        $this->endLocation = $endLocation;

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

    public function getScooterId(): ?string
    {
        return $this->scooterId;
    }

    public function setScooterId(string $scooterId): static
    {
        $this->scooterId = $scooterId;

        return $this;
    }
}
