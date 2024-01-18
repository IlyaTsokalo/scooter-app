<?php

namespace Functional;

use AbstractTest;
use App\Entity\Scooter;
use App\Repository\ScooterRepository;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Uid\Uuid;


/**
 * @group ScooterResource
 */
class ScooterResourceTest extends AbstractTest
{
    public function testGetCollectionNotAuthenticated(): void
    {
        static::createClient()->request('GET', static::getContainer()->get('router')->generate('get_scooters_collection'));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetCollection(): void
    {
        $client = static::createClientWithAuthorization();

        $response = $client->request('GET', static::getContainer()->get('router')->generate('get_scooters_collection'), [
            'headers' => [
                'Content-Type' => 'application/vnd.api+json'
            ],
            'json' => []
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertCount(30, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Scooter::class);
    }

    public function testGetScootersInRectangleArea(): void
    {
        $client = static::createClientWithAuthorization();

        $query = [
            'query' => [
                'southWestLocation' => '1.4215, 1.6972',
                'northEastLocation' => '85.4215, 75.6972',
                'status' => Scooter::SCOOTER_STATUS_AVAILABLE
            ],
        ];


        $response = $client->request('GET', static::getContainer()->get('router')->generate('get_scooters_in_rectangle_area', $query), $query);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, count($response->toArray()['hydra:member']));
        $this->assertMatchesResourceCollectionJsonSchema(Scooter::class);
    }

    /**
     * @dataProvider queryDataProvider
     */
    public function testGetScootersInRectangleAreaInvalidParameters(array $query): void
    {
        $client = static::createClientWithAuthorization();

        $client->request('GET', static::getContainer()->get('router')->generate('get_scooters_in_rectangle_area', $query), $query);

        $this->assertResponseIsUnprocessable();
    }

    public function testScooterUpdate(): void
    {
        $client = static::createClientWithAuthorization();

        $scooter = (new Scooter())
            ->setLocation(new Point(['y' => '30.5234', 'x' => '50.4501']))
            ->setStatus(Scooter::SCOOTER_STATUS_AVAILABLE)
            ->setId(Uuid::v4());

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($scooter);
        $entityManager->flush();

        $savedScooter = static::getContainer()->get(ScooterRepository::class)->findOneBy(['id' => $scooter->getId()]);
        $latitudeToUpdate = 31.5234;
        $longitudeToUpdate = 51.4501;


        $client->request('PATCH', static::getContainer()->get('router')->generate('update_scooter', ['id' => $scooter->getId()]),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json'
                ],
                'json' => [
                    'location' => [
                        'latitude' => $latitudeToUpdate,
                        'longitude' => $longitudeToUpdate,
                    ],
                    'status' => Scooter::SCOOTER_STATUS_AVAILABLE
                ]
            ]);

        $this->assertResponseIsSuccessful();

        $this->assertEquals($savedScooter->getLocation(), (new Point(['x' => $longitudeToUpdate, 'y' => $latitudeToUpdate])));
    }

    private function queryDataProvider(): array
    {
        return [
            'invalid geolocation' => [
                [
                    'query' => [
                        'southWestLocation' => '1.4215, 523232231.6972',
                        'northEastLocation' => '8322332325.4215, 75.6972',
                        'status' => Scooter::SCOOTER_STATUS_AVAILABLE
                    ],
                ],
            ],
            'invalid status' => [
                [
                    'query' => [
                        'southWestLocation' => '1.4215, 51.6972',
                        'northEastLocation' => '85.4215, 75.6972',
                        'status' => 3
                    ],
                ],
            ],
        ];
    }
}
