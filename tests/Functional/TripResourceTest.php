<?php

namespace Functional;

use AbstractTest;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Scooter;
use App\Entity\Trip;
use App\Repository\ScooterRepository;
use App\Repository\TripRepository;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Uid\Uuid;

/**
 * @group TripResource
 */
class TripResourceTest extends AbstractTest
{
    public function testGetCollectionNotAuthenticated(): void
    {
        static::createClient()->request('GET', static::getContainer()->get('router')->generate('get_trips_collection'));

        $this->assertResponseStatusCodeSame(401);

    }

    public function testGetCollectionAuthenticated(): void
    {
        $client = static::createClientWithAuthorization();

        $client->request('GET', static::getContainer()->get('router')->generate('get_trips_collection'));

        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceCollectionJsonSchema(Trip::class);
    }

    public function testStartTrip(): void
    {
        $client = static::createClientWithAuthorization();
        $scooter = $this->createScooter();
        $tripId = $this->createTrip($client, $scooter);

        $this->assertResponseStatusCodeSame(201);

        $newTrip = static::getContainer()->get(TripRepository::class)->findOneBy(['id' => $tripId]);
        $scooterUpdated = static::getContainer()->get(ScooterRepository::class)->findOneBy(['id' => $scooter->getId()]);


        $this->assertInstanceOf(Trip::class, $newTrip);
        $this->assertSame(Scooter::SCOOTER_STATUS_OCCUPIED, $scooterUpdated->getStatus());
    }

    public function testStartTripScooterOccupied(): void
    {
        $client = static::createClientWithAuthorization();
        $scooter = $this->createScooter(Scooter::SCOOTER_STATUS_OCCUPIED);

        $this->createTrip($client, $scooter);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testFinishTrip(): void
    {
        $updatedLongitude = '55.4501';
        $updatedLatitude = '32.5234';
        $client = static::createClientWithAuthorization();

        $scooter = $this->createScooter();
        $tripId = $this->createTrip($client, $scooter);


        $newTrip = static::getContainer()->get(TripRepository::class)->findOneBy(['id' => $tripId]);

        $client->request('PATCH', static::getContainer()->get('router')->generate('end_trip', ['id' => $newTrip->getId()]), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'currentLocation' => [
                    'latitude' => $updatedLatitude,
                    'longitude' => $updatedLongitude
                ]
            ]
        ]);
        $this->assertResponseIsSuccessful();


        $finishedTrip = static::getContainer()->get(TripRepository::class)->findOneBy(['id' => $tripId]);

        $this->assertSame(Trip::TRIP_STATUS_ENDED, $finishedTrip->getStatus());
        $this->assertEquals((new Point(['y' => $updatedLatitude, 'x' => $updatedLongitude])), $finishedTrip->getCurrentLocation());
    }


    private function createScooter(string $status = Scooter::SCOOTER_STATUS_AVAILABLE): Scooter
    {
        $scooter = (new Scooter())
            ->setLocation(new Point(['y' => '30.5234', 'x' => '50.4501']))
            ->setStatus($status)
            ->setId(Uuid::v4());

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($scooter);
        $entityManager->flush();

        return $scooter;
    }

    private function createTrip(Client $client, Scooter $scooter): string
    {
        $response = $client->request('POST', static::getContainer()->get('router')->generate('start_trip'), [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'scooterId' => $scooter->getId()
            ]
        ]);

        if ($response->getStatusCode() !== 201) {
            return '';
        }

        return $response->toArray()['id'];
    }
}
