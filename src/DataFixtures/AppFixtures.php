<?php

namespace App\DataFixtures;

use App\Entity\Scooter;
use App\Factory\ScooterFactory;
use App\Factory\UserFactory;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Factory\UuidFactory;

class AppFixtures extends Fixture
{
    public function __construct(protected UuidFactory $uuidFactory, UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        UserFactory::createMany(10, function () use ($faker) {
            return [
                'uuid' => $this->uuidFactory->create(),
                'email' => $faker->email,
                'roles' => ['ROLE_USER'],
                'password' => hash('sha512', '123456'),
            ];
        });


        ScooterFactory::createMany(300, function () use ($faker) {
            return [
                'id' => $this->uuidFactory->create(),
                'location' => new Point(['x' => rand(0, 180), 'y' => rand(0, 90)]),
                'status' => Scooter::SCOOTER_STATUS_AVAILABLE,
            ];
        });

    }
}
