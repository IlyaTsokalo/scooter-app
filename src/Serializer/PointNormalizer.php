<?php

namespace App\Serializer;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PointNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        return [
            'latitude' => $object->getLatitude(),
            'longitude' => $object->getLongitude(),
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Point;
    }

    /**
     * @param array{latitude:float,longitude:float} $data
     * @param string $type
     * @param string|null $format
     * @param array $context
     *
     * @return Point
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Point
    {
        return new Point($data['longitude'], $data['latitude']);
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return Point::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Point::class => true,
        ];
    }
}
